<?php

use Core\Database;
use Core\EventBus;
use Core\Module;
use Core\Rbac;
use Core\Auth;

class SalesController
{
    private PDO $db;
    private Rbac $rbac;
    private ?array $user;

    private array $leadStatuses = ['new', 'contacted', 'qualified', 'unqualified'];
    private array $stages = ['inquiry', 'qualification', 'consultation', 'proposal', 'negotiation', 'won', 'lost'];
    private array $activityTypes = ['call', 'meeting', 'email', 'whatsapp', 'document_review'];

    public function __construct()
    {
        $this->db = Database::connection();
        $this->rbac = new Rbac($this->db);
        $this->user = Auth::getInstance()->user();
        $this->ensureSchema();
    }

    public function overview(): void
    {
        $summary = $this->summary();
        $pipeline = $this->pipelineStats();
        $topSources = $this->topLeadSources();
        $upcoming = $this->upcomingFollowups(8);
        $recentLeads = $this->recentLeads(8);
        Module::renderView('Sales/views/overview', compact('summary', 'pipeline', 'topSources', 'upcoming', 'recentLeads'));
    }

    public function leads(): void
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, s.name AS service_name, u.full_name AS assigned_name
             FROM sales_leads l
             LEFT JOIN sales_services s ON s.id = l.service_id
             LEFT JOIN users u ON u.id = l.assigned_user_id
             WHERE ' . $this->scopeClause('l') . '
             ORDER BY l.created_at DESC
             LIMIT 100'
        );
        $stmt->execute($this->scopeParams());
        $rows = $stmt->fetchAll();
        $users = $this->formUsers();
        $services = $this->servicesList();
        Module::renderView('Sales/views/leads', compact('rows', 'users', 'services'));
    }

    public function editLead(int $id): void
    {
        $lead = $this->find('sales_leads', $id);
        if (!$lead) { $this->notFound('Lead tidak ditemukan'); return; }
        if (!$this->canAccessAssignedRow($lead)) { http_response_code(403); echo '403 - Lead ini bukan milik Anda'; return; }
        $users = $this->formUsers();
        $services = $this->servicesList();
        Module::renderView('Sales/views/lead_edit', compact('lead', 'users', 'services'));
    }

    public function opportunities(): void
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, s.name AS service_name, l.company_name AS lead_company, u.full_name AS assigned_name
             FROM sales_opportunities o
             LEFT JOIN sales_services s ON s.id = o.service_id
             LEFT JOIN sales_leads l ON l.id = o.lead_id
             LEFT JOIN users u ON u.id = o.assigned_user_id
             WHERE ' . $this->scopeClause('o') . '
             ORDER BY FIELD(o.stage, "inquiry", "qualification", "consultation", "proposal", "negotiation", "won", "lost"), o.expected_close_date ASC, o.id DESC
             LIMIT 100'
        );
        $stmt->execute($this->scopeParams());
        $rows = $stmt->fetchAll();
        $leads = $this->simpleLeads();
        $services = $this->servicesList();
        $users = $this->formUsers();
        Module::renderView('Sales/views/opportunities', compact('rows', 'leads', 'services', 'users'));
    }

    public function editOpportunity(int $id): void
    {
        $opportunity = $this->find('sales_opportunities', $id);
        if (!$opportunity) { $this->notFound('Opportunity tidak ditemukan'); return; }
        if (!$this->canAccessAssignedRow($opportunity)) { http_response_code(403); echo '403 - Opportunity ini bukan milik Anda'; return; }
        $leads = $this->simpleLeads();
        $services = $this->servicesList();
        $users = $this->formUsers();
        Module::renderView('Sales/views/opportunity_edit', compact('opportunity', 'leads', 'services', 'users'));
    }

    public function services(): void
    {
        $rows = $this->db->query('SELECT * FROM sales_services ORDER BY category ASC, name ASC')->fetchAll();
        Module::renderView('Sales/views/services', compact('rows'));
    }

    public function editService(int $id): void
    {
        $service = $this->find('sales_services', $id);
        if (!$service) { $this->notFound('Service tidak ditemukan'); return; }
        Module::renderView('Sales/views/service_edit', compact('service'));
    }

    public function followups(): void
    {
        $stmt = $this->db->prepare(
            'SELECT f.*, l.company_name, o.title AS opportunity_title, u.full_name AS assigned_name
             FROM sales_followups f
             LEFT JOIN sales_leads l ON l.id = f.lead_id
             LEFT JOIN sales_opportunities o ON o.id = f.opportunity_id
             LEFT JOIN users u ON u.id = f.assigned_user_id
             WHERE ' . $this->scopeClause('f') . '
             ORDER BY COALESCE(f.next_followup_date, f.activity_date) ASC, f.id DESC
             LIMIT 120'
        );
        $stmt->execute($this->scopeParams());
        $rows = $stmt->fetchAll();
        $leads = $this->simpleLeads();
        $opportunities = $this->simpleOpportunities();
        $users = $this->formUsers();
        Module::renderView('Sales/views/followups', compact('rows', 'leads', 'opportunities', 'users'));
    }

    public function editFollowup(int $id): void
    {
        $followup = $this->find('sales_followups', $id);
        if (!$followup) { $this->notFound('Follow-up tidak ditemukan'); return; }
        if (!$this->canAccessAssignedRow($followup)) { http_response_code(403); echo '403 - Follow-up ini bukan milik Anda'; return; }
        $leads = $this->simpleLeads();
        $opportunities = $this->simpleOpportunities();
        $users = $this->formUsers();
        Module::renderView('Sales/views/followup_edit', compact('followup', 'leads', 'opportunities', 'users'));
    }

    public function storeLead(): void { $this->saveLead(); header('Location: ' . app_url('sales/leads?created=1')); }
    public function updateLead(int $id): void { $this->saveLead($id); header('Location: ' . app_url('sales/leads?updated=1')); }
    public function deleteLead(int $id): void { $this->deleteAssigned('sales_leads', $id, 'sales/leads'); }
    public function storeOpportunity(): void { $this->saveOpportunity(); header('Location: ' . app_url('sales/opportunities?created=1')); }
    public function updateOpportunity(int $id): void { $this->saveOpportunity($id); header('Location: ' . app_url('sales/opportunities?updated=1')); }
    public function deleteOpportunity(int $id): void { $this->deleteAssigned('sales_opportunities', $id, 'sales/opportunities'); }
    public function storeService(): void { $this->saveService(); header('Location: ' . app_url('sales/services?created=1')); }
    public function updateService(int $id): void { $this->saveService($id); header('Location: ' . app_url('sales/services?updated=1')); }
    public function deleteService(int $id): void { $this->delete('sales_services', $id); header('Location: ' . app_url('sales/services?deleted=1')); }
    public function storeFollowup(): void { $this->saveFollowup(); header('Location: ' . app_url('sales/followups?created=1')); }
    public function updateFollowup(int $id): void { $this->saveFollowup($id); header('Location: ' . app_url('sales/followups?updated=1')); }
    public function deleteFollowup(int $id): void { $this->deleteAssigned('sales_followups', $id, 'sales/followups'); }

    public function dashboardCard(): string
    {
        $summary = $this->summary();
        $pipeline = $this->pipelineStats();
        $upcoming = $this->upcomingFollowups(5);
        ob_start();
        require __DIR__ . '/views/dashboard_card.php';
        return ob_get_clean();
    }

    private function saveLead(?int $id = null): void
    {
        $data = [
            'company_name' => trim($_POST['company_name'] ?? ''),
            'pic_name' => trim($_POST['pic_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'source' => trim($_POST['source'] ?? ''),
            'service_id' => (int)($_POST['service_id'] ?? 0) ?: null,
            'need_category' => trim($_POST['need_category'] ?? ''),
            'estimated_value' => $this->money($_POST['estimated_value'] ?? 0),
            'status' => in_array($_POST['status'] ?? 'new', $this->leadStatuses, true) ? $_POST['status'] : 'new',
            'assigned_user_id' => $this->assignedUserInput(),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
        if ($id) {
            $data['id'] = $id;
            $current = $this->find('sales_leads', $id);
            if (!$current || !$this->canAccessAssignedRow($current)) {
                http_response_code(403); echo '403 - Lead ini bukan milik Anda'; exit;
            }
            $stmt = $this->db->prepare('UPDATE sales_leads SET company_name=:company_name,pic_name=:pic_name,phone=:phone,email=:email,source=:source,service_id=:service_id,need_category=:need_category,estimated_value=:estimated_value,status=:status,assigned_user_id=:assigned_user_id,notes=:notes,updated_at=NOW() WHERE id=:id');
        } else {
            $stmt = $this->db->prepare('INSERT INTO sales_leads (company_name,pic_name,phone,email,source,service_id,need_category,estimated_value,status,assigned_user_id,notes,created_at) VALUES (:company_name,:pic_name,:phone,:email,:source,:service_id,:need_category,:estimated_value,:status,:assigned_user_id,:notes,NOW())');
        }
        $stmt->execute($data);
        $leadId = $id ?: (int)$this->db->lastInsertId();
        if ($data['status'] === 'qualified') {
            $this->createOpportunityFromQualifiedLead($leadId);
        }
    }

    private function createOpportunityFromQualifiedLead(int $leadId): void
    {
        if ($leadId <= 0 || $this->leadHasOpportunity($leadId)) {
            return;
        }

        $lead = $this->find('sales_leads', $leadId);
        if (!$lead || ($lead['status'] ?? '') !== 'qualified') {
            return;
        }

        $company = trim($lead['company_name'] ?? '') ?: 'Qualified Lead';
        $service = $this->findService((int)($lead['service_id'] ?? 0));
        $need = trim($lead['need_category'] ?? '');
        $serviceName = trim($service['name'] ?? '');
        $titleBase = $serviceName !== '' ? $serviceName : ($need !== '' ? $need : 'Opportunity');
        $dealValue = $this->money($lead['estimated_value'] ?? 0);
        if ($dealValue <= 0 && $service) {
            $dealValue = $this->money($service['base_price'] ?? 0);
        }
        $title = $titleBase . ' - ' . $company;

        $stmt = $this->db->prepare(
            'INSERT INTO sales_opportunities
                (lead_id, service_id, title, client_name, stage, deal_value, probability, expected_close_date, next_followup_date, assigned_user_id, lost_reason, notes, created_at)
             VALUES
                (:lead_id, :service_id, :title, :client_name, "qualification", :deal_value, 35, NULL, NULL, :assigned_user_id, "", :notes, NOW())'
        );
        $stmt->execute([
            'lead_id' => $leadId,
            'title' => $title,
            'client_name' => $company,
            'deal_value' => $dealValue,
            'service_id' => (int)($lead['service_id'] ?? 0) ?: null,
            'assigned_user_id' => (int)($lead['assigned_user_id'] ?? 0) ?: null,
            'notes' => trim('Auto-created from qualified lead. ' . ($lead['notes'] ?? '')),
        ]);
    }

    private function leadHasOpportunity(int $leadId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM sales_opportunities WHERE lead_id = :lead_id');
        $stmt->execute(['lead_id' => $leadId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function saveOpportunity(?int $id = null): int
    {
        $oldStage = null;
        if ($id) {
            $old = $this->find('sales_opportunities', $id);
            $oldStage = $old['stage'] ?? null;
        }

        $data = [
            'lead_id' => (int)($_POST['lead_id'] ?? 0) ?: null,
            'service_id' => (int)($_POST['service_id'] ?? 0) ?: null,
            'title' => trim($_POST['title'] ?? ''),
            'client_name' => trim($_POST['client_name'] ?? ''),
            'stage' => in_array($_POST['stage'] ?? 'inquiry', $this->stages, true) ? $_POST['stage'] : 'inquiry',
            'deal_value' => $this->money($_POST['deal_value'] ?? 0),
            'probability' => max(0, min(100, (int)($_POST['probability'] ?? 25))),
            'expected_close_date' => ($_POST['expected_close_date'] ?? '') ?: null,
            'next_followup_date' => ($_POST['next_followup_date'] ?? '') ?: null,
            'assigned_user_id' => $this->assignedUserInput(),
            'lost_reason' => trim($_POST['lost_reason'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
        if ($id) {
            $data['id'] = $id;
            if (!$old || !$this->canAccessAssignedRow($old)) {
                http_response_code(403); echo '403 - Opportunity ini bukan milik Anda'; exit;
            }
            $stmt = $this->db->prepare('UPDATE sales_opportunities SET lead_id=:lead_id,service_id=:service_id,title=:title,client_name=:client_name,stage=:stage,deal_value=:deal_value,probability=:probability,expected_close_date=:expected_close_date,next_followup_date=:next_followup_date,assigned_user_id=:assigned_user_id,lost_reason=:lost_reason,notes=:notes,updated_at=NOW() WHERE id=:id');
        } else {
            $stmt = $this->db->prepare('INSERT INTO sales_opportunities (lead_id,service_id,title,client_name,stage,deal_value,probability,expected_close_date,next_followup_date,assigned_user_id,lost_reason,notes,created_at) VALUES (:lead_id,:service_id,:title,:client_name,:stage,:deal_value,:probability,:expected_close_date,:next_followup_date,:assigned_user_id,:lost_reason,:notes,NOW())');
        }
        $stmt->execute($data);
        $opportunityId = $id ?: (int)$this->db->lastInsertId();

        if ($data['stage'] === 'won' && $oldStage !== 'won') {
            EventBus::dispatch('sales.opportunity.won', [
                'opportunity_id' => $opportunityId,
            ]);
        }

        return $opportunityId;
    }

    private function saveService(?int $id = null): void
    {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'base_price' => $this->money($_POST['base_price'] ?? 0),
            'estimated_duration' => trim($_POST['estimated_duration'] ?? ''),
            'required_documents' => trim($_POST['required_documents'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];
        if ($id) {
            $data['id'] = $id;
            $stmt = $this->db->prepare('UPDATE sales_services SET name=:name,category=:category,base_price=:base_price,estimated_duration=:estimated_duration,required_documents=:required_documents,description=:description,updated_at=NOW() WHERE id=:id');
        } else {
            $stmt = $this->db->prepare('INSERT INTO sales_services (name,category,base_price,estimated_duration,required_documents,description,created_at) VALUES (:name,:category,:base_price,:estimated_duration,:required_documents,:description,NOW())');
        }
        $stmt->execute($data);
    }

    private function saveFollowup(?int $id = null): void
    {
        $type = $_POST['activity_type'] ?? 'call';
        $data = [
            'lead_id' => (int)($_POST['lead_id'] ?? 0) ?: null,
            'opportunity_id' => (int)($_POST['opportunity_id'] ?? 0) ?: null,
            'activity_type' => in_array($type, $this->activityTypes, true) ? $type : 'call',
            'activity_date' => ($_POST['activity_date'] ?? '') ?: date('Y-m-d'),
            'result' => trim($_POST['result'] ?? ''),
            'next_action' => trim($_POST['next_action'] ?? ''),
            'next_followup_date' => ($_POST['next_followup_date'] ?? '') ?: null,
            'assigned_user_id' => $this->assignedUserInput(),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
        if ($id) {
            $data['id'] = $id;
            $current = $this->find('sales_followups', $id);
            if (!$current || !$this->canAccessAssignedRow($current)) {
                http_response_code(403); echo '403 - Follow-up ini bukan milik Anda'; exit;
            }
            $stmt = $this->db->prepare('UPDATE sales_followups SET lead_id=:lead_id,opportunity_id=:opportunity_id,activity_type=:activity_type,activity_date=:activity_date,result=:result,next_action=:next_action,next_followup_date=:next_followup_date,assigned_user_id=:assigned_user_id,notes=:notes,updated_at=NOW() WHERE id=:id');
        } else {
            $stmt = $this->db->prepare('INSERT INTO sales_followups (lead_id,opportunity_id,activity_type,activity_date,result,next_action,next_followup_date,assigned_user_id,notes,created_at) VALUES (:lead_id,:opportunity_id,:activity_type,:activity_date,:result,:next_action,:next_followup_date,:assigned_user_id,:notes,NOW())');
        }
        $stmt->execute($data);
    }

    private function summary(): array
    {
        $month = date('Y-m-01');
        $oppScope = $this->scopeClause();
        $leadScope = $this->scopeClause();
        $params = $this->scopeParams();

        $stmt = $this->db->prepare('SELECT COALESCE(SUM(deal_value),0) FROM sales_opportunities WHERE stage NOT IN ("lost") AND ' . $oppScope);
        $stmt->execute($params);
        $totalOpp = (float)$stmt->fetchColumn();

        $wonMonth = $this->sum('sales_opportunities', 'deal_value', 'stage = "won" AND updated_at >= :start AND ' . $oppScope, ['start' => $month] + $params);
        $leadsMonth = $this->countWhere('sales_leads', 'created_at >= :start AND ' . $leadScope, ['start' => $month] + $params);
        $wonCount = $this->countWhere('sales_opportunities', 'stage = "won" AND ' . $oppScope, $params);
        $closedCount = $this->countWhere('sales_opportunities', 'stage IN ("won","lost") AND ' . $oppScope, $params);
        return [
            'leads_month' => $leadsMonth,
            'pipeline_value' => $totalOpp,
            'won_month' => $wonMonth,
            'conversion_rate' => $closedCount > 0 ? round(($wonCount / $closedCount) * 100, 1) : 0,
            'followups_due' => $this->countWhere('sales_followups', 'next_followup_date IS NOT NULL AND next_followup_date <= CURDATE() AND ' . $this->scopeClause(), $params),
        ];
    }

    private function pipelineStats(): array
    {
        $stmt = $this->db->prepare('SELECT stage, COUNT(*) AS total, COALESCE(SUM(deal_value),0) AS value FROM sales_opportunities WHERE ' . $this->scopeClause() . ' GROUP BY stage ORDER BY FIELD(stage, "inquiry","qualification","consultation","proposal","negotiation","won","lost")');
        $stmt->execute($this->scopeParams());
        return $stmt->fetchAll();
    }

    private function topLeadSources(): array
    {
        $stmt = $this->db->prepare('SELECT COALESCE(NULLIF(source, ""), "Unknown") AS source, COUNT(*) AS total, COALESCE(SUM(estimated_value),0) AS value FROM sales_leads WHERE ' . $this->scopeClause() . ' GROUP BY COALESCE(NULLIF(source, ""), "Unknown") ORDER BY total DESC LIMIT 6');
        $stmt->execute($this->scopeParams());
        return $stmt->fetchAll();
    }

    private function upcomingFollowups(int $limit): array
    {
        $stmt = $this->db->prepare('SELECT f.*, l.company_name, o.title AS opportunity_title FROM sales_followups f LEFT JOIN sales_leads l ON l.id=f.lead_id LEFT JOIN sales_opportunities o ON o.id=f.opportunity_id WHERE f.next_followup_date IS NOT NULL AND ' . $this->scopeClause('f') . ' ORDER BY f.next_followup_date ASC LIMIT :limit');
        foreach ($this->scopeParams() as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function recentLeads(int $limit): array
    {
        $stmt = $this->db->prepare('SELECT * FROM sales_leads WHERE ' . $this->scopeClause() . ' ORDER BY created_at DESC LIMIT :limit');
        foreach ($this->scopeParams() as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function simpleLeads(): array
    {
        $stmt = $this->db->prepare('SELECT id, company_name FROM sales_leads WHERE ' . $this->scopeClause() . ' ORDER BY company_name ASC');
        $stmt->execute($this->scopeParams());
        return $stmt->fetchAll();
    }

    private function simpleOpportunities(): array
    {
        $stmt = $this->db->prepare('SELECT id, title FROM sales_opportunities WHERE ' . $this->scopeClause() . ' ORDER BY title ASC');
        $stmt->execute($this->scopeParams());
        return $stmt->fetchAll();
    }
    private function servicesList(): array { return $this->db->query('SELECT id, name, base_price FROM sales_services ORDER BY name ASC')->fetchAll(); }

    private function canViewAllSales(): bool
    {
        return $this->rbac->canViewAllForModule('sales', $this->user);
    }

    private function userId(): int
    {
        return (int)($this->user['id'] ?? 0);
    }

    private function scopeClause(string $alias = ''): string
    {
        if ($this->canViewAllSales()) {
            return '1=1';
        }
        $prefix = $alias !== '' ? $alias . '.' : '';
        return $prefix . 'assigned_user_id = :scope_user_id';
    }

    private function scopeParams(): array
    {
        return $this->canViewAllSales() ? [] : ['scope_user_id' => $this->userId()];
    }

    private function canAccessAssignedRow(array $row): bool
    {
        return $this->canViewAllSales() || (int)($row['assigned_user_id'] ?? 0) === $this->userId();
    }

    private function assignedUserInput(): ?int
    {
        if ($this->canViewAllSales()) {
            return (int)($_POST['assigned_user_id'] ?? 0) ?: null;
        }
        return $this->userId() ?: null;
    }

    private function formUsers(): array
    {
        if ($this->canViewAllSales()) {
            return $this->rbac->allUsers();
        }
        return $this->user ? [$this->user] : [];
    }

    private function findService(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        return $this->find('sales_services', $id);
    }

    private function find(string $table, int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `$table` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function delete(string $table, int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM `$table` WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function deleteAssigned(string $table, int $id, string $redirect): void
    {
        $row = $this->find($table, $id);
        if (!$row || !$this->canAccessAssignedRow($row)) {
            http_response_code(403); echo '403 - Data ini bukan milik Anda'; exit;
        }
        $this->delete($table, $id);
        header('Location: ' . app_url($redirect . '?deleted=1'));
    }

    private function sum(string $table, string $column, string $where, array $params): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM($column),0) FROM `$table` WHERE $where");
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }

    private function countWhere(string $table, string $where, array $params): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `$table` WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function money(mixed $value): float
    {
        return max(0, (float)str_replace([',', ' '], ['', ''], (string)$value));
    }

    private function ensureSchema(): void
    {
        $meta = new SalesModuleMeta();
        foreach ($meta->tables() as $schema) {
            $this->db->exec($schema);
        }
        $this->addColumnIfMissing('sales_leads', 'service_id', 'INT NULL AFTER source');
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
        $stmt->execute(['table' => $table, 'column' => $column]);
        if ((int)$stmt->fetchColumn() === 0) {
            $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    private function notFound(string $message): void
    {
        http_response_code(404);
        echo '<div class="nt-error-state"><h2>404 - ' . htmlspecialchars($message) . '</h2><a href="' . htmlspecialchars(app_url('sales')) . '">Kembali ke Sales</a></div>';
    }
}
