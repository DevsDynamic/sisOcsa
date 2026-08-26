<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

//use Spatie\Permission\Models\Role;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrator = Role::create(['name' => 'Administrador']);
        $customer = Role::create(['name' => 'Cliente']);

        // CONFIGURACIÓN DEL SISTEMA. El dueño obtiene acceso total mediante Gate::before.
        // Al administrador solo se le delegan los canales operativos de notificación.
        Permission::create(['name' => 'system.settings.view', 'description' => 'VER CONFIGURACIÓN DEL SISTEMA', 'module' => 'moduleSystem', 'sub_module' => 'Configuration'])->assignRole($administrator);
        Permission::create(['name' => 'system.notifications.manage', 'description' => 'CONFIGURAR CORREO Y TELEGRAM', 'module' => 'moduleSystem', 'sub_module' => 'Configuration'])->assignRole($administrator);
        Permission::create(['name' => 'system.integrations.manage', 'description' => 'CONFIGURAR AMBIENTE Y RETRANSMISIONES', 'module' => 'moduleSystem', 'sub_module' => 'Configuration']);

        // MODULO DASHBOARD
        Permission::create(['name' => 'dashboard.module', 'description' => 'MODULO DASHBOARD', 'module' => 'moduleDashboard', 'sub_module' => null])->assignRole($administrator);
        Permission::create(['name' => 'dashboard.index', 'description' => 'VER DASHBOARD', 'module' => 'moduleDashboard', 'sub_module' => 'Dashboard'])->assignRole($administrator);

        // MODULO PERSONAS
        Permission::create(['name' => 'persons.module', 'description' => 'MODULO PERSONAS', 'module' => 'modulePerson', 'sub_module' => null])->assignRole($administrator);

        // SUBMÓDULO USUARIOS
        Permission::create(['name' => 'users.submodule', 'description' => 'SUBMODULO USUARIOS', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);
        Permission::create(['name' => 'users.index', 'description' => 'VER BANDEJA DE USUARIOS', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);
        Permission::create(['name' => 'users.create', 'description' => 'CREAR USUARIO', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);
        Permission::create(['name' => 'users.edit', 'description' => 'EDITAR USUARIO', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);
        Permission::create(['name' => 'users.assign_role', 'description' => 'ASIGNAR ROL', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);
        Permission::create(['name' => 'users.access', 'description' => 'ACCESO AL SISTEMA', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);
        Permission::create(['name' => 'users.show', 'description' => 'VER USUARIO', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);
        Permission::create(['name' => 'users.destroy', 'description' => 'ELIMINAR USUARIO', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);
        Permission::create(['name' => 'users.change_status', 'description' => 'CAMBIAR ESTADO DE USUARIO (ACTIVO/INACTIVO)', 'module' => 'modulePerson', 'sub_module' => 'Users'])->assignRole($administrator);

        // SUBMÓDULO ROLES
        Permission::create(['name' => 'roles.submodule', 'description' => 'SUBMODULO ROLES', 'module' => 'modulePerson', 'sub_module' => 'Roles'])->assignRole($administrator);
        Permission::create(['name' => 'roles.index', 'description' => 'VER BANDEJA DE ROLES', 'module' => 'modulePerson', 'sub_module' => 'Roles'])->assignRole($administrator);
        Permission::create(['name' => 'roles.create', 'description' => 'CREAR ROL', 'module' => 'modulePerson', 'sub_module' => 'Roles'])->assignRole($administrator);
        Permission::create(['name' => 'roles.edit', 'description' => 'EDITAR ROL', 'module' => 'modulePerson', 'sub_module' => 'Roles'])->assignRole($administrator);
        Permission::create(['name' => 'roles.assign_role', 'description' => 'ASIGNAR ROL', 'module' => 'modulePerson', 'sub_module' => 'Roles'])->assignRole($administrator);
        Permission::create(['name' => 'roles.show', 'description' => 'VER ROL', 'module' => 'modulePerson', 'sub_module' => 'Roles'])->assignRole($administrator);
        Permission::create(['name' => 'roles.destroy', 'description' => 'ELIMINAR ROL', 'module' => 'modulePerson', 'sub_module' => 'Roles'])->assignRole($administrator);
        Permission::create(['name' => 'roles.change_status', 'description' => 'CAMBIAR ESTADO DE ROL (ACTIVO/INACTIVO)', 'module' => 'modulePerson', 'sub_module' => 'Roles'])->assignRole($administrator);

        // SUBMÓDULO TIPOS DE CLIENTES
        Permission::create(['name' => 'type-people.submodule', 'description' => 'SUBMODULO TIPOS DE CLIENTES', 'module' => 'modulePerson', 'sub_module' => 'Type-people'])->assignRole($administrator);
        Permission::create(['name' => 'type-people.index', 'description' => 'VER BANDEJA DE TIPOS DE CLIENTES', 'module' => 'modulePerson', 'sub_module' => 'Type-people'])->assignRole($administrator);
        Permission::create(['name' => 'type-people.create', 'description' => 'CREAR TIPO DE CLIENTE', 'module' => 'modulePerson', 'sub_module' => 'Type-people'])->assignRole($administrator);
        Permission::create(['name' => 'type-people.edit', 'description' => 'EDITAR TIPO DE CLIENTE', 'module' => 'modulePerson', 'sub_module' => 'Type-people'])->assignRole($administrator);
        Permission::create(['name' => 'type-people.show', 'description' => 'VER TIPO DE CLIENTE', 'module' => 'modulePerson', 'sub_module' => 'Type-people'])->assignRole($administrator);
        Permission::create(['name' => 'type-people.destroy', 'description' => 'ELIMINAR TIPO DE CLIENTE', 'module' => 'modulePerson', 'sub_module' => 'Type-people'])->assignRole($administrator);
        Permission::create(['name' => 'type-people.change_status', 'description' => 'CAMBIAR ESTADO DE TIPO DE CLIENTE (ACTIVO/INACTIVO)', 'module' => 'modulePerson', 'sub_module' => 'Type-people'])->assignRole($administrator);

        // SUBMÓDULO CLIENTES
        Permission::create(['name' => 'people.submodule', 'description' => 'SUBMODULO CLIENTES', 'module' => 'modulePerson', 'sub_module' => 'People'])->assignRole($administrator);
        Permission::create(['name' => 'people.index', 'description' => 'VER BANDEJA DE CLIENTES', 'module' => 'modulePerson', 'sub_module' => 'People'])->assignRole($administrator);
        Permission::create(['name' => 'people.create', 'description' => 'CREAR CLIENTE', 'module' => 'modulePerson', 'sub_module' => 'People'])->assignRole($administrator);
        Permission::create(['name' => 'people.edit', 'description' => 'EDITAR CLIENTE', 'module' => 'modulePerson', 'sub_module' => 'People'])->assignRole($administrator);
        Permission::create(['name' => 'people.show', 'description' => 'VER CLIENTE', 'module' => 'modulePerson', 'sub_module' => 'People'])->assignRole($administrator);
        Permission::create(['name' => 'people.destroy', 'description' => 'ELIMINAR CLIENTE', 'module' => 'modulePerson', 'sub_module' => 'People'])->assignRole($administrator);
        Permission::create(['name' => 'people.change_status', 'description' => 'CAMBIAR ESTADO DE CLIENTE (ACTIVO/INACTIVO)', 'module' => 'modulePerson', 'sub_module' => 'People'])->assignRole($administrator);

        // SUBMÓDULO COMPANIES
        Permission::create(['name' => 'companies.submodule', 'description' => 'SUBMODULO EMPRESA', 'module' => 'modulePerson', 'sub_module' => 'Companies'])->assignRole($administrator);
        Permission::create(['name' => 'companies.index', 'description' => 'VER BANDEJA DE EMPRESA', 'module' => 'modulePerson', 'sub_module' => 'Companies'])->assignRole($administrator);
        Permission::create(['name' => 'companies.create', 'description' => 'CREAR EMPRESA', 'module' => 'modulePerson', 'sub_module' => 'Companies'])->assignRole($administrator);
        Permission::create(['name' => 'companies.edit', 'description' => 'EDITAR EMPRESA', 'module' => 'modulePerson', 'sub_module' => 'Companies'])->assignRole($administrator);
        Permission::create(['name' => 'companies.show', 'description' => 'VER EMPRESA', 'module' => 'modulePerson', 'sub_module' => 'Companies'])->assignRole($administrator);
        Permission::create(['name' => 'companies.destroy', 'description' => 'ELIMINAR EMPRESA', 'module' => 'modulePerson', 'sub_module' => 'Companies'])->assignRole($administrator);
        Permission::create(['name' => 'companies.change_status', 'description' => 'CAMBIAR ESTADO DE EMPRESA (ACTIVO/INACTIVO)', 'module' => 'modulePerson', 'sub_module' => 'Companies'])->assignRole($administrator);
    }
}
