@extends('adminlte::page')

@section('title', 'Contactos')

@section('content_header')
    <h1>CONTACTOS
        @can('people.create')
            <a href="#" class="btn btn-success" 
                data-target="#modal-create" 
                data-toggle="modal" 
                data-type="co">
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
                        <th scope="col">DNI/RUC</th>
                        <th scope="col">Nombre completo</th>
                        <th scope="col">Tipo de cliente</th>
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
                        <th scope="col">DNI/RUC</th>
                        <th scope="col">Nombre completo</th>
                        <th scope="col">Tipo de cliente</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </tfoot>
            </table>
            @include('people.modals.create')
            @include('people.modals.show')
            @include('people.modals.edit')
            @include('people.modals.change-status')
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/people/index.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/people/index.js') }}"></script>

    <script>
        $(document).ready(function() {    
            // Inicialización de DataTables
            $('#tablaPrincipal').DataTable({
                responsive: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                searching: true,
                order: [[ 0, "desc" ]],
                ajax: "{{ route('people.index-co-data') }}",// Ruta para obtener los datos por AJAX
                columns: [
                    { data: 'id', name: 'id', // ID
                        render: function ( data, type, row, meta ) {             
                            return 'CLI' + ('00000' + row.id).slice(-5);
                        }
                    },
                    { // Número de documento
                        data: 'document_number', 
                        name: 'document_number' 
                    },
                    { // Nombre completo
                        data: 'full_name', 
                        name: 'full_name' 
                    },
                    // { // Empresa
                    //     data: 'company', 
                    //     name: 'company' 
                    // },
                    { // Tipo de cliente
                        data: 'type_person', 
                        name: 'type_person' 
                    },
                    { // Estado
                        data: 'status', 
                        name: 'status',
                        className: 'dt-center', // Clase específica para DataTables
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

            // Configuración del MODAL para CREAR nuevo cliente
            $('#modal-create').on('show.bs.modal', function (event) {
                //Almacenar datos
                var button = $(event.relatedTarget);
                var entity = "cliente";
                var type = button.data('type'); // Obtener el valor de data-type
                console.log('open modal create customer');

                // Configurar el modal con los datos del cliente
                var modal = $(this);
                modal.find('.texttitle').text((entity).toUpperCase()); // Cambiar .text() por .val() para input
                $('#modalType').val(type); // Asignar el valor al campo oculto dentro del modal
                $('#toggleSearchContainer').addClass('hidden').hide();
                
                // Inicialmente el input debe tener las esquinas redondeadas
                $('#document_number').css({
                    'border-top-right-radius': '.25rem',
                    'border-bottom-right-radius': '.25rem'
                });

                selectTypePerson();
                evaluateTypePerson();
            });

            // Configuración del MODAL para VISUALIZAR cliente
            $('#modal-show').on('show.bs.modal', function (event) {
                //Almacenar datos
                var button = $(event.relatedTarget);                
                var CustomerId = button.data('id');
                var entity = "Cliente";                
                console.log('open modal show customer');

                // Configurar el modal con los datos del cliente
                var modal = $(this);
                var formattedId = 'CLI' + ('00000' + CustomerId).slice(-5);
                modal.find('.texttitle').text((entity).toUpperCase()); // Cambiar .text() por .val() para input
                modal.find('.entity').text(entity);
                modal.find('.textcode').text(formattedId);

                // Construir la URL para la solicitud AJAX utilizando el ID del cliente
                var url = "{{ route('people.show', ':id') }}";
                url = url.replace(':id', CustomerId);

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (data) {
                        console.log(data.html);
                        // Aquí llenamos el modal con los datos recibidos
                        modal.find('#type_document').val(data.html.type_document);
                        modal.find('#document_number').val(data.html.document_number);
                        modal.find('#full_name').val(data.html.full_name);
                        modal.find('#company').val(data.html.company);
                        modal.find('#type_customer').val(data.html.type_customer);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener los datos del cliente:', error);
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al obtener los datos del cliente. Por favor, inténtelo nuevamente.',
                            showConfirmButton: true
                        });
                    }
                });
            });

            // Configuración del MODAL para EDITAR tipo de cliente
            $('#modal-edit').on('show.bs.modal', function (event) {
                //Almacenar datos
                var button = $(event.relatedTarget);                
                var CustomerId = button.data('id');
                var entity = "cliente";                
                console.log('open modal edit customer');

                // Configurar el modal con los datos del cliente
                var modal = $(this);
                modal.find('#hiddenIDCustomer').val(CustomerId);
                var formattedId = 'CLI' + ('00000' + CustomerId).slice(-5);
                modal.find('.texttitle').text((entity).toUpperCase()); // Cambiar .text() por .val() para input
                modal.find('.entity').text(entity);
                modal.find('.textcode').text(formattedId);

                // Construir la URL para la solicitud AJAX utilizando el ID del cliente
                var url = "{{ route('people.edit', ':id') }}";
                url = url.replace(':id', CustomerId);

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (data) {
                        console.log(data.html);
                        // Aquí llenamos el modal con los datos recibidos
                        modal.find('#type_document').val(data.html.type_document_id).trigger('change');
                        modal.find('#document_number').val(data.html.document_number);
                        modal.find('#full_name').val(data.html.full_name);
                        modal.find('#company').val(data.html.company_id).trigger('change');
                        modal.find('#type_customer').val(data.html.type_customer_id).trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener los datos del cliente:', error);
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al obtener los datos del cliente. Por favor, inténtelo nuevamente.',
                            showConfirmButton: true
                        });
                    }
                });                
            });

            // Configuración del modal de CAMBIO DE ESTADO de tipo de cliente (activar/inactivar)
            $('#modal-change-status').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var CustomerId = button.data('id');
                var statusAction = button.data('status');
                var entity = "cliente";

                // Configurar el modal con los datos del usuario
                var modal = $(this);
                var formattedId = 'CLI' + ('00000' + CustomerId).slice(-5);
                var statusText = (statusAction === 'activar') ? 'activar' : 'inactivar';

                modal.find('.texttitle').text((statusText+ ' ' + entity).toUpperCase());
                modal.find('.entity').text(entity);
                modal.find('.textstatus').text(statusText);
                modal.find('.textcode').text(formattedId);
                modal.find('#hiddenIDchangeStatus').val(CustomerId);
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
            });

            // Función para CREAR // Manejar el formulario crear uno cliente
            $('#formCreateCustomer').off('submit').on('submit', function (e) {
                e.preventDefault();
                console.log('clic button save create customer');

                var datainfo = $('#formCreateCustomer').serialize();
                console.log(datainfo);

                var formData = new FormData(this); // Usar FormData para enviar datos de archivo
                var typeDocument = $('#type_document').val();
                var documentNumber = $('#document_number').val();
                var selectedOption = $('#type_document option:selected');
                var maxLength = selectedOption.data('max-length'); // Obtener el maxLength del tipo de documento seleccionado
                // Obtener el texto de la opción seleccionada
                var typeDocumentText = $('#type_document option:selected').text().toLowerCase(); // Convertir a minúsculas                      

                if ((typeDocumentText == 'dni' || typeDocumentText == 'ruc') && documentNumber.length != maxLength) {
                    // Mostrar mensaje de alerta
                    Swal.fire({
                        position: 'center',
                        icon: 'info',
                        title: 'Alerta',
                        text: 'El ' + typeDocumentText.toUpperCase() + ' debe contener ' + maxLength + ' caracteres.',
                        timer: 2000
                    });
                    console.log('El ' + typeDocumentText.toUpperCase() + ' debe contener ' + maxLength + ' caracteres.');
                } else {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('people.store') }}', // Ajusta la ruta según tu configuración
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
                                showFormErrors('#formCreateCustomer',errors);
                                //showFormErrorsCreate(errors);
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
            });

            // Función para EDITAR cliente // Manejar el formulario editar tipo de cliente
            $('#formEditCustomer').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    console.log('clic button save edit customer');

                    var datainfo = $('#formEditCustomer').serialize();
                    console.log(datainfo);
                    var formData = new FormData(this);
                    formData.append('_method', 'PUT'); // Agregar método PUT para la actualización
                    
                    var CustomerId = $("#hiddenIDCustomer").val();
                    console.log('hiddenIDCustomer: '+CustomerId);                    

                    // Construir la URL para la solicitud AJAX utilizando el ID del cliente
                    var url = "{{ route('people.update', ':id') }}";
                    url = url.replace(':id', CustomerId);

                $.ajax({                    
                    url: url, // Ajusta la ruta según tu configuración
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.success) {
                            $('#modal-edit').modal('hide');
                            $('#tablaPrincipal').DataTable().ajax.reload(); // Actualiza la tabla si es necesario
                            console.log(data);
                            // Mostrar alerta de éxito
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: 'Éxito',
                                html: data.success,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { // Error de validación
                            var errors = xhr.responseJSON.errors;
                            showFormErrors('#formEditCustomer', errors);
                        } else {
                            var errorMessage = xhr.status + ': ' + xhr.statusText;
                            // Mostrar alerta de error general
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al editar el tipo de cliente: ' + errorMessage + '. Inténtelo de nuevo.',
                                showConfirmButton: true
                            });
                            console.log(errorMessage);
                        }
                    }
                });
            });

            // Función para cambiar el estado de tipo de cliente (activar/inactivar) // Manejar el formulario de cambio de estado
            $('#formChangeStatus').off('submit').on('submit', function (e) {
                e.preventDefault();
                console.log('clic button save change status');

                var datainfo = $('#formChangeStatus').serialize();
                console.log(datainfo);
                var formData = new FormData(this);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('people.change-status') }}', // Ajusta la ruta según tu configuración
                    data: formData,
                    processData: false, // Evita que jQuery procese los datos (importante para FormData)
                    contentType: false, // Evita que jQuery establezca el encabezado Content-Type (importante para FormData)
                    success: function(data) {
                        // Mostrar alerta de éxito o error
                        if (data.success) {
                            $('#modal-change-status').modal('hide'); // Ocultar el modal
                            $('#tablaPrincipal').DataTable().ajax.reload(); // Recargar la tabla
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: 'Éxito',
                                html: data.success,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            console.log('New status: ' + data.status);
                        } else if (data.error) {
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                html: data.error,
                                showConfirmButton: true
                            });
                            console.log(data.error);
                        }                        
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al cambiar el estado al cliente: ' + error + '. Inténtelo de nuevo.',
                            showConfirmButton: true
                        });
                        console.log(error);
                        $('#modal-change-status').modal('hide');
                    }
                });
            });  
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