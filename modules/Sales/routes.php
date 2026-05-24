<?php

require_once __DIR__ . '/controller.php';

$ctl = new SalesController();

$router->get('sales', fn() => $ctl->overview());
$router->get('sales/leads', fn() => $ctl->leads());
$router->get('sales/leads/edit/{id}', fn(int $id) => $ctl->editLead($id));
$router->get('sales/opportunities', fn() => $ctl->opportunities());
$router->get('sales/opportunities/edit/{id}', fn(int $id) => $ctl->editOpportunity($id));
$router->get('sales/services', fn() => $ctl->services());
$router->get('sales/services/edit/{id}', fn(int $id) => $ctl->editService($id));
$router->get('sales/followups', fn() => $ctl->followups());
$router->get('sales/followups/edit/{id}', fn(int $id) => $ctl->editFollowup($id));

$router->post('sales/leads', fn() => $ctl->storeLead());
$router->post('sales/leads/update/{id}', fn(int $id) => $ctl->updateLead($id));
$router->post('sales/leads/delete/{id}', fn(int $id) => $ctl->deleteLead($id));
$router->post('sales/opportunities', fn() => $ctl->storeOpportunity());
$router->post('sales/opportunities/update/{id}', fn(int $id) => $ctl->updateOpportunity($id));
$router->post('sales/opportunities/delete/{id}', fn(int $id) => $ctl->deleteOpportunity($id));
$router->post('sales/services', fn() => $ctl->storeService());
$router->post('sales/services/update/{id}', fn(int $id) => $ctl->updateService($id));
$router->post('sales/services/delete/{id}', fn(int $id) => $ctl->deleteService($id));
$router->post('sales/followups', fn() => $ctl->storeFollowup());
$router->post('sales/followups/update/{id}', fn(int $id) => $ctl->updateFollowup($id));
$router->post('sales/followups/delete/{id}', fn(int $id) => $ctl->deleteFollowup($id));
