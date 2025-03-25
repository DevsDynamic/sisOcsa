@extends('adminlte::page')

@section('title', 'Bandeja de Roles')

@section('content_header')
    <h1>BANDEJA DE ROLES
        @can('roles.create')
            <a href="{{ route('roles.create') }}" class="btn btn-success" title="Agregar nuevo rol">
                <i class="fas fa-plus-circle"></i> Agregar
            </a>
        @endcan
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="tablaPrincipal" class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Cod.</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los roles se cargarán dinámicamente aquí -->
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="col">Cod.</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </tfoot>
            </table>
            @include('roles.modals.show')
            @include('roles.modals.assign_role')
            @include('roles.modals.change_status')
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/roles/index.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/roles/index.js') }}"></script>

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
                ajax: "{{ route('roles.index-data') }}",// Ruta para obtener los datos por AJAX
                columns: [
                    { data: 'id', name: 'id', // ID
                        render: function ( data, type, row, meta ) {             
                            return 'ROL' + ('00000' + row.id).slice(-5);
                        }
                    },
                    { data: 'name', name: 'name' }, // Nombres completos
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

            // Configuración del modal de cambio de estado del rol (activar/inactivar)
            $('#modal-change-status').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var roleId = button.data('id');
                var statusAction = button.data('status');
                var entity = "rol";

                // Configurar el modal con los datos del usuario
                var modal = $(this);
                var formattedId = 'ROL' + ('00000' + roleId).slice(-5);
                var statusText = (statusAction === 'activar') ? 'activar' : 'inactivar';

                modal.find('.texttitle').text((statusText+ ' ' + entity).toUpperCase());
                modal.find('.entity').text(entity);
                modal.find('.textstatus').text(statusText);
                modal.find('.textcode').text(formattedId);
                modal.find('#hiddenIDchangeStatus').val(roleId);
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

            // Configuración del modal de asignación de roles
            $('#modal-assign-role').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var roleId = button.data('id');
                var roleName = button.data('name');
                var usersWithRole = button.data('users-with-role');
                var usersWithoutRole = button.data('users-without-role');

                // Configurar el modal con los datos del rol
                var modal = $(this);
                modal.find('.role-name').text(roleName);

                // Llenar la lista de usuarios con el rol (solo viñetas, divididos en dos columnas)
                var usersWithRoleList = $('#usersWithRoleList');
                usersWithRoleList.empty();

                if (usersWithRole && usersWithRole.length > 0) {
                    var currentUsersList = usersWithRole
                        .map(function(user) {
                            return `<div class="col-6"><li>${user.name}</li></div>`; // Coloca cada usuario en una columna
                        })
                        .join(''); // Combina todas las columnas

                    var userListHtml = `<div class="row">${currentUsersList}</div>`; // Envuelve la lista en un contenedor .row
                    usersWithRoleList.append(userListHtml); // Inserta la lista de usuarios
                } else {
                    usersWithRoleList.append('<p style="color:red">No hay usuarios con este rol.</p>');
                }

                // Combinar usuarios con y sin rol para `select2`
                var allUsers = [];
                
                // Agregar usuarios con el rol y marcarlos como seleccionados
                if (usersWithRole && usersWithRole.length > 0) {
                    usersWithRole.forEach(function(user) {
                        allUsers.push({ id: user.id, text: user.name, selected: true });
                    });
                }

                // Agregar usuarios sin el rol
                if (usersWithoutRole && usersWithoutRole.length > 0) {
                    usersWithoutRole.forEach(function(user) {
                        allUsers.push({ id: user.id, text: user.name });
                    });
                }

                // Inicializar select2 con los datos combinados
                var usersWithoutRoleList = $('#usersWithoutRoleList');
                usersWithoutRoleList.empty().select2({
                    data: allUsers,
                    width: '100%',
                    placeholder: "Selecciona usuarios...",
                    allowClear: true
                });

                // Asignar rol al hacer clic en el botón
                $('#modal-assign-role').find('.btn-primary').off('click').on('click', function() {
                    assignUsersRole(roleId, usersWithoutRoleList.val()); // Se envían solo los IDs seleccionados
                });
            });

            // Función para cambiar el estado del usuario (activar/inactivar)
            function changeStatus() {
                var formData = $('#formChangeStatus').serialize();
                console.log(formData);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('roles.change-status') }}', // Ajusta la ruta según tu configuración
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
                            text: 'Ocurrió un error al cambiar el estado al rol: ' + error + '. Inténtelo de nuevo.',
                            showConfirmButton: true
                        });

                        $('#modal-change-status').modal('hide');
                    }
                });
            }

            // Función para asignar el rol
            function assignUsersRole(roleId, selectedUsers) {
                // Preparar datos para enviar al servidor
                var formData = {
                    role_id: roleId,
                    users: selectedUsers
                };

                // Enviar datos al servidor via AJAX
                $.ajax({
                    type: 'POST',
                    url: '{{ route('roles.assign-users-role') }}', // Ajusta la ruta según tu configuración
                    data: formData,
                    success: function(data) {
                        $('#modal-assign-role').modal('hide');
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
                        // Verifica si la respuesta contiene un objeto JSON con el mensaje de error
                        let errorMessage = 'Ocurrió un error al asignar rol a los usuarios';
                        
                        // Si el servidor respondió con un mensaje de error en formato JSON
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error; // Usa el mensaje de error enviado desde el servidor
                        } else if (xhr.responseText) {
                            // Si no existe responseJSON, usa el texto de la respuesta
                            errorMessage += ': ' + xhr.responseText;
                        }

                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            showConfirmButton: true
                        });

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
                timer: 1500
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
                timer: 1500
            });
        </script>
    @endif
@stop