<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $definitions = [
            'system.settings.view' => 'VER CONFIGURACIÓN DEL SISTEMA',
            'system.notifications.manage' => 'CONFIGURAR CORREO Y TELEGRAM',
            'system.integrations.manage' => 'CONFIGURAR AMBIENTE Y RETRANSMISIONES',
        ];

        foreach ($definitions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $description, 'module' => 'moduleSystem', 'sub_module' => 'Configuration']
            );
        }

        // El administrador puede operar canales de alerta. Las credenciales y
        // ambientes de las entidades reguladoras permanecen reservados al dueño.
        if ($administrator = Role::where('name', 'Administrador')->first()) {
            $administrator->givePermissionTo([
                'system.settings.view',
                'system.notifications.manage',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'system.settings.view',
            'system.notifications.manage',
            'system.integrations.manage',
        ])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
