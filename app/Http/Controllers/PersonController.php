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
use App\Models\TypePersonTransition;
use App\Models\PersonTypeHistory;

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
            ->leftJoin('type_documents as td', 'people.type_document_id', 'td.id')
            ->leftJoin('type_people as tp', 'people.type_person_id', 'tp.id')
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
                'people.lead_source AS lead_source',
                'people.commercial_notes AS commercial_notes',
                'people.marketing_consent AS marketing_consent',
                'people.lead_source AS lead_source',
                'people.marketing_consent AS marketing_consent',
                'u.profile_photo_path AS profile_photo_path',
                'tp.name AS type_person',
                'people.token AS token',
                'people.status AS status',
                'people.created_at AS created_date'
            )
            ->when(!auth()->user()->is_system_owner, fn($query) => $query
                ->where(fn($visible) => $visible
                    ->whereNull('u.id')
                    ->orWhere('u.is_system_owner', false)));

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
                'people.phone_number AS phone_number',
                'tp.name AS type_person',
                'people.token AS token',
                'people.status AS status',
                'people.created_at AS created_date'
            )
            ->when(!auth()->user()->is_system_owner, fn($query) => $query
                ->where(fn($visible) => $visible
                    ->whereNull('u.id')
                    ->orWhere('u.is_system_owner', false)))
            ->where('tp.code', 'CO')
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
                'people.phone_number AS phone_number',
                'tp.name AS type_person',
                'people.token AS token',
                'people.status AS status',
                'people.created_at AS created_date'
            )
            ->when(!auth()->user()->is_system_owner, fn($query) => $query
                ->where(fn($visible) => $visible
                    ->whereNull('u.id')
                    ->orWhere('u.is_system_owner', false)))
            ->where('tp.code', 'CP')
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
                        $buttons .= '<button type="button" class="btn btn-primary btn-sm mr-1 mb-1 convert-prospect"
                                                    data-target="#modal-convert-prospect" data-toggle="modal"
                                                    data-id="' . $person->id . '" data-name="' . e($person->full_name) . '"
                                                    data-email="' . e($person->email ?? '') . '" data-phone="' . e($person->phone_number ?? '') . '">
                                                    <i class="fas fa-user-check"></i> Convertir a contacto
                                                </button>';
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
        $request->merge([
            'type' => strtolower((string) TypePerson::query()->whereKey($request->type_person)->value('code')),
        ]);

        $validator = Validator::make($request->all(), [
            'type_document' => ['nullable', Rule::requiredIf($request->type === 'co'), 'exists:type_documents,id'],
            'document_number' => ['nullable', Rule::requiredIf($request->type === 'co'), 'unique:people,document_number', 'string', 'max:50'],
            'full_name' => 'required|unique:people|string|max:255',
            'type_person' => 'required|exists:type_people,id',
            'type' => 'required|in:co,cp', // Solo se permiten cp y co
            //'email' => 'required_if:type,co|email:rfc|unique:people,email', // Obligatorio solo si type es "co"
            'email' => [
                'nullable', // Permitir que no se envíe
                Rule::requiredIf($request->type === 'co' || blank($request->phone_number)),
                'email:rfc', // Validar formato solo si se envía
                Rule::unique('people', 'email')->ignore($request->email) // Evitar la validación de unicidad si está vacío
            ],
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'phone_number' => ['nullable', Rule::requiredIf($request->type === 'cp' && blank($request->email)), 'regex:/^[0-9]{9}$/'],
            'token' => 'nullable|string|max:500',
            'lead_source' => 'nullable|string|max:100',
            'commercial_notes' => 'nullable|string|max:3000',
            'marketing_consent' => 'sometimes|boolean',
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
                'email' => $request->email,
                'birthdate' => $request->type === 'co' ? $request->birthdate : null,
                'address' => $request->type === 'co' ? $request->address : null,
                'phone_number' => $request->phone_number,
                'token' => $request->type === 'co' ? $request->token : null,
                'lead_source' => $request->lead_source,
                'commercial_notes' => $request->commercial_notes,
                'marketing_consent' => $request->boolean('marketing_consent'),
                'marketing_consent_at' => $request->boolean('marketing_consent') ? now() : null,
            ]);

            // Respuesta con mensaje de éxito
            return response()->json([
                'success' => 'Cliente <strong>' . e($person->full_name) . '</strong> agregado exitosamente.'
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
            $html = '';

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
                ->when(!auth()->user()->is_system_owner, fn($query) => $query
                    ->where(fn($visible) => $visible
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
            $html = '';

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
                    'people.lead_source AS lead_source',
                    'people.commercial_notes AS commercial_notes',
                    'people.marketing_consent AS marketing_consent',
                    'tp.name AS type_person',
                    'tp.id AS type_person_id',
                    'tp.code AS type_person_code',
                    'people.token AS token',
                    'people.status AS status',
                    'people.created_at AS created_date'
                )
                ->when(!auth()->user()->is_system_owner, fn($query) => $query
                    ->where(fn($visible) => $visible
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

        if ((int) $person->type_person_id !== (int) $request->type_person) {
            return response()->json([
                'errors' => ['type_person' => ['El tipo no se cambia desde la edición. Utiliza el flujo de conversión configurado.']],
            ], 422);
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
        $request->merge([
            'type' => strtolower((string) TypePerson::query()->whereKey($request->type_person)->value('code')),
        ]);

        $validator = Validator::make($request->all(), [
            'type_document' => ['nullable', Rule::requiredIf($request->type === 'co'), 'exists:type_documents,id'],
            'document_number' => [
                Rule::requiredIf($request->type === 'co'),
                'nullable',
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
            'type_person' => 'required|exists:type_people,id',
            'type' => 'required|in:co,cp',
            'email' => [
                'nullable',
                Rule::requiredIf($request->type === 'co' || blank($request->phone_number)),
                'email:rfc',
                Rule::unique('people', 'email')->ignore($person->id)
            ],
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'phone_number' => ['nullable', Rule::requiredIf($request->type === 'cp' && blank($request->email)), 'regex:/^[0-9]{9}$/'],
            'token' => 'nullable|string|max:255',
            'lead_source' => 'nullable|string|max:100',
            'commercial_notes' => 'nullable|string|max:3000',
            'marketing_consent' => 'sometimes|boolean',
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
                'email' => $request->email,
                'birthdate' => $request->type === 'co' ? $request->birthdate : null,
                'address' => $request->type === 'co' ? $request->address : null,
                'phone_number' => $request->phone_number,
                'token' => $request->type === 'co' ? $request->token : null,
                'lead_source' => $request->lead_source,
                'commercial_notes' => $request->commercial_notes,
                'marketing_consent' => $request->boolean('marketing_consent'),
                'marketing_consent_at' => $request->boolean('marketing_consent')
                    ? ($person->marketing_consent_at ?: now()) : null,
                'marketing_opt_out_at' => $request->boolean('marketing_consent') ? null : ($person->marketing_consent ? now() : $person->marketing_opt_out_at),
            ]);

            return response()->json([
                'success' => 'Cliente <strong>' . e($person->full_name) . '</strong> actualizado exitosamente.'
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

    public function convert(Request $request, Person $person)
    {
        abort_unless(auth()->user()->can('people.edit'), 403);
        $this->abortIfSystemOwnerPerson($person);

        $target = TypePerson::whereRaw('LOWER(code) = ?', ['co'])->firstOrFail();
        $transition = TypePersonTransition::where('from_type_person_id', $person->type_person_id)
            ->where('to_type_person_id', $target->id)->where('active', true)->first();
        abort_unless($transition, 422, 'No existe un flujo activo para esta conversión.');

        $validated = $request->validate([
            'type_document_id' => ['required', 'exists:type_documents,id'],
            'document_number' => ['required', 'string', 'max:50', Rule::unique('people', 'document_number')->ignore($person->id)],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('people', 'email')->ignore($person->id)],
            'phone_number' => ['nullable', 'regex:/^[0-9]{9}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'marketing_consent' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($person, $target, $validated, $request) {
            $fromType = $person->type_person_id;
            $consent = $request->boolean('marketing_consent');
            $person->update([
                'type_person_id' => $target->id,
                'type_document_id' => $validated['type_document_id'],
                'document_number' => $validated['document_number'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'birthdate' => $validated['birthdate'] ?? null,
                'commercial_notes' => $validated['notes'] ?? $person->commercial_notes,
                'marketing_consent' => $consent,
                'marketing_consent_at' => $consent ? ($person->marketing_consent_at ?: now()) : null,
                'marketing_opt_out_at' => $consent ? null : ($person->marketing_consent ? now() : $person->marketing_opt_out_at),
                'converted_at' => now(),
            ]);
            PersonTypeHistory::create([
                'person_id' => $person->id,
                'from_type_person_id' => $fromType,
                'to_type_person_id' => $target->id,
                'changed_by' => auth()->id(),
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return response()->json(['success' => 'El prospecto fue convertido en contacto y el cambio quedó registrado.']);
    }

    public function history(Person $person)
    {
        abort_unless(auth()->user()->can('people.show'), 403);
        $this->abortIfSystemOwnerPerson($person);

        return response()->json($person->typeHistory()->with(['fromType:id,name', 'toType:id,name', 'changedBy:id,username'])
            ->get()->map(fn($item) => [
                'from' => $item->fromType?->name,
                'to' => $item->toType?->name,
                'reason' => $item->reason,
                'notes' => $item->notes,
                'changed_by' => $item->changedBy?->username,
                'date' => $item->created_at?->format('d/m/Y H:i:s'),
            ]));
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
