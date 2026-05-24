<?php

use Core\Database;
use Core\ModuleLink;

class CasesSalesWonListener
{
    public function __invoke(array $payload): void
    {
        $opportunityId = (int)($payload['opportunity_id'] ?? 0);
        if ($opportunityId <= 0) {
            return;
        }

        $db = Database::connection();
        if (ModuleLink::targetId($db, 'sales', 'opportunity', $opportunityId, 'cases', 'case')) {
            return;
        }

        $stmt = $db->prepare(
            'SELECT o.*, s.name AS service_name, l.company_name AS lead_company
             FROM sales_opportunities o
             LEFT JOIN sales_services s ON s.id = o.service_id
             LEFT JOIN sales_leads l ON l.id = o.lead_id
             WHERE o.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $opportunityId]);
        $opportunity = $stmt->fetch();
        if (!$opportunity) {
            return;
        }

        $typeId = $this->findOrCreateType($db, 'Sales Won');
        $title = 'Sales Won - ' . ($opportunity['title'] ?: $opportunity['client_name']);
        $info = "Client: " . ($opportunity['client_name'] ?: $opportunity['lead_company'] ?: '-') .
            "\nService: " . ($opportunity['service_name'] ?: '-') .
            "\nDeal Value: " . (string)$opportunity['deal_value'] .
            "\nExpected Close: " . ($opportunity['expected_close_date'] ?: '-');

        $insert = $db->prepare(
            'INSERT INTO cases (type_id, title, description, priority, status, deadline, personal_note, information, assigned_user_ids, created_at)
             VALUES (:type_id, :title, :description, "normal", "verification", :deadline, "", :information, :assigned_user_ids, NOW())'
        );
        $insert->execute([
            'type_id' => $typeId,
            'title' => $title,
            'description' => $opportunity['notes'] ?: 'Auto-created from Sales opportunity won.',
            'deadline' => $opportunity['expected_close_date'] ?: null,
            'information' => $info,
            'assigned_user_ids' => json_encode(array_values(array_filter([(int)($opportunity['assigned_user_id'] ?? 0)]))),
        ]);

        ModuleLink::create($db, 'sales', 'opportunity', $opportunityId, 'cases', 'case', (int)$db->lastInsertId());
    }

    private function findOrCreateType(PDO $db, string $name): int
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $stmt = $db->prepare('SELECT id FROM case_types WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int)$id;
        }
        $stmt = $db->prepare('INSERT INTO case_types (name, slug, created_at) VALUES (:name, :slug, NOW())');
        $stmt->execute(['name' => $name, 'slug' => $slug]);
        return (int)$db->lastInsertId();
    }
}
