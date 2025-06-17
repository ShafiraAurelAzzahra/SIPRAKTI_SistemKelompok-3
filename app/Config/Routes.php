<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

$routes->get('/', 'Home::index');
$routes->get('/item', 'Home::item');

service('auth')->routes($routes);

$routes->group('admin', ['filter' => 'session'], static function (RouteCollection $routes) {
    $routes->get('/', 'Dashboard\DashboardController');
    $routes->get('dashboard', 'Dashboard\DashboardController::dashboard');

    $routes->resource('members', ['controller' => 'Members\MembersController']);
    $routes->resource('items', ['controller' => 'Items\ItemsController']);
    $routes->resource('categories', ['controller' => 'Items\CategoriesController']);
    $routes->resource('racks', ['controller' => 'Items\RacksController']);

    $routes->get('loans/new/members/search', 'Loans\LoansController::searchMember');
    $routes->get('loans/new/items/search', 'Loans\LoansController::searchItem');
    $routes->post('loans/new', 'Loans\LoansController::new');
    $routes->resource('loans', ['controller' => 'Loans\LoansController']);

    $routes->get('returns/new/search', 'Loans\ReturnsController::searchLoan');
    $routes->resource('returns', ['controller' => 'Loans\ReturnsController']);

    $routes->get('fines/returns/search', 'Loans\FinesController::searchReturn');
    $routes->get('fines/pay/(:any)', 'Loans\FinesController::pay/$1');
    $routes->resource('fines/settings', ['controller' => 'Loans\FineSettingsController', 'filter' => 'group:superadmin']);
    $routes->resource('fines', ['controller' => 'Loans\FinesController']);

    $routes->group('users', ['filter' => 'group:superadmin'], static function (RouteCollection $routes) {
        $routes->get('new', 'Users\RegisterController::index');
        $routes->post('', 'Users\RegisterController::registerAction');
    });
    $routes->resource('users', ['controller' => 'Users\UsersController', 'filter' => 'group:superadmin']);
});

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
