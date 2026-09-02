<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        $permissions = [
            'integration.monitor.view' => 'VER MONITOR DE INTEGRACIÃ“N',
            'integration.monitor.execute' => 'EJECUTAR RETRANSMISIÃ“N MANUAL',
            'integration.monitor.purge_demo' => 'ELIMINAR DATOS DEMO',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'], [
                'description' => $description,
                'module' => 'moduleSystem',
                'sub_module' => 'IntegrationMonitor',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'integration.monitor.view', 'integration.monitor.execute', 'integration.monitor.purge_demo',
        ])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
