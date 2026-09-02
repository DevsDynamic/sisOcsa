<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePrivilegeEscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_cannot_grant_a_permission_they_do_not_have(): void
    {
        $administrator = User::factory()->create();
        $createRoles = $this->permission('roles.create', 'CREAR ROLES');
        $allowed = $this->permission('people.index', 'VER CLIENTES');
        $forbidden = $this->permission('system.integrations.manage', 'CONFIGURAR INTEGRACIONES');
        $administrator->givePermissionTo([$createRoles, $allowed]);

        $this->actingAs($administrator)->get(route('roles.create'))
            ->assertOk()->assertSee('VER CLIENTES')->assertDontSee('CONFIGURAR INTEGRACIONES');

        $this->actingAs($administrator)->post(route('roles.store'), [
            'name' => 'Rol elevado',
            'permissions' => [$allowed->id, $forbidden->id],
        ])->assertSessionHasErrors('permissions.1');

        $this->assertDatabaseMissing('roles', ['name' => 'Rol elevado']);
    }

    public function test_administrator_cannot_assign_a_role_with_superior_permissions(): void
    {
        $administrator = User::factory()->create();
        $target = User::factory()->create();
        $assignRoles = $this->permission('roles.assign_role', 'ASIGNAR ROLES');
        $superior = $this->permission('system.integrations.manage', 'CONFIGURAR INTEGRACIONES');
        $administrator->givePermissionTo($assignRoles);
        $role = Role::create(['name' => 'Dueño técnico', 'guard_name' => 'web', 'status' => true]);
        $role->givePermissionTo($superior);

        $this->actingAs($administrator)->post(route('roles.assign-role'), [
            'user_id' => $target->id,
            'roles' => $role->name,
        ])->assertForbidden();

        $this->assertFalse($target->fresh()->hasRole($role));
    }

    public function test_roles_index_permission_does_not_authorize_role_creation(): void
    {
        $administrator = User::factory()->create();
        $administrator->givePermissionTo($this->permission('roles.index', 'VER ROLES'));

        $this->actingAs($administrator)->get(route('roles.create'))->assertForbidden();
    }

    private function permission(string $name, string $description): Permission
    {
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'], [
            'description' => $description,
            'module' => 'moduleSystem',
            'sub_module' => 'Roles',
        ]);
    }
}
