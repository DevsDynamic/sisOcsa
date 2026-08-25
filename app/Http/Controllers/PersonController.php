<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\TypeDocument;
use App\Models\TypePerson;
use Illuminate\Http\Request;
//use DataTables;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;

class PersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('people.index');
    }

    public function indexTable()
    {
        $people = Person::leftJoin('users as u', 'people.user_id', 'u.id')
                            ->join('type_documents as td', 'people.type_document_id', 'td.id')
                            ->join('type_people as tp', 'people.type_person_id', 'tp.id')        
                            ->select(
                                    'people.id AS id',
                                    'u.username AS username',
                                    'td.name AS type_document',
                                    'people.document_number AS document_number',
                                    'people.full_name AS full_name',
                                    'people.birthdate AS birthdate',
                                    'people.address AS address',
                                    'people.email AS email',
                                    'u.profile_photo_path AS profile_photo_path',
                                    'tp.name AS type_person',
                                    'people.token AS token',
                                    'people.status AS status',
                                    'people.created_at AS created_date'                            
                                    )
                            ->when(!auth()->user()->is_system_owner, fn ($query) => $query
                                ->where(fn ($visible) => $visible
                                    ->whereNull('u.id')
                                    ->orWhere('u.is_system_owner', false)))
                            ;

        return Datatables::eloquent($people)
                        ->addIndexColumn()
                        ->addColumn('action', function ($person) {
                            $buttons = '';

                            if ($person->status != '0') {
                                // Ver cliente
                                if (auth()->user()->can('people.show')) {
                                    $buttons .= '<a href="" data-target="#modal-show" data-toggle="modal" data-id="' . $person->id . '">
                                                    <button class="btn btn-info btn-sm mr-1 mb-1" title="Ver cliente">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </button>
                                                </a>';
                                }
                                // editar cliente
                                if (auth()->user()->can('people.edit')) {
                                    $buttons .= '<a href="" data-target="#modal-edit" data-toggle="modal" data-id="' . $person->id . '">
                                                    <button class="btn btn-warning btn-sm mr-1 mb-1" title="Editar cliente">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                </a>';
                                }
                                // Cambiar estado del cliente (activar/inactivar)
                                if (auth()->user()->can('people.change_status')) {                                    
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $person->id . '" data-status="inactivar">
                                                    <button class="btn btn-sm mr-1 mb-1 btn-danger" title="Inactivar cliente">
                                                        <i class="fas fa-times-circle"></i> Inactivar
                                                    </button>
                                                </a>';
                                }
                            } else {
                                // Activar cliente (activar/inactivar)
                                if (auth()->user()->can('people.change_status')) {
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $person->id . '" data-status="activar">
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

    public function indexCO()
    {
        return view('people.index-co');
    }

    public function indexTableCO()
    {
        $people = Person::leftJoin('users as u', 'people.user_id', 'u.id')
                            ->leftjoin('type_documents as td', 'people.type_document_id', 'td.id')
                            ->leftjoin('type_people as tp', 'people.type_person_id', 'tp.id')        
                            ->select(
                                    'people.id AS id',
                                    'u.username AS username',
                                    'td.name AS type_document',
                                    'people.document_number AS document_number',
                                    'people.full_name AS full_name',
                                    'people.birthdate AS birthdate',
                                    'people.address AS address',
                                    'people.email AS email',
                                    'tp.name AS type_person',
                                    'people.token AS token',
                                    'people.status AS status',
                                    'people.created_at AS created_date'                            
                                    )
                            ->when(!auth()->user()->is_system_owner, fn ($query) => $query
                                ->where(fn ($visible) => $visible
                                    ->whereNull('u.id')
                                    ->orWhere('u.is_system_owner', false)))
                            ->where('tp.code','CO')                           
                            ->get();

        return Datatables::of($people)
                        ->addIndexColumn()
                        ->addColumn('action', function ($person) {
                            $buttons = '';

                            if ($person->status != '0') {
                                // Ver cliente
                                if (auth()->user()->can('people.show')) {
                                    $buttons .= '<a href="" data-target="#modal-show" data-toggle="modal" data-id="' . $person->id . '">
                                                    <button class="btn btn-info btn-sm mr-1 mb-1" title="Ver cliente">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </button>
                                                </a>';
                                }
                                // editar cliente
                                if (auth()->user()->can('people.edit')) {
                                    $buttons .= '<a href="" data-target="#modal-edit" data-toggle="modal" 
                                                    data-id="' . $person->id . '"
                                                    data-type="co">
                                                    <button class="btn btn-warning btn-sm mr-1 mb-1" title="Editar cliente">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                </a>';
                                }
                                // Cambiar estado del cliente (activar/inactivar)
                                if (auth()->user()->can('people.change_status')) {                                    
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $person->id . '" data-status="inactivar">
                                                    <button class="btn btn-sm mr-1 mb-1 btn-danger" title="Inactivar cliente">
                                                        <i class="fas fa-times-circle"></i> Inactivar
                                                    </button>
                                                </a>';
                                }
                            } else {
                                // Activar cliente (activar/inactivar)
                                if (auth()->user()->can('people.change_status')) {
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $person->id . '" data-status="activar">
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

    public function indexCP()
    {
        return view('people.index-cp');
    }

    public function indexTableCP()
    {
        $people = Person::leftJoin('users as u', 'people.user_id', 'u.id')
                            ->leftjoin('type_documents as td', 'people.type_document_id', 'td.id')
                            ->leftjoin('type_people as tp', 'people.type_person_id', 'tp.id')        
                            ->select(
                                    'people.id AS id',
                                    'u.username AS username',
                                    'td.name AS type_document',
                                    'people.document_number AS document_number',
                                    'people.full_name AS full_name',
                                    'people.birthdate AS birthdate',
                                    'people.address AS address',
                                    'people.email AS email',
                                    'tp.name AS type_person',
                                    'people.token AS token',
                                    'people.status AS status',
                                    'people.created_at AS created_date'                            
                                    )
                            ->when(!auth()->user()->is_system_owner, fn ($query) => $query
                                ->where(fn ($visible) => $visible
                                    ->whereNull('u.id')
                                    ->orWhere('u.is_system_owner', false)))
                            ->where('tp.code','CP')                           
                            ->get();

        return Datatables::of($people)
                        ->addIndexColumn()
                        ->addColumn('action', function ($person) {
                            $buttons = '';

                            if ($person->status != '0') {
                                // Ver cliente
                                if (auth()->user()->can('people.show')) {
                                    $buttons .= '<a href="" data-target="#modal-show" data-toggle="modal" data-id="' . $person->id . '">
                                                    <button class="btn btn-info btn-sm mr-1 mb-1" title="Ver cliente">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </button>
                                                </a>';
                                }
                                // editar cliente
                                if (auth()->user()->can('people.edit')) {
                                    $buttons .= '<a href="" data-target="#modal-edit" data-toggle="modal" 
                                                    data-id="' . $person->id . '"
                                                    data-type="cp">
                                                    <button class="btn btn-warning btn-sm mr-1 mb-1" title="Editar cliente">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                </a>';
                                }
                                // Cambiar estado del cliente (activar/inactivar)
                                if (auth()->user()->can('people.change_status')) {                                    
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $person->id . '" data-status="inactivar">
                                                    <button class="btn btn-sm mr-1 mb-1 btn-danger" title="Inactivar cliente">
                                                        <i class="fas fa-times-circle"></i> Inactivar
                                                    </button>
                                                </a>';
                                }
                            } else {
                                // Activar cliente (activar/inactivar)
                                if (auth()->user()->can('people.change_status')) {
                                    $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $person->id . '" data-status="activar">
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
        //
    }

    public function store(Request $request)
    {
        // Mensajes de error personalizados
        $messages = [
            'type_document.required' => 'El tipo de documento es obligatorio.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.unique' => 'El número de documento ingresado ya existe.',
            'document_number.string' => 'El número de documento debe ser una cadena de texto.',
            'document_number.max' => 'El número de documento no puede tener más de 50 caracteres.',
            'full_name.required' => 'El nombre completo es obligatorio.',
            'full_name.unique' => 'El nombre ingresado ya existe.',
            'full_name.string' => 'El campo nombre debe ser una cadena de texto.',
            'full_name.max' => 'El campo nombre no puede tener más de 255 caracteres.',
            'type_person.required' => 'El tipo de cliente es obligatorio.',
            'type.required' => 'El tipo de usuario es obligatorio.',
            'type.in' => 'El tipo de usuario debe ser "CP" o "CO".',
            'email.required_if' => 'El correo electrónico es obligatorio cuando el tipo de usuario es "CO".',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'phone_number.regex' => 'El número de teléfono debe contener exactamente 9 dígitos numéricos.',
            'birthdate.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'address.string' => 'La dirección debe ser una cadena de texto.',
            'address.max' => 'La dirección no puede tener más de 255 caracteres.',
            'token.string' => 'El token debe ser una cadena de texto.',
            'token.max' => 'El token no puede tener más de 500 caracteres.',
        ];

        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'type_document' => 'required',
            'document_number'=> 'required|unique:people|string|max:50',
            'full_name' => 'required|unique:people|string|max:255',
            'type_person'=> 'required',
            'type' => 'required|in:co,cp', // Solo se permiten cp y co
            //'email' => 'required_if:type,co|email:rfc|unique:people,email', // Obligatorio solo si type es "co"
            'email' => [
                'nullable', // Permitir que no se envíe
                'required_if:type,co', // Solo obligatorio si el tipo es "co"
                'email:rfc', // Validar formato solo si se envía
                Rule::unique('people', 'email')->ignore($request->email) // Evitar la validación de unicidad si está vacío
            ],
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|regex:/^[0-9]{9}$/',
            'token' => 'nullable|string|max:500',
        ], $messages);

        // Si la validación falla, devolver errores
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Código para guardar el tipo de cliente
            $person = Person::create([
                'type_document_id' => $request->type_document,
                'document_number' => $request->document_number,
                'full_name' => $request->full_name,
                'type_person_id' => $request->type_person,
                'email' => $request->type === 'co' ? $request->email : null,
                'birthdate' => $request->type === 'co' ? $request->birthdate : null,
                'address' => $request->type === 'co' ? $request->address : null,
                'phone_number' => $request->type === 'co' ? $request->phone_number : null,
                'token' => $request->type === 'co' ? $request->token : null,
            ]);
    
            // Respuesta con mensaje de éxito
            return response()->json([
                'message' => 'Cliente <strong>' . $person->full_name . '</strong> agregado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear el cliente. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //VERIFICAR QUE EXISTE ID
        if (!$id) {
            $html = 'NULL';
        } else {
            $html='';

            $person = Person::leftJoin('users as u', 'people.user_id', 'u.id')
                            ->leftjoin('type_documents as td', 'people.type_document_id', 'td.id')
                            ->leftjoin('type_people as tp', 'people.type_person_id', 'tp.id')        
                            ->select(
                                    'people.id AS id',
                                    'u.username AS username',
                                    'td.name AS type_document',
                                    'people.document_number AS document_number',
                                    'people.full_name AS full_name',
                                    'people.birthdate AS birthdate',
                                    'people.address AS address',
                                    'people.email AS email',
                                    'people.phone_number AS phone_number',
                                    'tp.name AS type_person',
                                    'people.token AS token',
                                    'people.status AS status',
                                    'people.created_at AS created_date'                            
                                    )
                            ->when(!auth()->user()->is_system_owner, fn ($query) => $query
                                ->where(fn ($visible) => $visible
                                    ->whereNull('u.id')
                                    ->orWhere('u.is_system_owner', false)))
                            ->where('people.id', $id)                           
                            ->first();
            
            $html = $person;          
        }
        return response()->json(['html' => $html]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {   //VERIFICAR QUE EXISTE ID
        if (!$id) {
            $html = 'NULL';
        } else {
            $html='';

            $person = Person::leftJoin('users as u', 'people.user_id', 'u.id')
                                ->leftjoin('type_documents as td', 'people.type_document_id', 'td.id')
                                ->leftjoin('type_people as tp', 'people.type_person_id', 'tp.id')        
                                ->select(
                                        'people.id AS id',
                                        'u.username AS username',
                                        'td.name AS type_document',
                                        'td.id AS type_document_id',
                                        'people.document_number AS document_number',
                                        'people.full_name AS full_name',
                                        'people.birthdate AS birthdate',
                                        'people.address AS address',
                                        'people.email AS email',
                                        'people.phone_number AS phone_number',
                                        'tp.name AS type_person',
                                        'tp.id AS type_person_id',
                                        'people.token AS token',
                                        'people.status AS status',
                                        'people.created_at AS created_date'                            
                                        )
                                ->when(!auth()->user()->is_system_owner, fn ($query) => $query
                                    ->where(fn ($visible) => $visible
                                        ->whereNull('u.id')
                                        ->orWhere('u.is_system_owner', false)))
                                ->where('people.id', $id)                           
                                ->first();
            
            $html = $person;          
        }
        return response()->json(['html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $person = Person::find($id);
        $this->abortIfSystemOwnerPerson($person);

        if (!$person) {
            return response()->json([
                'error' => 'Cliente no encontrado.'
            ], 404);
        }

        // Mensajes de error personalizados
        $messages = [
            'type_document.required' => 'El tipo de documento es obligatorio.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.unique' => 'El número de documento ingresado ya existe.',
            'document_number.string' => 'El número de documento debe ser una cadena de texto.',
            'document_number.max' => 'El número de documento no puede tener más de 50 caracteres.',
            'full_name.required' => 'El nombre completo es obligatorio.',
            'full_name.unique' => 'El nombre ingresado ya existe.',
            'full_name.string' => 'El campo nombre debe ser una cadena de texto.',
            'full_name.max' => 'El campo nombre no puede tener más de 255 caracteres.',
            'type_person.required' => 'El tipo de cliente es obligatorio.',
            'type.required' => 'El tipo de usuario es obligatorio.',
            'type.in' => 'El tipo de usuario debe ser "CP" o "CO".',
            'email.required_if' => 'El correo electrónico es obligatorio cuando el tipo de usuario es "CO".',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'phone_number.regex' => 'El número de teléfono debe contener exactamente 9 dígitos numéricos.',
            'birthdate.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'address.string' => 'La dirección debe ser una cadena de texto.',
            'address.max' => 'La dirección no puede tener más de 255 caracteres.',
            'token.string' => 'El token debe ser una cadena de texto.',
            'token.max' => 'El token no puede tener más de 500 caracteres.',
        ];

        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'type_document' => 'required',
            'document_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('people', 'document_number')->ignore($person->id)
            ],
            'full_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('people', 'full_name')->ignore($person->id)
            ],
            'type_person' => 'required',
            'type' => 'required|in:co,cp',
            'email' => [
                'nullable',
                'required_if:type,co',
                'email:rfc',
                Rule::unique('people', 'email')->ignore($person->id)
            ],
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|regex:/^[0-9]{9}$/',
            'token' => 'nullable|string|max:255',
        ], $messages);

        // Si la validación falla, devolver errores
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Actualizar los datos
            $person->update([
                'type_document_id' => $request->type_document,
                'document_number' => $request->document_number,
                'full_name' => $request->full_name,
                'type_person_id' => $request->type_person,
                'email' => $request->type === 'co' ? $request->email : null,
                'birthdate' => $request->type === 'co' ? $request->birthdate : null,
                'address' => $request->type === 'co' ? $request->address : null,
                'phone_number' => $request->type === 'co' ? $request->phone_number : null,
                'token' => $request->type === 'co' ? $request->token : null,
            ]);

            return response()->json([
                'message' => 'Cliente <strong>' . $person->full_name . '</strong> actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar el cliente. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'register_id' => 'required|exists:people,id',
            'status_action' => 'required|in:activar,inactivar'
        ]);

        $person = Person::find($request->register_id);
        $this->abortIfSystemOwnerPerson($person);

        if (!$person) {
            return response()->json([
                'error' => 'Cliente no encontrado.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Determinar el nuevo estado
            $newStatus = $request->status_action === 'activar' ? 1 : 0;

            // Actualizar el estado del rol
            $person->status = $newStatus;
            $person->save();

            DB::commit();

            return response()->json([
                'success' => 'Cliente <strong>' . $person->full_name . '</strong> cambiado exitosamente a <strong>' . ($newStatus ? 'Activo' : 'Inactivo') . '</strong>',
                'status' => $newStatus
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al cambiar el estado del cliente <strong>' . $person->full_name . '</strong>: ' . $th->getMessage()
            ], 500);
        }
    }

    private function abortIfSystemOwnerPerson(?Person $person): void
    {
        if (!$person || auth()->user()->is_system_owner || !$person->user_id) {
            return;
        }

        abort_if(
            User::whereKey($person->user_id)->where('is_system_owner', true)->exists(),
            404
        );
    }
}
