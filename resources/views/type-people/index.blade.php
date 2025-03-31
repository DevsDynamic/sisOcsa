@extends('adminlte::page')

@section('title', 'Tipos de clientes')

@section('content_header')
    <h1>TIPOS DE CLIENTES
        @can('roles.create')
            <a href="" data-target="#modal-create" data-toggle="modal">
                <button class="btn btn-success" title="Agregar tipo de cliente">
                    <i class="fas fa-plus-circle"></i> Agregar
                </button>
            </a>
        @endcan
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="tablaPrincipal" class="table table-striped table-centered">
                <thead>
                    <tr>
                        <th scope="col">Cod.</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Código</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- La data se cargará dinámicamente por AJAX aquí -->
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="col">Cod.</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Código</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </tfoot>
            </table>
            @include('type-people.modals.create')
            @include('type-people.modals.show')
            @include('type-people.modals.edit')
            @include('type-people.modals.change-status')
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/type_people/index.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Configuración global para las solicitudes AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });         

            // Inicialización de DataTables
            $('#tablaPrincipal').DataTable({
                responsive: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                searching: true,
                order: [[ 0, "desc" ]],
                ajax: "{{ route('type-people.index-table') }}",// Ruta para obtener los datos por AJAX
                columns: [
                    { data: 'id', name: 'id', // ID
                        render: function ( data, type, row, meta ) {             
                            return 'TPCLI' + ('00000' + row.id).slice(-5);
                        }
                    },
                    { data: 'name', name: 'name' }, // Nombres de tipo de cliente
                    { data: 'code', name: 'code' }, // Código del tipo de cliente
                    { data: 'description', name: 'description' }, // Descripcion de tipo de cliente
                    { data: 'status', name: 'status', // Estado   
                        render: function ( data, type, row, meta ) {
                            if (row.status == '1') {
                                return '<span class="badge badge-success">Activo</span>';
                            } else if (row.status == '0') {
                                return '<span class="badge badge-danger">Inactivo</span>';
                            } else {
                                return '<span class="badge badge-secondary">Sin Estado</span>';
                            }
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false} // Botones
                ],
                language: {
                    "decimal": "",
                    "emptyTable": "No hay información",
                    "info": "Mostrando del _START_ al _END_ de _TOTAL_ Registros",
                    "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                    "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar " +
                                    `<select class="custom-select custom-select-sm form-control form-control-sm">
                                        <option value = '10'>10</option>
                                        <option value = '25'>25</option>
                                        <option value = '50'>50</option>
                                        <option value = '100'>100</option>
                                        <option value = '-1'>Todo</option>
                                    </select>` +
                                    " Registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });

            // Configuración del MODAL para CREAR nuevo tipo de cliente
            $('#modal-create').on('show.bs.modal', function (event) {
                //Almacenar datos
                var button = $(event.relatedTarget);                 
                var entity = "tipo de cliente";
                console.log('open modal create type customer');

                // Configurar el modal con los datos del tipo de cliente
                var modal = $(this);
                modal.find('.texttitle').text((entity).toUpperCase()); // Cambiar .text() por .val() para input
                
                // Manejar el formulario crear uno tipo de cliente
                $('#formCreateTypeCustomer').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    createTypeCustomer(new FormData(this));
                    console.log('clic button create type customer');
                });
            });

            // Configuración del MODAL para VISUALIZAR tipo de cliente
            $('#modal-show').on('show.bs.modal', function (event) {
                //Almacenar datos
                var button = $(event.relatedTarget);                
                var typeId = button.data('id');
                var typeName = button.data('name');
                var typeCode = button.data('code');
                var typeDescription = button.data('description');
                var entity = "tipo de cliente";                
                console.log('open modal show type customer');

                // Configurar el modal con los datos del tipo de cliente
                var modal = $(this);
                var formattedId = 'TPCLI' + ('00000' + typeId).slice(-5);
                modal.find('.texttitle').text((entity).toUpperCase()); // Cambiar .text() por .val() para input
                modal.find('.entity').text(entity);
                modal.find('.textcode').text(formattedId);
                modal.find('#name').val(typeName);
                modal.find('#code').val(typeCode);
                modal.find('#description').val(typeDescription);
            });

            // Configuración del MODAL para EDITAR tipo de cliente
            $('#modal-edit').on('show.bs.modal', function (event) {
                //Almacenar datos
                var button = $(event.relatedTarget);                
                var typeId = button.data('id');
                var typeName = button.data('name');
                var typeCode = button.data('code');
                var typeDescription = button.data('description');
                var entity = "tipo de cliente";                
                console.log('open modal edit type customer');

                // Configurar el modal con los datos del tipo de cliente
                var modal = $(this);
                modal.find('#hiddenIDtypeCustomer').val(typeId);
                var formattedId = 'TPCLI' + ('00000' + typeId).slice(-5);
                modal.find('.texttitle').text((entity).toUpperCase()); // Cambiar .text() por .val() para input
                modal.find('.entity').text(entity);
                modal.find('.textcode').text(formattedId);
                modal.find('#name').val(typeName);
                modal.find('#code').val(typeCode);
                modal.find('#description').val(typeDescription);

                // Manejar el formulario editar tipo de cliente
                $('#formEditTypeCustomer').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    editTypeCustomer(new FormData(this));
                    console.log('clic button edit type customer');
                });
            });

            // Configuración del modal de CAMBIO DE ESTADO de tipo de cliente (activar/inactivar)
            $('#modal-change-status').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var typeId = button.data('id');
                var statusAction = button.data('status');
                var entity = "tipo de cliente";

                // Configurar el modal con los datos del usuario
                var modal = $(this);
                var formattedId = 'TPCLI' + ('00000' + typeId).slice(-5);
                var statusText = (statusAction === 'activar') ? 'activar' : 'inactivar';

                modal.find('.texttitle').text((statusText+ ' ' + entity).toUpperCase());
                modal.find('.entity').text(entity);
                modal.find('.textstatus').text(statusText);
                modal.find('.textcode').text(formattedId);
                modal.find('#hiddenIDchangeStatus').val(typeId);
                modal.find('#hiddenStatusAction').val(statusAction);

                // Cambiar el estilo de la cabecera del modal
                var header = modal.find('.modal-header');
                header.removeClass('btn-danger btn-success');
                if (statusAction === 'activar') {
                    header.addClass('btn-success');
                } else {
                    header.addClass('btn-danger');
                }

                // Cambiar el estilo del botón de confirmación
                var confirmButton = modal.find('.btn-confirm-status');
                confirmButton.removeClass('btn-danger btn-success');
                var icon = modal.find('.icon-status');
                icon.removeClass('fas fa-check-circle fas fa-user-times');
                if (statusAction === 'activar') {
                    confirmButton.addClass('btn-success');
                    icon.addClass('fas fa-check-circle');
                } else {
                    confirmButton.addClass('btn-danger');
                    icon.addClass('fas fa-user-times');
                }

                // Manejar el formulario de cambio de estado
                $('#formChangeStatus').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    changeStatus();
                });
            });

            // Función para CREAR nuevo tipo de cliente
            function createTypeCustomer(formData) {
                var datainfo = $('#formCreateTypeCustomer').serialize();
                console.log(datainfo);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('type-people.store') }}', // Ajusta la ruta según tu configuración
                    data: formData, 
                    processData: false,
                    contentType: false,
                    success: function(data, textStatus, xhr) {
                        if (xhr.status === 200) { // Verifica si el código de estado es 200
                            $('#modal-create').modal('hide');
                            $('#tablaPrincipal').DataTable().ajax.reload(); // Actualiza la tabla si es necesario
                            console.log(data);

                            // Mostrar alerta de éxito
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: 'Éxito',
                                html: data.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            // Si el código de estado no es 200, mostrar error
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al agregar el registro. Inténtelo de nuevo.',
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Si el código de estado es 422, mostrar los errores de validación
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            showFormErrorsCreate(errors);
                        } else {
                            // En caso de error 500, mostrar el mensaje de error general
                            var errorMessage = xhr.status + ': ' + xhr.statusText;
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al agregar el registro: ' + errorMessage + '. Inténtelo de nuevo.',
                                showConfirmButton: true
                            });
                            console.log(errorMessage);
                        }
                    }
                });
            }

            // Función para EDITAR tipo de cliente
            function editTypeCustomer(formData) {
                var id = $("#hiddenIDtypeCustomer").val();
                console.log(id);
                var datainfo = $('#formEditTypeCustomer').serialize();
                console.log(datainfo);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('type-people.update', '') }}/' + id, // Ajusta la ruta según tu configuración
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-HTTP-Method-Override': 'PUT' // Esto indica que el método debe ser tratado como PUT
                    },
                    success: function(data, textStatus, xhr) {
                        if (xhr.status === 200) { // Verifica si el código de estado es 200
                            $('#modal-edit').modal('hide');
                            $('#tablaPrincipal').DataTable().ajax.reload(); // Actualiza la tabla si es necesario
                            console.log(data);

                            // Mostrar alerta de éxito
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: 'Éxito',
                                html: data.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            // Si el código de estado no es 200, mostrar error
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al agregar el registro. Inténtelo de nuevo.',
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Si el código de estado es 422, mostrar los errores de validación
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            showFormErrorsEdit(errors);
                        } else {
                            // En caso de error 500, mostrar el mensaje de error general
                            var errorMessage = xhr.status + ': ' + xhr.statusText;
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al agregar el registro: ' + errorMessage + '. Inténtelo de nuevo.',
                                showConfirmButton: true
                            });
                            console.log(errorMessage);
                        }
                    }
                });
            }

            // Función para cambiar el estado de tipo de cliente (activar/inactivar)
            function changeStatus() {
                var formData = $('#formChangeStatus').serialize();
                console.log(formData);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('type-people.change-status') }}', // Ajusta la ruta según tu configuración
                    data: formData,
                    success: function(data) {
                        $('#modal-change-status').modal('hide');
                        $('#tablaPrincipal').DataTable().ajax.reload(); // Actualiza la tabla si es necesario

                        // Mostrar alerta de éxito o error
                        if (data.success) {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: 'Éxito',
                                html: data.success,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else if (data.error) {
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                html: data.error,
                                showConfirmButton: true
                            });
                        }                        
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al cambiar el estado al rol: ' + error + '. Inténtelo de nuevo.',
                            showConfirmButton: true
                        });

                        $('#modal-change-status').modal('hide');
                    }
                });
            }  

            // Función para limpiar los errores del formulario CREAR
            function clearFormErrorsCreate() {
                $('#formCreateTypeCustomer .is-invalid').removeClass('is-invalid');
                $('#formCreateTypeCustomer .text-danger').remove();
            }

            // Función para limpiar los errores del formulario EDITAR
            function clearFormErrorsEdit() {
                $('#formEditTypeCustomer .is-invalid').removeClass('is-invalid');
                $('#formEditTypeCustomer .text-danger').remove();
            }

            // Restablecer el formulario cuando el MODAL CREAR se oculta
            $('#modal-create').on('hidden.bs.modal', function() {
                $('#formCreateTypeCustomer')[0].reset();
                $('#createErrorMessages').hide().empty();
                clearFormErrorsCreate();
            });

            // Restablecer el formulario cuando el MODAL EDITAR se oculta
            $('#modal-edit').on('hidden.bs.modal', function() {
                $('#formEditTypeCustomer')[0].reset();
                clearFormErrorsEdit();
            });

            // Función para mostrar los errores de validación en el formulario CREAR
            function showFormErrorsCreate(errors) {
                clearFormErrorsCreate();
                $.each(errors, function(key, value) {
                    var input = $('#formCreateTypeCustomer [name="' + key + '"]');
                    input.addClass('is-invalid');
                    input.after('<small class="text-danger">' + value[0] + '</small>');
                });
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'Error',
                    text: errors.name,
                    showConfirmButton: true
                });
            }

            // Función para mostrar los errores de validación en el formulario EDITAR
            function showFormErrorsEdit(errors) {
                clearFormErrorsEdit();
                $.each(errors, function(key, value) {
                    var input = $('#formEditTypeCustomer [name="' + key + '"]');
                    input.addClass('is-invalid');
                    input.after('<small class="text-danger">' + value[0] + '</small>');
                });
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'Error',
                    text: errors.name,
                    showConfirmButton: true
                });
            }
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: true
            });
        </script>
    @endif

    @if (session('info'))
        <script>
            Swal.fire({
                position: 'center',
                icon: 'info',
                title: '{{ session('info') }}',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif
@stop