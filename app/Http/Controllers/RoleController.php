<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
// use Spatie\Permission\Models\Role;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
// use DataTables;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $users = User::all();
        return view('roles.index', compact('users'));
    }

    public function indexTable(){
        $roles = Role::select(
                            'roles.id AS id',
                            'roles.name AS name',
                            'roles.guard_name AS guard_name',
                            'roles.status AS status',
                            'roles.created_at AS created_date'                            
                            )
                        ->get();
       
        return Datatables::of($roles)
                        ->addIndexColumn()
                        ->addColumn('action', function ($role) {
                            $buttons = '';                            
                            
                            // Obtener usuarios con el rol específico
                            $usersWithRole = User::whereHas('roles', function ($query) use ($role) {
                                $query->where('roles.id', $role->id);
                            })->get()->map(function ($user) {
                                return [
                                    'id' => $user->id,
                                    'name' => $user->username,
                                    'profile_photo_url' => $user->profile_photo_url,
                                ];
                            })->toArray();

                            // Obtener usuarios sin el rol específico
                            $usersWithoutRole = User::whereDoesntHave('roles', function ($query) use ($role) {
                                $query->where('roles.id', $role->id);
                            })->get()->map(function ($user) {
                                return [
                                    'id' => $user->id,
                                    'name' => $user->username,
                                    'profile_photo_url' => $user->profile_photo_url,
                                ];
                            })->toArray();

                            $usersWithRoleJson = json_encode($usersWithRole);
                            $usersWithoutRoleJson = json_encode($usersWithoutRole);

                            if ($role->status != '0') {
                                // Ver rol
                                if (auth()->user()->can('roles.show')) {
                                    $buttons .= '<button class="btn btn-info btn-sm mr-1 mb-1 ver-rol" 
                                                    data-id="' . $role->id . '">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>';
                                }
                                // Editar rol
                                if (auth()->user()->can('roles.edit')) {
                                    $buttons .= '<a href="' . route('roles.edit', $role->id) . '" class="btn btn-warning btn-sm mr-1 mb-1" title="Editar usuario"><i class="fas fa-tasks"></i> Asignar permisos</a> ';
                                }                                
                                // Asignar rol
                                if (auth()->user()->can('roles.assign_role')) {
                                    $buttons .= '<a href="" data-target="#modal-assign-role" data-toggle="modal" data-id="' . $role->id . '" data-name="' . $role->name . '" data-users-with-role=\'' . $usersWithRoleJson . '\' data-users-without-role=\'' . $usersWithoutRoleJson . '\'>
                                                    <button class="btn btn-primary btn-sm mr-1 mb-1" title="Asignar rol">
                                                        <i class="fas fa-user-shield"></i> Asignar rol
                                                    </button>
                                                </a>';
                                }
                                // Cambiar estado del rol
                                if (auth()->user()->can('roles.change_status')) {                                    
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $role->id . '" data-status="inactivar">
                                                    <button class="btn btn-sm mr-1 mb-1 btn-danger" title="Inactivar rol">
                                                        <i class="fas fa-times-circle"></i> Inactivar
                                                    </button>
                                                </a>';
                                }
                            } else {
                                // Activar rol
                                if (auth()->user()->can('roles.change_status')) {
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $role->id . '" data-status="activar">
                                                    <button class="btn btn-sm mr-1 mb-1 btn-success" title="Activar usuario">
                                                        <i class="fas fa-check-circle"></i> Activar
                                                    </button>
                                                </a>';
                                }
                            }
                    
                            // Mostrar botones o mensaje de sin permisos
                            if (!empty($buttons)) {
                                return $buttons;
                            } else {
                                return '<span class="badge badge-secondary">SIN PERMISOS</span>';
                            }
                        })
                        ->rawColumns(['action'])
                        ->make(true);                    
    }

    public function create()
    {
        // Obtener todos los permisos
        $permissions = Permission::all();

        // Organizar los permisos por módulos y submódulos
        $groupedPermissions = $permissions->groupBy('module')->map(function ($moduleGroup) {
            return $moduleGroup->groupBy('sub_module');
        });

        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'name' => 'required|string|max:255', // Asegura que el campo 'name' esté presente y sea una cadena de máximo 255 caracteres
            'permissions' => 'required|array|min:1', // Asegura que 'permissions' sea un arreglo y tenga al menos un elemento
        ], [
            'permissions.min' => 'Debe seleccionar al menos un permiso.', // Mensaje personalizado de error para el mínimo de permisos
        ]);

        // Crear el rol
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web', // Asegura que el guard_name sea 'web' si es el caso en tu configuración
        ]);

        // Asignar permisos al rol
        $role->permissions()->sync($request->permissions);

        // Redireccionar a la lista de roles con un mensaje de éxito
        return redirect()->route('roles.index')->with('success', 'Rol registrado correctamente.');
    }

    public function show($roleId)
    {
        // Obtener el rol por ID
        $role = Role::findOrFail($roleId);
    
        // Obtener permisos asignados al rol específico
        $rolePermissions = $role->permissions;
    
        // Organizar los permisos por módulos y submódulos
        $groupedPermissions = $rolePermissions->groupBy('module')->map(function ($moduleGroup) {
            return $moduleGroup->groupBy('sub_module');
        });
    
        // Devolver los datos como JSON para la llamada AJAX
        return response()->json([
            'role' => $role->name, // Nombre del rol
            'permissions' => $groupedPermissions
        ]);
    }    

    public function edit(Role $role)
    {
        // Obtener todos los permisos
        $permissions = Permission::all();
    
        // Obtener permisos del rol
        $rolePermissions = $role->permissions->pluck('id')->toArray();
    
        // Organizar los permisos por módulos y submódulos
        $groupedPermissions = $permissions->groupBy('module')->map(function ($moduleGroup) {
            return $moduleGroup->groupBy('sub_module');
        });
    
        return view('roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }
  
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $role->update($request->all());

        $role->permissions()->sync($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado con éxito.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with('info', 'ELIMINADO');
    }

    public function assignRole(Request $request)
    {
        $user = User::findOrFail($request->input('user_id'));
        $role = $request->input('roles');

        // Remover todos los roles actuales del usuario
        $user->roles()->detach();

        // Asignar el nuevo rol
        $user->assignRole($role);

        return response()->json([
            'success' => 'rol <strong>' . $role . '</strong> asignado exitosamente al usuario seleccionado.',
            'status' => $role
        ]);
    }

    public function assignUsersRole(Request $request)
    {
        // Obtener datos del formulario
        $roleId = $request->input('role_id');
        $userIds = $request->input('users');
    
        try {
            // Buscar el rol por su ID
            $role = Role::findOrFail($roleId);
    
            // **1. Quitar el rol de todos los usuarios (asegurarse de que no tienen más de un rol)**
            if (empty($userIds)) {
                // Si no hay usuarios seleccionados, quitar el rol de todos los usuarios
                User::each(function ($user) use ($role) {
                    $user->roles()->detach($role->id); // Se quita el rol específico de todos los usuarios
                });
    
                return response()->json([
                    'success' => 'Rol <strong>'.$role->name.'</strong> eliminado de todos los usuarios.',
                    'status' => 'success'
                ]);
            }
    
            // **2. Si hay usuarios seleccionados, asignar el nuevo rol solo a los usuarios seleccionados**
            User::each(function ($user) use ($role) {
                $user->roles()->detach($role->id); // Se quita el rol específico de todos los usuarios
            });
    
            User::whereIn('id', $userIds)->each(function ($user) use ($role) {
                $user->roles()->detach();  // Eliminar todos los roles del usuario antes de asignar uno nuevo
                $user->assignRole($role);  // Asignamos solo el rol nuevo
            });
    
            return response()->json([
                'success' => 'Rol <strong>'.$role->name.'</strong> asignado exitosamente a los usuarios seleccionados.',
                'status' => 'success'
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al asignar rol: ' . $e->getMessage(),
                'status' => 'error'
            ], 400);
        }
    }    

    public function changeStatus(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'status_action' => 'required|in:activar,inactivar'
        ]);

        $role = Role::find($request->role_id);

        if (!$role) {
            return response()->json([
                'error' => 'Rol no encontrado.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Determinar el nuevo estado
            $newStatus = $request->status_action === 'activar' ? 1 : 0;

            // Actualizar el estado del rol
            $role->status = $newStatus;
            $role->save();

            DB::commit();

            return response()->json([
                'success' => 'Estado del rol cambiado exitosamente a <strong>' . ($newStatus ? 'Activo' : 'Inactivo') . '</strong>',
                'status' => $newStatus
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al cambiar el estado del rol: ' . $th->getMessage()
            ], 500);
        }
    }
}