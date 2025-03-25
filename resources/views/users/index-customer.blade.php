@extends('adminlte::page')

@section('title', 'Usuarios clientes')

@section('content_header')
    <h1>USUARIOS CLIENTES
        @can('users.create')
            <a href="{{ route('users.create', ['type' => 'customer']) }}" class="btn btn-success" title="Agregar nuevo usuario">
                <i class="fas fa-plus-circle"></i> Agregar
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
                        <th scope="col">Usuario</th>
                        <th scope="col">Acceso al sistema</th>
                        <th scope="col">Estado</th>
                        <th scope="col">F. Registro</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- La data se cargará dinámicamente por AJAX aquí -->
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="col">Cod.</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Acceso al sistema</th>
                        <th scope="col">Estado</th>
                        <th scope="col">F. Registro</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </tfoot>
            </table>
            @include('users.modals.assign_role')
            @include('users.modals.change_status')
            @include('users.modals.access_system')
            @include('users.modals.show')
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/users/index.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/users/index.js') }}"></script>
    
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
                ajax: "{{ route('users.index-customer-data') }}",
                columns: [
                    {
                        data: 'id',
                        name: 'id', // ID
                        render: function ( data, type, row, meta ) {             
                            return 'USER' + ('00000' + row.id).slice(-5);
                        }
                    },
                    { // Nombres completos
                        data: 'username', 
                        name: 'username' 
                    },
                    { // Acceso al sistema
                        data: 'access', 
                        className: 'dt-center', // Clase específica para DataTables
                        name: 'access',
                        render: function ( data, type, row, meta ) {
                            if (row.access == '1') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else if (row.access == '0') {
                                return '<span class="badge badge-danger">No</span>';
                            } else {
                                return '<span class="badge badge-secondary">NO DEFINIDO</span>';
                            }
                        }
                    },
                    { // Estado
                        data: 'status', 
                        className: 'dt-center', // Clase específica para DataTables
                        name: 'status',  
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
                    { // Fecha de registro
                        data: 'created_date', 
                        name: 'created_date'
                    },
                    { // Botones
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false
                    }
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

            // Configuración del modal de asignación de roles
            $('#modal-assign-role').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var userId = button.data('id');
                var userName = button.data('name');
                var currentRole = button.data('current-role');
                var roles = @json($roles); // Asegúrate de pasar los roles a JavaScript

                $("#hiddenIDassignRole").val(userId);

                var formattedId = 'USER' + ('00000' + userId).slice(-5);
                $("#name").val(userName); // Cambiar .text() por .val() para input
                $("#currentrole").val(currentRole);
                $(".textcode").text(formattedId);

                // Limpiar roles previos
                $('#roleList').empty();

                // Cargar roles dinámicamente
                roles.forEach(function(role) {
                    var checked = (role.name === currentRole) ? 'checked' : '';
                    $('#roleList').append(
                        '<div>' +
                        '<label>' +
                        '<input type="radio" name="roles" value="' + role.name + '" class="mr-1" ' + checked + '>' +
                        role.name +
                        '</label>' +
                        '</div>'
                    );
                });

                // Manejar el formulario de cambio de rol
                $('#formAssignRole').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    assignRole();
                });
            });

            // Configuración del modal de cambio de estado del usuario (activar/inactivar)
            $('#modal-change-status').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var userId = button.data('id');
                var statusAction = button.data('status');
                var entity = "usuario";

                // Configurar el modal con los datos del usuario
                var modal = $(this);
                var formattedId = 'USER' + ('00000' + userId).slice(-5);
                var statusText = (statusAction === 'activar') ? 'activar' : 'inactivar';

                modal.find('.texttitle').text((statusText+ ' ' + entity).toUpperCase());
                modal.find('.entity').text(entity);
                modal.find('.textstatus').text(statusText);
                modal.find('.textcode').text(formattedId);
                modal.find('#hiddenIDchangeStatus').val(userId);
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

            // Configuración del modal de acceso al sistema (otorgar/quitar)
            $('#modal-access-system').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var userId = button.data('id');
                var accessAction = button.data('access-system');
                var entity = "usuario";

                // Configurar el modal con los datos del usuario
                var modal = $(this);
                var formattedId = 'USER' + ('00000' + userId).slice(-5);
                var accessText = (accessAction === 'otorgar') ? 'otorgar acceso' : 'quitar acceso';

                modal.find('.texttitle').text((accessText + ' al ' + entity).toUpperCase());
                modal.find('.entity').text(entity);
                modal.find('.textaccess').text(accessText);
                modal.find('.textcode').text(formattedId);
                modal.find('#hiddenIDaccessSystem').val(userId);
                modal.find('#hiddenAccessSystem').val(accessAction);

                // Cambiar el estilo de la cabecera del modal
                var header = modal.find('.modal-header');
                header.removeClass('btn-danger btn-success');
                if (accessAction === 'otorgar') {
                    header.addClass('btn-success');
                } else {
                    header.addClass('btn-danger');
                }

                // Cambiar el estilo del botón de confirmación
                var confirmButton = modal.find('.btn-confirm-access');
                confirmButton.removeClass('btn-danger btn-success');
                var icon = modal.find('.icon-access');
                icon.removeClass('fas fa-user-lock fas fa-key');
                if (accessAction === 'otorgar') {
                    confirmButton.addClass('btn-success');
                    icon.addClass('fas fa-key');
                } else {
                    confirmButton.addClass('btn-danger');
                    icon.addClass('fas fa-user-lock');
                }

                // Manejar el formulario de cambio de estado
                $('#formAccessSystem').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    accessSystem();
                });
            });                
            
            // Función para cambiar el estado del usuario (activar/inactivar)
            function changeStatus() {
            var formData = $('#formChangeStatus').serialize();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('users.change-status') }}', // Ajusta la ruta según tu configuración
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
                                text: data.error,
                                showConfirmButton: true
                            });
                        }                        
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al cambiar el estado al usuario: ' + error + '. Inténtelo de nuevo.',
                            showConfirmButton: true
                        });

                        $('#modal-change-status').modal('hide');
                    }
                });
            }

            // Función para cambiar acceso al sistema (otorgar/quitar)
            function accessSystem() {
            var formData = $('#formAccessSystem').serialize();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('users.access-system') }}',
                    data: formData,
                    success: function(data) {
                        $('#modal-access-system').modal('hide');
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
                                text: data.error,
                                showConfirmButton: true
                            });
                        }                        
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al cambiar el acceso al sistema: ' + error + '. Inténtelo de nuevo.',
                            showConfirmButton: true
                        });
                        $('#modal-access-system').modal('hide');
                    }
                });
            }

            // Función para asignar rol a usuario
            function assignRole() {
                var formData = $('#formAssignRole').serialize();
                console.log(formData);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('roles.assign-role') }}', // Ajusta la ruta según tu configuración
                    data: formData,                    
                    success: function(data) {
                        $('#modal-assign-role').modal('hide');
                        $('#tablaPrincipal').DataTable().ajax.reload(); // Actualiza la tabla si es necesario
                        console.log(data);

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
                                text: data.error,
                                showConfirmButton: true
                            });
                        }                        
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al asignar nuevo rol al usuario: ' + error + '. Inténtelo de nuevo.',
                            showConfirmButton: true
                        });
                        console.log(error);

                        $('#modal-assign-role').modal('hide');
                    }
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
                icon: 'success',
                title: '{{ session('info') }}',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif
@stop