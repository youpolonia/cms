<?php
/**
 * Jessie CMS — CRM Core
 * Contact management, pipeline, activities, deals
 */

class CrmManager
{
    // ─── CONTACTS ───

    public static function getContacts(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['source'])) {
            $where[] = 'c.source = ?';
            $params[] = $filters['source'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.company LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }
        if (!empty($filters['tag'])) {
            $where[] = 'c.tags LIKE ?';
            $params[] = '%' . $filters['tag'] . '%';
        }

        $whereStr = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM crm_contacts c WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $order = $filters['sort'] ?? 'created_at DESC';
        $allowed = ['created_at DESC', 'created_at ASC', 'score DESC', 'first_name ASC', 'last_name ASC', 'updated_at DESC'];
        if (!in_array($order, $allowed)) $order = 'created_at DESC';

        $stmt = $pdo->prepare(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM crm_deals d WHERE d.contact_id = c.id) as deals_count,
                    (SELECT SUM(d.value) FROM crm_deals d WHERE d.contact_id = c.id AND d.stage NOT IN ('lost')) as deals_value,
                    (SELECT COUNT(*) FROM crm_activities a WHERE a.contact_id = c.id AND a.completed = 0 AND a.due_date IS NOT NULL) as pending_tasks
             FROM crm_contacts c 
             WHERE {$whereStr}
             ORDER BY {$order}
             LIMIT ? OFFSET ?"
        );
        $allParams = array_merge($params, [$perPage, $offset]);
        $stmt->execute($allParams);

        return [
            'contacts' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'totalPages' => max(1, (int)ceil($total / $perPage)),
        ];
    }

    public static function getContact(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM crm_contacts WHERE id = ?");
        $stmt->execute([$id]);
        $contact = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $contact ?: null;
    }

    public static function getContactByEmail(string $email): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM crm_contacts WHERE email = ? LIMIT 1");
        $stmt->execute([trim($email)]);
        $contact = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $contact ?: null;
    }

    public static function createContact(array $data): int
    {
        $pdo = db();
        $fields = ['first_name', 'last_name', 'email', 'phone', 'company', 'job_title', 
                    'source', 'status', 'score', 'tags', 'notes'];
        $insert = [];
        $values = [];
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $insert[] = $f;
                $values[] = $data[$f];
            }
        }
        if (empty($insert)) return 0;

        $cols = implode(', ', $insert);
        $placeholders = implode(', ', array_fill(0, count($insert), '?'));
        $stmt = $pdo->prepare("INSERT INTO crm_contacts ({$cols}) VALUES ({$placeholders})");
        $stmt->execute($values);
        $newId = (int)$pdo->lastInsertId();

        if (function_exists('cms_event')) {
            cms_event('crm.contact.created', [
                'id' => $newId,
                'name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                'email' => $data['email'] ?? '',
                'source' => $data['source'] ?? 'manual',
            ]);
        }

        return $newId;
    }

    public static function updateContact(int $id, array $data): bool
    {
        $pdo = db();
        $fields = ['first_name', 'last_name', 'email', 'phone', 'company', 'job_title',
                    'status', 'score', 'tags', 'notes'];
        $sets = [];
        $values = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $data)) {
                $sets[] = "{$f} = ?";
                $values[] = $data[$f];
            }
        }
        if (empty($sets)) return false;

        $values[] = $id;
        $stmt = $pdo->prepare("UPDATE crm_contacts SET " . implode(', ', $sets) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function deleteContact(int $id): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM crm_contacts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Import contact from contact form submission
     */
    public static function importFromContactForm(array $submission): int
    {
        $nameParts = explode(' ', trim($submission['name'] ?? ''), 2);
        return self::createContact([
            'first_name' => $nameParts[0] ?? 'Unknown',
            'last_name' => $nameParts[1] ?? '',
            'email' => $submission['email'] ?? '',
            'phone' => $submission['phone'] ?? '',
            'source' => 'contact_form',
            'status' => 'new',
            'notes' => $submission['message'] ?? '',
        ]);
    }

    /**
     * Import from chatbot session
     */
    public static function importFromChatbot(string $sessionId, string $email = '', string $name = ''): int
    {
        $nameParts = explode(' ', trim($name), 2);
        return self::createContact([
            'first_name' => $nameParts[0] ?: 'Chat Visitor',
            'last_name' => $nameParts[1] ?? '',
            'email' => $email,
            'source' => 'chatbot',
            'status' => 'new',
            'notes' => "Imported from chatbot session: {$sessionId}",
        ]);
    }

    // ─── ACTIVITIES ───

    public static function getActivities(int $contactId, int $limit = 50): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT * FROM crm_activities WHERE contact_id = ? ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$contactId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function addActivity(int $contactId, string $type, string $title, string $description = '', ?string $dueDate = null): int
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO crm_activities (contact_id, type, title, description, due_date, created_by) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $user = $_SESSION['username'] ?? 'system';
        $stmt->execute([$contactId, $type, $title, $description, $dueDate, $user]);
        $actId = (int)$pdo->lastInsertId();

        // Update last_contacted_at
        if (in_array($type, ['email', 'call', 'meeting'])) {
            $pdo->prepare("UPDATE crm_contacts SET last_contacted_at = NOW() WHERE id = ?")
                ->execute([$contactId]);
        }

        return (int)$pdo->lastInsertId();
    }

    public static function completeActivity(int $activityId): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare("UPDATE crm_activities SET completed = 1 WHERE id = ?");
        return $stmt->execute([$activityId]);
    }

    public static function getUpcomingTasks(int $limit = 10): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT a.*, c.first_name, c.last_name, c.email, c.company
             FROM crm_activities a 
             JOIN crm_contacts c ON c.id = a.contact_id
             WHERE a.completed = 0 AND a.due_date IS NOT NULL
             ORDER BY a.due_date ASC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ─── DEALS ───

    public static function getDeals(array $filters = []): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['stage'])) {
            $where[] = 'd.stage = ?';
            $params[] = $filters['stage'];
        }
        if (!empty($filters['contact_id'])) {
            $where[] = 'd.contact_id = ?';
            $params[] = $filters['contact_id'];
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $pdo->prepare(
            "SELECT d.*, c.first_name, c.last_name, c.email, c.company
             FROM crm_deals d
             JOIN crm_contacts c ON c.id = d.contact_id
             WHERE {$whereStr}
             ORDER BY d.updated_at DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function createDeal(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO crm_deals (contact_id, title, value, currency, stage, probability, expected_close, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['contact_id'], $data['title'], $data['value'] ?? 0,
            $data['currency'] ?? 'USD', $data['stage'] ?? 'lead',
            $data['probability'] ?? 10, $data['expected_close'] ?? null,
            $data['notes'] ?? ''
        ]);
        $dealId = (int)$pdo->lastInsertId();

        if (function_exists('cms_event')) {
            cms_event('crm.deal.created', [
                'id' => $dealId, 'title' => $data['title'],
                'value' => $data['value'] ?? 0, 'stage' => $data['stage'] ?? 'lead',
                'contact_id' => $data['contact_id'],
            ]);
        }

        return $dealId;
    }

    public static function updateDeal(int $id, array $data): bool
    {
        $pdo = db();
        $fields = ['title', 'value', 'currency', 'stage', 'probability', 'expected_close', 'notes'];
        $sets = [];
        $values = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $data)) {
                $sets[] = "{$f} = ?";
                $values[] = $data[$f];
            }
        }
        if (empty($sets)) return false;
        $values[] = $id;
        $stmt = $pdo->prepare("UPDATE crm_deals SET " . implode(', ', $sets) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function deleteDeal(int $id): bool
    {
        $pdo = db();
        return $pdo->prepare("DELETE FROM crm_deals WHERE id = ?")->execute([$id]);
    }

    // ─── PIPELINE VIEW ───

    public static function getPipeline(): array
    {
        $stages = ['lead', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        $pipeline = [];
        $pdo = db();

        foreach ($stages as $stage) {
            $stmt = $pdo->prepare(
                "SELECT d.*, c.first_name, c.last_name, c.company
                 FROM crm_deals d JOIN crm_contacts c ON c.id = d.contact_id
                 WHERE d.stage = ? ORDER BY d.value DESC"
            );
            $stmt->execute([$stage]);
            $deals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $totalValue = array_sum(array_column($deals, 'value'));
            $pipeline[$stage] = [
                'deals' => $deals,
                'count' => count($deals),
                'value' => $totalValue,
            ];
        }

        return $pipeline;
    }

    // ─── STATS ───

    public static function getStats(): array
    {
        $pdo = db();
        return [
            'total_contacts' => (int)$pdo->query("SELECT COUNT(*) FROM crm_contacts")->fetchColumn(),
            'new_contacts' => (int)$pdo->query("SELECT COUNT(*) FROM crm_contacts WHERE status = 'new'")->fetchColumn(),
            'this_month' => (int)$pdo->query("SELECT COUNT(*) FROM crm_contacts WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
            'total_deals' => (int)$pdo->query("SELECT COUNT(*) FROM crm_deals WHERE stage NOT IN ('won','lost')")->fetchColumn(),
            'deals_value' => (float)$pdo->query("SELECT COALESCE(SUM(value), 0) FROM crm_deals WHERE stage NOT IN ('won','lost')")->fetchColumn(),
            'won_value' => (float)$pdo->query("SELECT COALESCE(SUM(value), 0) FROM crm_deals WHERE stage = 'won'")->fetchColumn(),
            'pending_tasks' => (int)$pdo->query("SELECT COUNT(*) FROM crm_activities WHERE completed = 0 AND due_date IS NOT NULL")->fetchColumn(),
        ];
    }
}
