<?php
$case = null;
$types = \Core\Database::connection()->query('SELECT * FROM case_types ORDER BY name ASC')->fetchAll();
$users = (new \Core\Rbac())->allUsers();
$selectedTypeId = (int)($_GET['type_id'] ?? 0);
$mode = 'create';
require __DIR__ . '/form.php';
