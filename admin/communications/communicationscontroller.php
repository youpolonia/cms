<?php
require_once __DIR__ . '/../core/csrf.php';

class CommunicationsController {
    private $messageModel;
    private $viewRenderer;

    public function __construct() {
        $this->messageModel = new Message();
        $this->viewRenderer = new ViewRenderer();
    }

    public function index() {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];

        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10;

        $result = $this->messageModel->getThreads(Auth::user()->id, $filters, $page, $perPage);

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'threads' => $result['threads'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'perPage' => $result['perPage'],
                    'totalPages' => ceil($result['total'] / $result['perPage'])
                ]
            ]);
            exit;
        }

        $this->viewRenderer->render('admin/views/communications/index.php', [
            'threads' => $result['threads'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'perPage' => $result['perPage'],
                'totalPages' => ceil($result['total'] / $result['perPage'])
            ],
            'layout' => 'admin/views/layout.php'
        ]);
    }

    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    public function viewThread($threadId) {
        $messages = $this->messageModel->getThreadMessages($threadId);
        $this->viewRenderer->render('admin/views/communications/thread.php', [
            'messages' => $messages,
            'layout' => 'admin/views/layout.php'
        ]);
    }

    public function createThread() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_or_403();
            $threadId = $this->messageModel->createThread(
                $_POST['recipient_id'],
                Auth::user()->id,
                $_POST['subject'],
                $_POST['message']
            );
            header("Location: /admin/communications/thread/$threadId");
            exit;
        }
        
        $this->viewRenderer->render('admin/views/communications/create.php', [
            'workers' => (new Worker())->getAll(),
            'layout' => 'admin/views/layout.php'
        ]);
    }

    public function reply($threadId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_or_403();
            $this->messageModel->addReply(
                $threadId,
                Auth::user()->id,
                $_POST['message']
            );
            header("Location: /admin/communications/thread/$threadId");
            exit;
        }
    }

    public function archiveThread($threadId) {
        $this->messageModel->archiveThread($threadId, Auth::user()->id);
        header("Location: /admin/communications");
        exit;
    }
}
