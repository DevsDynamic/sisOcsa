<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('osinergmins', function (Blueprint $table) {
            $table->text('response_message')->nullable()->change();
            $table->text('response_suggestion')->nullable()->change();
            $table->index(
                ['environment', 'person_id', 'created_at', 'id'],
                'osin_env_person_created_id_index'
            );
            $table->index(
                ['environment', 'person_id', 'uuid', 'created_at', 'id'],
                'osin_env_person_uuid_created_id_index'
            );
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::firstOrCreate(
            ['name' => 'osinergmins.manage', 'guard_name' => 'web'],
            [
                'description' => 'VER RETRANSMISIONES DE TODOS LOS CLIENTES',
                'module' => 'moduleRetransmission',
                'sub_module' => 'Osinergmin',
            ]
        );

        Role::where('name', 'Administrador')->first()?->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('osinergmins', function (Blueprint $table) {
            $table->dropIndex('osin_env_person_created_id_index');
            $table->dropIndex('osin_env_person_uuid_created_id_index');
            $table->string('response_message')->nullable()->change();
            $table->string('response_suggestion')->nullable()->change();
        });

        Permission::where('name', 'osinergmins.manage')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
