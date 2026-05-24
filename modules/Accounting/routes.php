<?php

require_once __DIR__ . '/controller.php';

$ctl = new AccountingController();

$router->get('accounting', fn() => $ctl->overview());
$router->get('accounting/report', fn() => $ctl->report());
$router->get('accounting/income', fn() => $ctl->income());
$router->get('accounting/income/edit/{id}', fn(int $id) => $ctl->editIncome($id));
$router->get('accounting/expenses', fn() => $ctl->expenses());
$router->get('accounting/expenses/edit/{id}', fn(int $id) => $ctl->editExpense($id));
$router->get('accounting/receivables', fn() => $ctl->receivables());
$router->get('accounting/receivables/edit/{id}', fn(int $id) => $ctl->editReceivable($id));

$router->post('accounting/income', fn() => $ctl->storeIncome());
$router->post('accounting/income/update/{id}', fn(int $id) => $ctl->updateIncome($id));
$router->post('accounting/income/delete/{id}', fn(int $id) => $ctl->deleteIncome($id));
$router->post('accounting/expenses', fn() => $ctl->storeExpense());
$router->post('accounting/expenses/update/{id}', fn(int $id) => $ctl->updateExpense($id));
$router->post('accounting/expenses/delete/{id}', fn(int $id) => $ctl->deleteExpense($id));
$router->post('accounting/receivables', fn() => $ctl->storeReceivable());
$router->post('accounting/receivables/update/{id}', fn(int $id) => $ctl->updateReceivable($id));
$router->post('accounting/receivables/delete/{id}', fn(int $id) => $ctl->deleteReceivable($id));
