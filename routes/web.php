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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\IntegrationMonitorController;
use App\Http\Controllers\PublicIntegrationStatusController;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard.index') : redirect()->route('login');
});

Route::get('estado-integracion', PublicIntegrationStatusController::class)
    ->middleware(['signed', 'throttle:30,1'])
    ->name('integration-status.public');

// Rutas protegidas por autenticación
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('dashboard/status', [DashboardController::class, 'status'])->middleware('throttle:30,1')->name('dashboard.status');
    Route::get('mi-perfil', [ProfileController::class, 'edit'])->name('profile.account');
    Route::put('mi-perfil', [ProfileController::class, 'update'])->name('profile.account.update');
    Route::put('mi-perfil/password', [ProfileController::class, 'password'])->name('profile.account.password');
    Route::post('mi-perfil/foto', [ProfileController::class, 'photo'])->name('profile.account.photo');
    Route::get('mi-perfil/foto', [ProfileController::class, 'photoContent'])->name('profile.account.photo.show');
    Route::middleware('can:system.settings.view')->group(function () {
        Route::get('administracion/configuracion', [SystemSettingController::class, 'edit'])->name('system-settings.edit');
        Route::put('administracion/configuracion', [SystemSettingController::class, 'update'])
            ->middleware('can:system.integrations.manage')->name('system-settings.update');
        Route::put('administracion/configuracion/correo', [SystemSettingController::class, 'updateMail'])
            ->middleware('can:system.notifications.manage')->name('system-settings.update-mail');
        Route::post('administracion/configuracion/probar-correo', [SystemSettingController::class, 'testMail'])
            ->middleware('can:system.notifications.manage')->name('system-settings.test-mail');
        Route::put('administracion/configuracion/telegram', [SystemSettingController::class, 'updateTelegram'])
            ->middleware('can:system.notifications.manage')->name('system-settings.update-telegram');
        Route::post('administracion/configuracion/probar-telegram', [SystemSettingController::class, 'testTelegram'])
            ->middleware('can:system.notifications.manage')->name('system-settings.test-telegram');
    });
    Route::get('administracion/monitor', [IntegrationMonitorController::class, 'index'])
        ->middleware('can:integration.monitor.view')->name('integration-monitor.index');
    Route::post('administracion/monitor/enviar', [IntegrationMonitorController::class, 'sendNow'])
        ->middleware('can:integration.monitor.execute')->name('integration-monitor.send-now');
    Route::delete('administracion/datos-demo', [IntegrationMonitorController::class, 'purgeDemo'])
        ->middleware('can:integration.monitor.purge_demo')->name('integration-monitor.purge-demo');

    //USERS//USUARIOS
    Route::get('users', [UserController::class, 'index'])->middleware('can:users.index')->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->middleware('can:users.create')->name('users.create');
    Route::post('users', [UserController::class, 'store'])->middleware('can:users.create')->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('can:users.show')->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->middleware('can:users.edit')->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->middleware('can:users.edit')->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('can:users.destroy')->name('users.destroy');
    Route::middleware('can:users.index')->group(function () {
        Route::get('users.index-table', [UserController::class, 'indexTable'])->name('users.index-table');
        Route::get('users.index-admin', [UserController::class, 'indexAdmin'])->name('users.index-admin');
        Route::get('users.index-admin/data', [UserController::class, 'indexTableAdmin'])->name('users.index-admin-data');
        Route::get('users.index-customer', [UserController::class, 'indexCustomer'])->name('users.index-customer');
        Route::get('users.index-customer/data', [UserController::class, 'indexTableCustomer'])->name('users.index-customer-data');
    });
    Route::post('/users/change-status', [UserController::class, 'changeStatus'])->middleware('can:users.change_status')->name('users.change-status');
    Route::post('/users/access-system', [UserController::class, 'accessSystem'])->middleware('can:users.access')->name('users.access-system');

    //ROLES
    Route::resource('roles', RoleController::class)->middleware('can:roles.index')->names('roles');
    Route::get('roles.index/data', [RoleController::class, 'indexTable'])->middleware('can:roles.index')->name('roles.index-data');
    Route::post('roles/change-status', [RoleController::class, 'changeStatus'])->middleware('can:roles.change_status')->name('roles.change-status');
    Route::post('roles/assign-role', [RoleController::class, 'assignRole'])->middleware('can:roles.assign_role')->name('roles.assign-role');
    Route::post('roles/assign-users-role', [RoleController::class, 'assignUsersRole'])->middleware('can:roles.assign_role')->name('roles.assign-users-role');

    //TYPE PEOPLE//TIPOS DE PERSONAS
    Route::resource('type-people', TypePersonController::class)->middleware('can:type-people.index')->names('type-people');
    Route::get('type-people.index-table', [TypePersonController::class, 'indexTable'])->middleware('can:type-people.index')->name('type-people.index-table');
    Route::post('/type-people/change-status', [TypePersonController::class, 'changeStatus'])->middleware('can:type-people.change_status')->name('type-people.change-status');

    //PEOPLE//PERSONAS
    Route::resource('people', PersonController::class)->middleware('can:people.index')->names('people');
    Route::get('people.index-table', [PersonController::class, 'indexTable'])->middleware('can:people.index')->name('people.index-table');
    Route::get('people.index-co', [PersonController::class, 'indexCO'])->middleware('can:people.index')->name('people.index-co');
    Route::get('people.index-co/data', [PersonController::class, 'indexTableCO'])->middleware('can:people.index')->name('people.index-co-data');
    Route::get('people.index-cp', [PersonController::class, 'indexCP'])->middleware('can:people.index')->name('people.index-cp');
    Route::get('people.index-cp/data', [PersonController::class, 'indexTableCP'])->middleware('can:people.index')->name('people.index-cp-data');
    Route::post('/people/change-status', [PersonController::class, 'changeStatus'])->middleware('can:people.change_status')->name('people.change-status');
    Route::post('/people/{person}/convert', [PersonController::class, 'convert'])->middleware('can:people.edit')->name('people.convert');
    Route::get('/people/{person}/history', [PersonController::class, 'history'])->middleware('can:people.show')->name('people.history');

    //OSINERGMIN//RETRANSMISION DE OSINERGMIN
    Route::get('osinergmins', [OsinergminController::class, 'index'])->middleware('can:osinergmins.manage')->name('osinergmins.index');
    Route::get('osinergmins.index-table', [OsinergminController::class, 'indexTable'])->middleware('can:osinergmins.manage')->name('osinergmins.index-table');
    Route::get('osinergmins.index-units', [OsinergminController::class, 'indexUnits'])->name('osinergmins.index-units');
    Route::get('osinergmins.index-units/data', [OsinergminController::class, 'indexTableUnitsV2'])->name('osinergmins.index-units-data');
    Route::get('/osinergmin-retransmission/{id}', [OsinergminController::class, 'retransmissionUnits'])->name('osinergmin-retransmission');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports.retransmissions', [ReportController::class, 'getRetransmissionsReport'])->name('report.retransmissions');
    Route::get('reports.osinergmin', [ReportController::class, 'reportOsinergmin'])->name('reports.osinergmin');
    Route::get('reports.view-osinergmin', [ReportController::class, 'viewReportOsinergmin'])->name('reports.view-osinergmin');
    Route::get('reports.export-osinergmin', [ReportController::class, 'exportOsinergmin'])->name('reports.export-osinergmin');
});
