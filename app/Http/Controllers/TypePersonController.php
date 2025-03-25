<?php

namespace App\Http\Controllers;

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

class TypePersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('type-people.index');
    }

    public function indexTable(){
        $type_people = TypePerson::select(
                            'type_people.id AS id',
                            'type_people.code AS code',
                            'type_people.name AS name',
                            'type_people.description AS description',
                            'type_people.status AS status',
                            'type_people.created_at AS created_date'                            
                            )
                        ->get();

        return Datatables::of($type_people)
                        ->addIndexColumn()
                        ->addColumn('action', function ($type_person) {
                            $buttons = [];

                            // Escapar variables para evitar problemas con caracteres especiales
                            $id = htmlspecialchars($type_person->id, ENT_QUOTES, 'UTF-8');
                            $name = htmlspecialchars($type_person->name, ENT_QUOTES, 'UTF-8');
                            $code = htmlspecialchars($type_person->code, ENT_QUOTES, 'UTF-8');
                            $description = htmlspecialchars($type_person->description, ENT_QUOTES, 'UTF-8');

                            if ($type_person->status != '0') {
                                // Ver tipo de cliente
                                if (auth()->user()->can('type-people.show')) {
                                    $buttons[] = '<a href="#" class="btn btn-info btn-sm mr-1 mb-1" data-target="#modal-show" data-toggle="modal" 
                                                    data-id="' . $id . '"
                                                    data-name="'. $name .'"
                                                    data-code="'. $code .'"
                                                    data-description="'. $description .'">
                                                        <i class="fas fa-eye"></i> Ver
                                                </a>';
                                }
                                // editar tipo de cliente
                                if (auth()->user()->can('type-people.edit')) {
                                    $buttons[] = '<a href="#" class="btn btn-warning btn-sm mr-1 mb-1" data-target="#modal-edit" data-toggle="modal" 
                                                    data-id="' . $id . '"
                                                    data-name="'. $name .'"
                                                    data-code="'. $code .'"
                                                    data-description="'. $description .'">
                                                        <i class="fas fa-edit"></i> Editar
                                                </a>';
                                }
                                // Cambiar estado del tipo de cliente (activar/inactivar)
                                if (auth()->user()->can('type-people.change_status')) {                                    
                                    $buttons[] = '<a href="#" class="btn btn-danger btn-sm mr-1 mb-1" data-target="#modal-change-status" data-toggle="modal" 
                                                    data-id="' . $id . '"
                                                    data-status="inactivar">
                                                        <i class="fas fa-times-circle"></i> Inactivar
                                                </a>';
                                }
                            } else {
                                // Activar tipo de cliente (activar/inactivar)
                                if (auth()->user()->can('type-people.change_status')) {
                                    $buttons[] = '<a href="#" class="btn btn-success btn-sm mr-1 mb-1" data-target="#modal-change-status" data-toggle="modal" 
                                                    data-id="' . $id . '"
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
        //
    }

    public function store(Request $request)
    {
        // Mensajes de error personalizados
        $messages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El nombre ingresado ya existe.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no puede tener más de 255 caracteres.',
            'code.required' => 'El campo nombre es obligatorio.',
            'code.unique' => 'El nombre ingresado ya existe.',
            'code.string' => 'El campo nombre debe ser una cadena de texto.',
            'code.max' => 'El campo nombre no puede tener más de 255 caracteres.',
            'description.string' => 'El campo descripción debe ser una cadena de texto.',
            'description.max' => 'El campo descripción no puede tener más de 255 caracteres.',
        ];

        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:type_people|string|max:255',
            'code' => 'required|unique:type_people|string|max:255',
            'description' => 'nullable|string|max:255',
        ], $messages);

        // Si la validación falla, devolver errores
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }        

        try {
            // Código para guardar el tipo de cliente
            $type_person = TypePerson::create([
                    'name' => $request->name,
                    'code' => $request->code,
                    'description' => $request->description
                ]);
    
            // Respuesta con mensaje de éxito
            return response()->json([
                'message' => 'Tipo de cliente <strong>' . $type_person->name . '</strong> agregado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear el tipo de cliente. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        // Mensajes de error personalizados
        $messages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El nombre ingresado ya existe.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no puede tener más de 255 caracteres.',
            'code.required' => 'El campo código es obligatorio.',
            'code.unique' => 'El código ingresado ya existe.',
            'code.string' => 'El campo código debe ser una cadena de texto.',
            'code.max' => 'El campo código no puede tener más de 255 caracteres.',
            'description.string' => 'El campo descripción debe ser una cadena de texto.',
            'description.max' => 'El campo descripción no puede tener más de 255 caracteres.',
        ];

        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:type_people,name,' . $id,
            'code' => 'required|string|max:255|unique:type_people,code,' . $id,
            'description' => 'nullable|string|max:255',
        ], $messages);

        // Si la validación falla, devolver errores
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Encontrar el tipo de cliente por su ID
            $type_person = TypePerson::findOrFail($id);

            // Actualizar los datos del tipo de cliente
            $type_person->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description ?? $type_person->description, // Mantener valor actual si no se proporciona descripción
            ]);

            // Respuesta con mensaje de éxito
            return response()->json([
                'message' => 'Tipo de cliente <strong>' . $type_person->name . '</strong> actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar el tipo de cliente. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        //
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'type_customer_id' => 'required|exists:type_people,id',
            'status_action' => 'required|in:activar,inactivar'
        ]);

        $type_person = TypePerson::find($request->type_customer_id);

        if (!$type_person) {
            return response()->json([
                'error' => 'Tipo de cliente no encontrado.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Determinar el nuevo estado
            $newStatus = $request->status_action === 'activar' ? 1 : 0;

            // Actualizar el estado del rol
            $type_person->status = $newStatus;
            $type_person->save();

            DB::commit();

            return response()->json([
                'success' => 'Tipo de cliente <strong>' . $type_person->name . '</strong> cambiado exitosamente a <strong>' . ($newStatus ? 'Activo' : 'Inactivo') . '</strong>',
                'status' => $newStatus
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al cambiar el estado del tipo de cliente <strong>' . $type_person->name . '</strong>: ' . $th->getMessage()
            ], 500);
        }
    }
}
