<?php

use Core\Database;
use Core\ModuleLink;

class AccountingSalesWonListener
{
    public function __invoke(array $payload): void
    {
        $opportunityId = (int)($payload['opportunity_id'] ?? 0);
        if ($opportunityId <= 0) {
            return;
        }

        $db = Database::connection();
        if (ModuleLink::targetId($db, 'sales', 'opportunity', $opportunityId, 'accounting', 'income')) {
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

        $insert = $db->prepare(
            'INSERT INTO accounting_incomes
                (source_id, client_name, title, amount, received_date, payment_method, reference_no, record_state, notes, created_at)
             VALUES
                (NULL, :client_name, :title, :amount, CURDATE(), "", :reference_no, "draft", :notes, NOW())'
        );
        $insert->execute([
            'client_name' => $opportunity['client_name'] ?: $opportunity['lead_company'] ?: 'Sales Client',
            'title' => $opportunity['title'] ?: 'Sales Won Opportunity',
            'amount' => (float)$opportunity['deal_value'],
            'reference_no' => 'SALES-OPP-' . $opportunityId,
            'notes' => 'Draft pemasukan auto-created from Sales Won. Service: ' . ($opportunity['service_name'] ?: '-'),
        ]);

        ModuleLink::create($db, 'sales', 'opportunity', $opportunityId, 'accounting', 'income', (int)$db->lastInsertId());
    }
}
