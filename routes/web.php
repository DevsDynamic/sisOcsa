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
    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->name('dashboard');
    Route::resource('dashboard', DashboardController::class)->names('dashboard');
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
    Route::middleware('system-owner')->group(function () {
        Route::get('administracion/monitor', [IntegrationMonitorController::class, 'index'])->name('integration-monitor.index');
        Route::post('administracion/monitor/enviar', [IntegrationMonitorController::class, 'sendNow'])->name('integration-monitor.send-now');
        Route::delete('administracion/datos-demo', [IntegrationMonitorController::class, 'purgeDemo'])->name('integration-monitor.purge-demo');
    });

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
    Route::get('osinergmins.index-units/data', [OsinergminController::class, 'indexTableUnitsV2'])->name('osinergmins.index-units-data');
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
