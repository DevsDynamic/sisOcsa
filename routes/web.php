<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProsegurController;
use App\Http\Controllers\RemittanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SapController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OsinergminController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TypeCustomerController;
use App\Http\Controllers\TypePersonController;
use App\Http\Controllers\WhatsAppController;

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('auth.login');
});

// Rutas protegidas por autenticación
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->name('dashboard');
    Route::resource('dashboard', DashboardController::class)->names('dashboard');

    //USERS//USUARIOS
    Route::resource('users', UserController::class)->names('users');
    Route::get('users.index-table', [UserController::class, 'indexTable'])->name('users.index-table');
    Route::get('users.index-admin', [UserController::class, 'indexAdmin'])->name('users.index-admin');
    Route::get('users.index-admin/data', [UserController::class, 'indexTableAdmin'])->name('users.index-admin-data');
    Route::get('users.index-customer', [UserController::class, 'indexCustomer'])->name('users.index-customer');
    Route::get('users.index-customer/data', [UserController::class, 'indexTableCustomer'])->name('users.index-customer-data');
    Route::post('/users/change-status', [UserController::class, 'changeStatus'])->name('users.change-status');
    Route::post('/users/access-system', [UserController::class, 'accessSystem'])->name('users.access-system');

    //ROLES
    Route::resource('roles', RoleController::class)->names('roles');
    Route::get('roles.index-table', [RoleController::class, 'indexTable'])->name('roles.index-table');
    Route::post('/roles/change-status', [RoleController::class, 'changeStatus'])->name('roles.change-status');
    Route::post('/roles/assign-role', [RoleController::class, 'assignRole'])->name('roles.assign-role');//Asignar rol a un usuario
    Route::post('/roles/assign-users-role', [RoleController::class, 'assignUsersRole'])->name('roles.assign-users-role');//Asignar varios usuarios a un rol 

    Route::resource('roles', RoleController::class)->names('roles');
    Route::get('roles.index/data', [RoleController::class, 'indexTable'])->name('roles.index-data');
    Route::post('roles/change-status', [RoleController::class, 'changeStatus'])->name('roles.change-status');
    Route::post('roles/assign-role', [RoleController::class, 'assignRole'])->name('roles.assign-role');//Asignar rol a un usuario
    Route::post('roles/assign-users-role', [RoleController::class, 'assignUsersRole'])->name('roles.assign-users-role');//Asignar rol a varios usuarios 

    //TYPE PEOPLE//TIPOS DE PERSONAS
    Route::resource('type-people', TypePersonController::class)->names('type-people');
    Route::get('type-people.index-table', [TypePersonController::class, 'indexTable'])->name('type-people.index-table');
    Route::post('/type-people/change-status', [TypePersonController::class, 'changeStatus'])->name('type-people.change-status');

    //PEOPLE//PERSONAS
    Route::resource('people', PersonController::class)->names('people');
    Route::get('people.index-table', [PersonController::class, 'indexTable'])->name('people.index-table');
    Route::get('people.index-co', [PersonController::class, 'indexCO'])->name('people.index-co');
    Route::get('people.index-co/data', [PersonController::class, 'indexTableCO'])->name('people.index-co-data');
    Route::get('people.index-cp', [PersonController::class, 'indexCP'])->name('people.index-cp');
    Route::get('people.index-cp/data', [PersonController::class, 'indexTableCP'])->name('people.index-cp-data');
    Route::post('/people/change-status', [PersonController::class, 'changeStatus'])->name('people.change-status');

    //OSINERGMIN//RETRANSMISION DE OSINERGMIN
    Route::resource('osinergmins', OsinergminController::class)->names('osinergmins'); //
    Route::get('osinergmins.index-table', [OsinergminController::class, 'indexTable'])->name('osinergmins.index-table');
    Route::get('osinergmins.index-units', [OsinergminController::class, 'indexUnits'])->name('osinergmins.index-units');
    Route::get('osinergmins.index-units/data', [OsinergminController::class, 'indexTableUnits'])->name('osinergmins.index-units-data');
    Route::get('/osinergmin-retransmission/{id}', [OsinergminController::class, 'retransmissionUnits'])->name('osinergmin-retransmission');

    Route::resource('reports', ReportController::class)->names('reports');
    Route::get('/reports.retransmissions', [ReportController::class, 'getRetransmissionsReport'])->name('report.retransmissions');
    Route::get('reports.osinergmin', [ReportController::class, 'reportOsinergmin'])->name('reports.osinergmin');
    Route::get('reports.view-osinergmin', [ReportController::class, 'viewReportOsinergmin'])->name('reports.view-osinergmin');
    Route::get('reports.export-osinergmin', [ReportController::class, 'exportOsinergmin'])->name('reports.export-osinergmin');

    // Pruebas de carga y lectura de imágenes
    Route::get('/upload', [ImageController::class, 'showUploadForm'])->name('image.form');
    Route::post('/upload', [ImageController::class, 'uploadImage'])->name('image.upload');

    Route::post('/send-whatsapp', [WhatsAppController::class, 'sendMessage']);
});