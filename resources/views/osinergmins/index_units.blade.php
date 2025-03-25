@extends('adminlte::page')

@section('title', 'Empresas')

@section('content_header')
    <h1>MIS UNIDADES CON RETRANSMISION A OSINERGMIN</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="tablaPrincipal" class="table table-striped table-centered">
                <thead>
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">ID</th>
                        <th scope="col">Placa</th>
                        <th scope="col">Modelo</th>
                        <th scope="col">Kilometraje</th>
                        <th scope="col">Última actualización</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se llenarán dinámicamente por AJAX -->
                </tbody>
            </table>            
        </div>
        @include('osinergmins.modals.show_unit')
    </div>
@stop

@section('css')
    <style>
        .col-2 {
            min-width: 100px; /* Tamaño mínimo */
            min-height: 100px; /* Altura mínima */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 10px !important; /* Forzar esquinas redondeadas */
            overflow: hidden; /* Evita que el contenido sobresalga */
        }

        .shadow-sm {
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); /* Sombra suave */
        }
    </style>
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
                order: [[0, "desc"]],
                ajax: "{{ route('osinergmins.index-units-data') }}", // Ruta para obtener los datos por AJAX
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'uuid', name: 'uuid' },
                    { data: 'plate', name: 'company_name' },
                    { data: 'name_unit', name: 'name_unit' },
                    // { data: 'icon', name: 'icon' },
                    { data: 'mileage', name: 'mileage' },
                    { data: 'last_update', name: 'last_update' },
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
                                        <option value='10'>10</option>
                                        <option value='25'>25</option>
                                        <option value='50'>50</option>
                                        <option value='100'>100</option>
                                        <option value='-1'>Todo</option>
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
        });

        // Modal ver unidad
        $(document).on('click', '.show-unit', function () {
            let unidadId = $(this).data('id');
            let plate = $(this).data('plate');
            let modal = $('#modal-show-unit'); // Definir el modal correctamente
            let entity = "unidad";

            console.log('open modal show unit');

            modal.find('.texttitle').text((entity).toUpperCase()); // Cambiar .text() por .val() para input
            modal.find('.entity').text(entity);
            modal.find('.plate').text(plate);

            var array_length = '';
            
            // Llamada AJAX para obtener detalles
            $.ajax({
                url: `/osinergmin-retransmission/${unidadId}`, 
                method: 'GET',
                success: function(data) {
                    console.log(data);
                    $('#modal-show-unit').modal('show');
                    
                    // **SECCIÓN DE DETALLE DE VALE DE SALIDA**
                    // Primero, eliminamos cualquier DataTable previa para evitar duplicados
                    if ($.fn.DataTable.isDataTable('#detalles')) {
                        $('#detalles').DataTable().destroy();
                    }

                    // Limpiamos el contenido del tbody
                    let detallesTable = $('#detalles tbody');
                    detallesTable.empty(); 

                    // Agregar filas dinámicamente
                    if (data.data.length > 0) {
                        data.data.forEach((item, index) => {
                            let idRetransmision = item.id; // ✅ Accede correctamente al ID de cada detalle
                            let codigo = 'OSIN' + ('00000' + idRetransmision).slice(-5);
                            let fila = `
                                <tr class="newrow">
                                    <th scope="row">${index + 1}</th> 
                                    <td>${codigo}</td>
                                    <td>${item.event || 'Sin registro'}</td>
                                    <td>${item.speed || 'Sin registro'}</td>
                                    <td>${item.latitude || 'Sin registro'}</td>
                                    <td>${item.longitude || 'Sin registro'}</td>
                                    <td>${item.gpsDate || 'Sin registro'}</td>
                                    <td>${item.odometer || 'Sin registro'}</td>
                                    <td>${item.response_timestamp || 'Sin registro'}</td>
                                    <td>${item.response_message || 'Sin registro'}</td>
                                    <td>${item.response_suggestion || 'Sin registro'}</td>
                                    <td>${item.response_status || 'Sin registro'}</td>
                                </tr>
                            `;
                            detallesTable.append(fila);
                        });
                    } else {
                        // Si no hay datos, agregar un mensaje en la tabla
                        detallesTable.append('<tr><td colspan="12" class="text-center">No hay detalles registrados</td></tr>');
                    }

                    // Ahora sí inicializamos DataTables
                    $('#detalles').DataTable({
                        responsive: true,
                        paging: true,
                        ordering: true,
                        info: true,
                        autoWidth: false,
                        language: {
                            search: "Buscar:",
                            lengthMenu: "Mostrar _MENU_ registros",
                            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                            paginate: {
                                previous: "Anterior",
                                next: "Siguiente"
                            }
                        }
                    });
                }
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
                icon: 'info',
                title: '{{ session('info') }}',
                showConfirmButton: false,
                timer: 1500
            });
        </script>
    @endif
@stop