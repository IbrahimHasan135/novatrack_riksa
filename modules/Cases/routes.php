<?php
// modules/Cases/routes.php
// Tidak perlu namespace: file ini di-load di global scope oleh Module::loadRoutes()
// urutan require: routes.php dimuat dari Module::loadRoutes() di index.php

require_once __DIR__ . '/controller.php';

$ctl = new CasesController();

// GET
$router->get('cases', fn() => $ctl->index());
$router->get('cases/type/{id}', fn(int $id) => $ctl->type($id));
$router->get('cases/create', fn() => $ctl->create());
$router->get('cases/detail/{id}', fn(int $id) => $ctl->detail($id));
$router->get('cases/edit/{id}', fn(int $id) => $ctl->edit($id));

// POST
$router->post('cases', fn() => $ctl->store());
$router->post('cases/update/{id}', fn(int $id) => $ctl->update($id));
$router->post('cases/delete/{id}', fn(int $id) => $ctl->delete($id));
$router->post('cases/types', fn() => $ctl->storeType());
$router->post('cases/types/delete/{id}', fn(int $id) => $ctl->deleteType($id));
