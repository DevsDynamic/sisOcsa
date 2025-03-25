<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
//use Spatie\Permission\Models\Role;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
//use DataTables;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::get();
        return view('users.index', compact('roles'));
    }

    public function indexTable(){
        $users = User::join('role_user as ru', 'users.id', 'ru.user_id')
                        ->join('roles as r', 'ru.role_id', 'r.id')
                        ->select(
                            'users.id AS id',
                            'users.full_name AS full_name',//DB::raw("CONCAT(users.lastname, ', ', users.name) AS names"),
                            'users.dni AS dni',
                            'users.birthdate AS birthdate',
                            'users.email AS email',
                            'r.name AS role',
                            'users.profile_photo_path AS profile_photo_path',
                            'users.access AS access',
                            'users.status AS status',
                            'users.created_at AS created_date'                            
                            )
                        ->get();

        return Datatables::of($users)
                        ->addIndexColumn()
                        ->addColumn('action', function ($user) {
                            $buttons = '';
                            $roleName = $user->roles->first() ? $user->roles->first()->name : 'Ninguno';
                    
                            if ($user->status != '0') {
                                // Ver usuario
                                if (auth()->user()->can('users.show')) {
                                    $buttons .= '<a href="' . route('users.show', $user) . '" class="btn btn-info btn-sm mr-1 mb-1" title="Ver usuario"><i class="fas fa-eye"></i> Ver</a> ';
                                }
                                // Editar usuario
                                if (auth()->user()->can('users.edit')) {
                                    $buttons .= '<a href="' . route('users.edit', $user->id) . '" class="btn btn-warning btn-sm mr-1 mb-1" title="Editar usuario"><i class="fas fa-user-edit"></i> Editar</a> ';
                                }
                                // Inactivar usuario
                                // if (auth()->user()->can('users.xxx')) {
                                //     $buttons .= '<a href="" data-target="#modal-delete" data-toggle="modal" data-delete="' . $user->id . '"><button class="btn btn-danger btn-sm mr-1 mb-1" title="Desactivar usuario"><i class="fas fa-trash-alt"></i> Desactivar</button></a> ';
                                // }
                                // Cambiar estado del usuario
                                if (auth()->user()->can('users.change_status')) {                                    
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $user->id . '" data-status="inactivar">
                                                    <button class="btn btn-sm mr-1 mb-1 btn-danger" title="Inactivar usuario">
                                                        <i class="fas fa-user-times"></i> Inactivar
                                                    </button>
                                                </a>';
                                }
                                // Asignar rol
                                if (auth()->user()->can('users.assign_role')) {
                                    $buttons .= '<a href="" data-target="#modal-assign-role" data-toggle="modal" data-id="' . $user->id . '" data-name="' . $user->full_name . '" data-current-role="' . $roleName . '">
                                                    <button class="btn btn-primary btn-sm mr-1 mb-1" title="Asignar rol">
                                                        <i class="fas fa-user-shield"></i> Asignar rol
                                                    </button>
                                                </a>';
                                }
                                // Acceso al sistema
                                if (auth()->user()->can('users.access')) {
                                    // Si tiene acceso (access = 1), botón rojo para quitar acceso
                                    // Si no tiene acceso (access = 0), botón verde para dar acceso
                                    $buttons .= '<a href="" data-target="#modal-access-system" data-toggle="modal" data-id="' . $user->id . '" data-access-system="' . ($user->access == 1 ? 'quitar' : 'otorgar') . '">
                                                    <button class="btn btn-sm mr-1 mb-1 ' . ($user->access == 1 ? 'btn-danger' : 'btn-success') . '" title="' . ($user->access == 1 ? 'Quitar' : 'Otorgar') . ' acceso">
                                                        <i class="' . ($user->access == 1 ? 'fas fa-user-lock' : 'fas fa-key') . '"></i> ' . ($user->access == 1 ? 'Quitar acceso' : 'Otorgar acceso') . '
                                                    </button>
                                                </a>';
                                }
                            } else {
                                // Activar usuario
                                if (auth()->user()->can('users.change_status')) {
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $user->id . '" data-status="activar">
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

    public function indexAdmin()
    {
        $roles = Role::get();
        return view('users.index-admin', compact('roles'));
    }

    public function indexTableAdmin(){
        $users = User::join('role_user as ru', 'users.id', 'ru.user_id')
                        ->join('roles as r', 'ru.role_id', 'r.id')
                        ->select(
                            'users.id AS id',
                            'users.username AS username',
                            'r.name AS role',
                            'users.profile_photo_path AS profile_photo_path',
                            'users.access AS access',
                            'users.status AS status',
                            'users.created_at AS created_date'                            
                            )
                        ->where('r.id', 1)
                        ->get();

        return Datatables::of($users)
                        ->addIndexColumn()
                        ->addColumn('action', function ($user) {
                            $buttons = [];
                            $roleName = $user->roles->first() ? $user->roles->first()->name : 'Ninguno';
                    
                            if ($user->status != '0') {
                                // Ver usuario
                                if (auth()->user()->can('users.show')) {
                                    $buttons[] = '<button class="btn btn-info btn-sm mr-1 mb-1 ver-usuario" 
                                                    data-id="' . $user->id . '">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>';
                                }
                                // Editar usuario
                                if (auth()->user()->can('users.edit')) {
                                    $buttons[] = '<a href="' . route('users.edit', $user->id) . '?type=admin" class="btn btn-warning btn-sm mr-1 mb-1" title="Editar usuario">
                                                    <i class="fas fa-user-edit"></i> Editar
                                                </a>';
                                }
                                // Cambiar estado - Inactivar registro
                                if (auth()->user()->can('users.change_status')) {                                    
                                    $buttons[] = '<a href="#" class="btn btn-danger btn-sm mr-1 mb-1" data-target="#modal-change-status" data-toggle="modal" 
                                                    data-id="' . $user->id . '" 
                                                    data-name="' . $user->username . '"
                                                    data-status="inactivar">
                                                        <i class="fas fa-times-circle"></i> Inactivar
                                                </a>';
                                }
                                // Asignar rol
                                if (auth()->user()->can('users.assign_role')) {
                                    $buttons[] = '<a href="#" class="btn btn-primary btn-sm mr-1 mb-1" data-target="#modal-assign-role" data-toggle="modal" 
                                                    data-id="' . $user->id . '" 
                                                    data-name="' . $user->username . '" 
                                                    data-current-role="' . $roleName . '">
                                                        <i class="fas fa-user-shield"></i> Asignar rol
                                                </a>';
                                }
                                // Acceso al sistema
                                if (auth()->user()->can('users.access')) {
                                    // Si tiene acceso (access = 1), botón rojo para quitar acceso
                                    // Si no tiene acceso (access = 0), botón verde para dar acceso
                                    $buttons[] = '<a href="#" class="btn ' . ($user->access == 1 ? 'btn-danger' : 'btn-success') . ' btn-sm mr-1 mb-1" data-target="#modal-access-system" data-toggle="modal"
                                                    data-id="' . $user->id . '" 
                                                    data-access-system="' . ($user->access == 1 ? 'quitar' : 'otorgar') . '">
                                                        <i class="' . ($user->access == 1 ? 'fas fa-user-lock' : 'fas fa-key') . '"></i> ' . ($user->access == 1 ? 'Quitar acceso' : 'Otorgar acceso') . '
                                                </a>';
                                }
                            } else {
                                // Activar usuario
                                if (auth()->user()->can('users.change_status')) {                                    
                                    $buttons[] = '<a href="#" class="btn btn-success btn-sm mr-1 mb-1" data-target="#modal-change-status" data-toggle="modal" 
                                                    data-id="' . $user->id . '" 
                                                    data-name="' . $user->username . '"
                                                    data-status="activar">
                                                        <i class="fas fa-check-circle"></i> Activar
                                                </a>';
                                }
                            }
                    
                            // Retornar botones o mensaje de "SIN PERMISOS"
                            return !empty($buttons) ? implode('', $buttons) : '<span class="badge badge-secondary">SIN PERMISOS</span>';
                        })
                        ->rawColumns(['action'])
                        ->make(true);
    }

    public function indexCustomer()
    {
        $roles = Role::get();
        return view('users.index-customer', compact('roles'));
    }

    public function indexTableCustomer(){
        $users = User::join('role_user as ru', 'users.id', 'ru.user_id')
                        ->join('roles as r', 'ru.role_id', 'r.id')
                        ->select(
                            'users.id AS id',
                            'users.username AS username',
                            'r.name AS role',
                            'users.profile_photo_path AS profile_photo_path',
                            'users.access AS access',
                            'users.status AS status',
                            'users.created_at AS created_date'                            
                            )
                        ->where('r.id', 2)
                        ->get();

        return Datatables::of($users)
                        ->addIndexColumn()
                        ->addColumn('action', function ($user) {
                            $buttons = [];
                            $roleName = $user->roles->first() ? $user->roles->first()->name : 'Ninguno';
                    
                            if ($user->status != '0') {
                                // Ver usuario
                                if (auth()->user()->can('users.show')) {
                                    $buttons[] = '<button class="btn btn-info btn-sm mr-1 mb-1 ver-usuario" 
                                                    data-id="' . $user->id . '">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>';
                                }
                                // Editar usuario
                                if (auth()->user()->can('users.edit')) {
                                    $buttons[] = '<a href="' . route('users.edit', $user->id) . '?type=customer" class="btn btn-warning btn-sm mr-1 mb-1" title="Editar usuario">
                                                    <i class="fas fa-user-edit"></i> Editar
                                                </a>';
                                }
                                // Cambiar estado - Inactivar registro
                                if (auth()->user()->can('users.change_status')) {                                    
                                    $buttons[] = '<a href="#" class="btn btn-danger btn-sm mr-1 mb-1" data-target="#modal-change-status" data-toggle="modal" 
                                                    data-id="' . $user->id . '" 
                                                    data-name="' . $user->username . '"
                                                    data-status="inactivar">
                                                        <i class="fas fa-times-circle"></i> Inactivar
                                                </a>';
                                }
                                // Asignar rol
                                if (auth()->user()->can('users.assign_role')) {
                                    $buttons[] = '<a href="#" class="btn btn-primary btn-sm mr-1 mb-1" data-target="#modal-assign-role" data-toggle="modal" 
                                                    data-id="' . $user->id . '" 
                                                    data-name="' . $user->username . '" 
                                                    data-current-role="' . $roleName . '">
                                                        <i class="fas fa-user-shield"></i> Asignar rol
                                                </a>';
                                }
                                // Acceso al sistema
                                if (auth()->user()->can('users.access')) {
                                    // Si tiene acceso (access = 1), botón rojo para quitar acceso
                                    // Si no tiene acceso (access = 0), botón verde para dar acceso
                                    $buttons[] = '<a href="#" class="btn ' . ($user->access == 1 ? 'btn-danger' : 'btn-success') . ' btn-sm mr-1 mb-1" data-target="#modal-access-system" data-toggle="modal"
                                                    data-id="' . $user->id . '" 
                                                    data-access-system="' . ($user->access == 1 ? 'quitar' : 'otorgar') . '">
                                                        <i class="' . ($user->access == 1 ? 'fas fa-user-lock' : 'fas fa-key') . '"></i> ' . ($user->access == 1 ? 'Quitar acceso' : 'Otorgar acceso') . '
                                                </a>';
                                }
                            } else {
                                // Activar usuario
                                if (auth()->user()->can('users.change_status')) {                                    
                                    $buttons[] = '<a href="#" class="btn btn-success btn-sm mr-1 mb-1" data-target="#modal-change-status" data-toggle="modal" 
                                                    data-id="' . $user->id . '" 
                                                    data-name="' . $user->username . '"
                                                    data-status="activar">
                                                        <i class="fas fa-check-circle"></i> Activar
                                                </a>';
                                }
                            }
                    
                            // Retornar botones o mensaje de "SIN PERMISOS"
                            return !empty($buttons) ? implode('', $buttons) : '<span class="badge badge-secondary">SIN PERMISOS</span>';
                        })
                        ->rawColumns(['action'])
                        ->make(true);
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        // Mensajes de error personalizados
        $messages = [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'El correo ingresado ya está registrado.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.max' => 'El correo no puede superar los 255 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La imagen debe estar en formato jpeg, png, jpg o gif.',
            'photo.max' => 'El tamaño máximo permitido es de 2MB.',
            'type.required' => 'El tipo de usuario es obligatorio.',
            'type.in' => 'El tipo de usuario debe ser "admin" o "customer".',
        ];

        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:rfc|unique:users,username|max:255',
            'password' => 'required|confirmed|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:admin,customer', // Solo se permiten admin y customer
        ], $messages);

        // Si la validación falla, devolver errores
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Definir la variable de almacenamiento de la foto
            $file_photo_name = null;

            // Manejo de imagen si el usuario subió una
            if ($request->hasFile('photo')) {
                $file_photo = $request->file('photo');
                $file_photo_name = Str::uuid() . '.' . $file_photo->getClientOriginalExtension();
                
                // Guardar en storage/app/public/users/photo
                $file_photo->storeAs('public/users/photo/', $file_photo_name);
            }

            // Determinar el rol según el tipo de usuario
            $role_id = ($request->type === 'admin') ? 1 : 2; // 1 = Admin, 2 = Customer

            // Crear el usuario en la base de datos
            $user = User::create([
                'username' => $request->email,                
                'profile_photo_path' => $file_photo_name,
                'password' => bcrypt($request->password)
            ]);

            // Asignar el rol en función del `type`
            $role_id = $request->type === 'admin' ? 1 : 2;
            $role = Role::find($role_id);

            if ($role) {
                $user->assignRole($role->name);

                // Sincronizar los permisos del rol al usuario
                $permissions = $role->permissions->pluck('name');
                $user->syncPermissions($permissions);
            }

            // Definir la redirección según el tipo de usuario
            $redirect_url = match ($request->type) {
                'admin' => route('users.index-admin'),
                'customer' => route('users.index-customer'),
                default => route('users.index')
            };
            
            return response()->json([
                'message' => 'Usuario ' . $user->username . ' creado con éxito.',
                'redirect_url' => $redirect_url
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear el usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al crear el usuario.'.$e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $user = User::leftjoin('role_user as ru', 'users.id', 'ru.user_id')
                    ->leftjoin('roles as r', 'ru.role_id', 'r.id')
                    ->select(
                        'users.id AS id',
                        'users.username AS username',
                        'users.profile_photo_path AS profile_photo_path',
                        'r.name AS role',
                        'users.access AS access',
                        'users.status AS status',
                        'users.created_at AS created_date'                            
                        )
                    ->where('users.id', $id)
                    ->first();

        return response()->json($user);
    }

    public function edit($id)
    {
        $user = User::join('role_user AS ru', 'users.id', '=', 'ru.user_id')
                    ->select(
                        'users.id AS id',
                        'users.username AS email',
                        'users.profile_photo_path AS profile_photo_path',
                        'users.access AS access',
                        'users.status AS status',
                        'users.created_at AS created_date'                            
                    )
                    ->where('users.id', $id)
                    ->first(); // <- Usamos `first()` en lugar de `get()`
        
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        // Mensajes de error personalizados
        $messages = [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'El correo ingresado ya está registrado.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.max' => 'El correo no puede superar los 255 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La imagen debe estar en formato jpeg, png, jpg o gif.',
            'photo.max' => 'El tamaño máximo permitido es de 2MB.',
            'type.required' => 'El tipo de usuario es obligatorio.',
            'type.in' => 'El tipo de usuario debe ser "admin" o "customer".',
        ];

        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:rfc|unique:users,username,' . $id . '|max:255', // Eliminar la validación de unique si es el mismo ID
            'password' => 'nullable|confirmed|min:6', // La contraseña es opcional
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:admin,customer', // Solo se permiten admin y customer
        ], $messages);

        // Si la validación falla, devolver errores
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtener el usuario existente
            $user = User::findOrFail($id);

            // Definir la variable de almacenamiento de la foto
            $file_photo_name = $user->profile_photo_path; // Mantener la foto actual si no se sube una nueva

            // Manejo de imagen si el usuario subió una nueva foto
            if ($request->hasFile('photo')) {
                $file_photo = $request->file('photo');
                $file_photo_name = Str::uuid() . '.' . $file_photo->getClientOriginalExtension();
                
                // Eliminar la foto anterior si existe
                if ($user->profile_photo_path) {
                    \Storage::delete('public/users/photo/' . $user->profile_photo_path);
                }
                
                // Guardar la nueva foto en storage/app/public/users/photo
                $file_photo->storeAs('public/users/photo/', $file_photo_name);
            }

            // Actualizar los datos del usuario
            $user->update([
                'username' => $request->email, // Mantener el correo
                'profile_photo_path' => $file_photo_name, // Actualizar la foto
            ]);

            // Si el password fue enviado, actualizarlo
            if ($request->password) {
                $user->password = bcrypt($request->password);
                $user->save();
            }

            // Actualizar el rol del usuario (si es necesario)
            $role_id = ($request->type === 'admin') ? 1 : 2; // 1 = Admin, 2 = Customer
            $role = Role::find($role_id);

            if ($role) {
                $user->syncRoles([$role->name]); // Sincronizar el rol
                $permissions = $role->permissions->pluck('name');
                $user->syncPermissions($permissions); // Sincronizar permisos
            }

            // Redirigir según el tipo de usuario
            $redirect_url = match ($request->type) {
                'admin' => route('users.index-admin'),
                'customer' => route('users.index-customer'),
                default => route('users.index')
            };

            return response()->json([
                'message' => 'Usuario ' . $user->username . ' actualizado con éxito.',
                'redirect_url' => $redirect_url
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar el usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el usuario. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        //
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status_action' => 'required|in:activar,inactivar'
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'error' => 'Usuario no encontrado.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Determinar el nuevo estado
            $newStatus = $request->status_action === 'activar' ? 1 : 0;

            // Actualizar el estado del usuario y el acceso al sistema si se desactiva
            $user->status = $newStatus;
            if ($newStatus == 0) {
                $user->access = 0;
            }
            $user->save();

            DB::commit();

            return response()->json([
                'success' => 'Estado del usuario cambiado exitosamente a <strong>' . ($newStatus ? 'Activo' : 'Inactivo') . '</strong>',
                'status' => $newStatus
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al cambiar el estado del usuario: ' . $th->getMessage()
            ], 500);
        }
    }

    public function accessSystem(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'access_action' => 'required|in:otorgar,quitar'
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$user) {
            return response()->json([
                'error' => 'Usuario no encontrado.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Determinar el nuevo acceso al sistema
            $newAccess = $request->access_action === 'otorgar' ? 1 : 0;

            // Actualizar el acceso al sistema
            $user->access = $newAccess; // Aquí actualizamos el acceso, no el estado
            $user->save();

            DB::commit();

            return response()->json([
                'success' => 'Acceso del usuario cambiado exitosamente a <strong>' . ($newAccess ? 'Otorgado' : 'Quitado') . '</strong>',
                'status' => $newAccess
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al cambiar el estado del usuario: ' . $th->getMessage()
            ], 500);
        }
    }
}