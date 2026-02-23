<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;
use Core\Session;

class CrmController
{
    private function loadCrm(): void
    {
        require_once CMS_ROOT . '/core/crm_manager.php';
    }

    // ─── DASHBOARD ───

    public function dashboard(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $stats = \CrmManager::getStats();
        $tasks = \CrmManager::getUpcomingTasks(5);
        $recentResult = \CrmManager::getContacts(['sort' => 'created_at DESC'], 1, 5);
        $pipeline = \CrmManager::getPipeline();

        render('admin/crm/dashboard', [
            'stats' => $stats,
            'tasks' => $tasks,
            'recent' => $recentResult['contacts'],
            'pipeline' => $pipeline,
        ]);
    }

    // ─── CONTACTS ───

    public function contacts(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'source' => $_GET['source'] ?? '',
            'search' => $_GET['q'] ?? '',
            'tag' => $_GET['tag'] ?? '',
            'sort' => $_GET['sort'] ?? 'created_at DESC',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = \CrmManager::getContacts($filters, $page);

        render('admin/crm/contacts', [
            'contacts' => $result['contacts'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'filters' => $filters,
        ]);
    }

    public function contactCreate(Request $request): void
    {
        Session::requireRole('admin');
        render('admin/crm/contact-form', ['contact' => null]);
    }

    public function contactEdit(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();
        $id = (int)$request->param('id');
        $contact = \CrmManager::getContact($id);
        if (!$contact) {
            Session::flash('error', 'Contact not found.');
            \Core\Response::redirect('/admin/crm/contacts');
        }
        render('admin/crm/contact-form', ['contact' => $contact]);
    }

    public function contactStore(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $data = $_POST;
        $id = \CrmManager::createContact($data);

        if ($id) {
            \CrmManager::addActivity($id, 'note', 'Contact created', 'Contact was added manually.');
            Session::flash('success', 'Contact created.');
            \Core\Response::redirect("/admin/crm/contacts/{$id}");
        } else {
            Session::flash('error', 'Failed to create contact.');
            \Core\Response::redirect('/admin/crm/contacts/create');
        }
    }

    public function contactView(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $id = (int)$request->param('id');
        $contact = \CrmManager::getContact($id);
        if (!$contact) {
            Session::flash('error', 'Contact not found.');
            \Core\Response::redirect('/admin/crm/contacts');
        }

        $activities = \CrmManager::getActivities($id);
        $deals = \CrmManager::getDeals(['contact_id' => $id]);

        render('admin/crm/contact-view', [
            'contact' => $contact,
            'activities' => $activities,
            'deals' => $deals,
        ]);
    }

    public function contactUpdate(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $id = (int)$request->param('id');
        \CrmManager::updateContact($id, $_POST);

        Session::flash('success', 'Contact updated.');
        \Core\Response::redirect("/admin/crm/contacts/{$id}");
    }

    public function contactDelete(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $id = (int)$request->param('id');
        \CrmManager::deleteContact($id);

        Session::flash('success', 'Contact deleted.');
        \Core\Response::redirect('/admin/crm/contacts');
    }

    // ─── ACTIVITIES ───

    public function activityAdd(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $contactId = (int)($_POST['contact_id'] ?? 0);
        $type = $_POST['type'] ?? 'note';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $dueDate = $_POST['due_date'] ?? null;

        if ($contactId && $title) {
            \CrmManager::addActivity($contactId, $type, $title, $description, $dueDate ?: null);
            Session::flash('success', 'Activity added.');
        }

        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        \Core\Response::redirect("/admin/crm/contacts/{$contactId}");
    }

    public function activityComplete(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $id = (int)$request->param('id');
        \CrmManager::completeActivity($id);

        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? '/admin/crm';
        \Core\Response::redirect($ref);
    }

    // ─── DEALS ───

    public function pipeline(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $pipeline = \CrmManager::getPipeline();
        $stats = \CrmManager::getStats();

        render('admin/crm/pipeline', [
            'pipeline' => $pipeline,
            'stats' => $stats,
        ]);
    }

    public function dealCreate(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $contactId = (int)($_POST['contact_id'] ?? 0);
        $data = $_POST;
        $data['contact_id'] = $contactId;
        $id = \CrmManager::createDeal($data);

        if ($id) {
            \CrmManager::addActivity($contactId, 'note', "Deal created: {$data['title']}", "Value: {$data['value']}");
            Session::flash('success', 'Deal created.');
        }

        \Core\Response::redirect("/admin/crm/contacts/{$contactId}");
    }

    public function dealUpdate(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $id = (int)$request->param('id');
        \CrmManager::updateDeal($id, $_POST);

        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? '/admin/crm';
        \Core\Response::redirect($ref);
    }

    public function dealDelete(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $id = (int)$request->param('id');
        \CrmManager::deleteDeal($id);

        Session::flash('success', 'Deal deleted.');
        $ref = $_SERVER['HTTP_REFERER'] ?? '/admin/crm';
        \Core\Response::redirect($ref);
    }

    // ─── IMPORT ───

    public function importPage(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();
        $count = (int)$pdo->query(
            "SELECT COUNT(*) FROM contact_submissions 
             WHERE email NOT IN (SELECT email FROM crm_contacts WHERE email IS NOT NULL AND email != '')"
        )->fetchColumn();

        render('admin/crm/import', ['importable' => $count]);
    }

    public function importFromSubmissions(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCrm();

        $pdo = db();
        $stmt = $pdo->query(
            "SELECT * FROM contact_submissions 
             WHERE email NOT IN (SELECT email FROM crm_contacts WHERE email IS NOT NULL AND email != '')
             ORDER BY created_at DESC LIMIT 100"
        );
        $submissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $imported = 0;
        foreach ($submissions as $sub) {
            $id = \CrmManager::importFromContactForm($sub);
            if ($id) {
                \CrmManager::addActivity($id, 'form_submit', 'Contact form submission', $sub['message'] ?? '');
                $imported++;
            }
        }

        Session::flash('success', "{$imported} contacts imported from form submissions.");
        \Core\Response::redirect('/admin/crm/contacts');
    }
}
