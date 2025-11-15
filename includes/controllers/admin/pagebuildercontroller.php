<?php
namespace Includes\Controllers\Admin;

use Core\BaseController;
use Core\Request;
use Core\Response;
use Includes\Routing\Request as RoutingRequest;
use Models\ContentVersionModel;

class PageBuilderController extends BaseController {
    protected $versionModel;

    public function __construct(Request $request, array $dependencies = []) {
        parent::__construct($request, $dependencies);
        $this->versionModel = new ContentVersionModel();
    }

    public function handle(\Core\Request $request): \Core\Response {
        $action = $request->get('action', 'index');
        $id = $request->get('id');

        switch ($action) {
            case 'create':
                return $this->create();
            case 'store':
                return $this->store($request);
            case 'edit':
                return $this->edit($id);
            case 'update':
                return $this->update($id, $request);
            default:
                return $this->index();
        }
    }

    public function index() {
        return $this->view->render('admin/page-builder/index');
    }

    public function create() {
        return $this->view->render('admin/page-builder/create');
    }

    public function store(Request $request): Response {
        try {
            // TODO: Implement content creation
            $_SESSION['flash_success'] = 'Page created successfully';
            return $this->redirect('/admin/page-builder');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create page: ' . $e->getMessage();
            return $this->redirect('/admin/page-builder/create');
        }
    }

    public function edit($id) {
        $content = []; // TODO: Get content from database
        return $this->view->render('admin/page-builder/edit', ['content' => $content]);
    }

    public function update($id, Request $request): Response {
        try {
            // TODO: Implement content update
            $_SESSION['flash_success'] = 'Page updated successfully';
            return $this->redirect('/admin/page-builder/' . $id);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update page: ' . $e->getMessage();
            header('Location: /admin/page-builder/' . $id . '/edit');
            exit;
        }
    }

    public function versions($id) {
        $versions = $this->versionModel->getByPageId($id);
        return $this->view->render('admin/page-builder/versions', [
            'contentId' => $id,
            'versions' => $versions
        ]);
    }

    public function compare($id, $versionA, $versionB) {
        $versions = $this->versionModel->getVersionsForComparison($id, $versionA, $versionB);
        $allVersions = $this->versionModel->getByPageId($id);
        
        if (count($versions) !== 2) {
            $_SESSION['flash_error'] = 'Could not find both versions for comparison';
            header('Location: /admin/page-builder/' . $id . '/versions');
            exit;
        }

        return $this->view->render('admin/page-builder/compare', [
            'contentId' => $id,
            'versionA' => $versions[0],
            'versionB' => $versions[1],
            'allVersions' => $allVersions
        ]);
    }

    public function restore($id, $versionId, $reason = '') {
        try {
            $userId = $_SESSION['user_id'] ?? 0;
            if ($this->versionModel->restoreVersion($versionId, $userId, $reason)) {
                $_SESSION['flash_success'] = 'Version restored successfully';
                header('Location: /admin/page-builder/' . $id);
                exit;
            }
            
            throw new \Exception('Version restore failed');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to restore version: ' . $e->getMessage();
            header('Location: /admin/page-builder/' . $id . '/versions');
            exit;
        }
    }

    public function delete($id) {
        try {
            // TODO: Implement content deletion
            $_SESSION['flash_success'] = 'Page deleted successfully';
            header('Location: /admin/page-builder');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to delete page: ' . $e->getMessage();
            header('Location: /admin/page-builder/' . $id);
            exit;
        }
    }

    public function bulkDeleteVersions($id) {
        try {
            if (!isset($_POST['versions'])) {
                throw new \Exception('No versions selected');
            }

            $backupDir = __DIR__ . '/../../../../storage/version_backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $backupFile = $backupDir . '/versions_' . date('Ymd_His') . '.json';
            $backupData = [
                'content_id' => $id,
                'versions' => $_POST['versions'],
                'backup_date' => date('Y-m-d H:i:s')
            ];

            file_put_contents($backupFile, json_encode($backupData));

            $this->versionModel->bulkDelete($id, $_POST['versions']);

            $_SESSION['flash_success'] = 'Selected versions deleted successfully (backup saved)';
            header('Location: /admin/page-builder/' . $id . '/versions');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to delete versions: ' . $e->getMessage();
            header('Location: /admin/page-builder/' . $id . '/versions');
            exit;
        }
    }
}
