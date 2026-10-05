<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Index::index');
$routes->get('zavod/(:num)', 'Detail::detail/$1');
$routes->get('result/stage/(:num)/(:num)', 'Detail::stageResult/$1/$2');
$routes->get('race-year/create', 'Formular::createRaceYear');
$routes->post('race-year/store', 'Formular::storeRaceYear');
