@extends('adminlte::page')

@section('title', 'Empresas')

@section('content_header')
    <h1>CLIENTES CON RETRANSMISION A OSINERGMIN</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="tablaPrincipal" class="table table-striped table-centered">
                <thead>
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Empresa</th>
                        <th scope="col">Unidades</th>
                        {{-- <th scope="col">Acciones</th> --}}
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
                ajax: "{{ route('osinergmins.index-table') }}", // Ruta para obtener los datos por AJAX
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nombre_cliente', name: 'nombre_cliente',
                        render: function(data, type, row) {
                            return row.nombre_cliente ? row.nombre_cliente : "Sin registrar";
                        }
                    },
                    { data: 'empresa.company_name', name: 'empresa.company_name',
                        render: function(data, type, row) {
                            if (row.empresa) {
                                return `
                                    <strong>${row.empresa.company_name}</strong><br>
                                    Dirección: ${row.empresa.address ? row.empresa.address : "Sin registrar"}<br>
                                    Id: ${row.empresa.company_id ? row.empresa.company_id : "Sin registrar"}
                                `;
                            } else {
                                return "Sin registrar";
                            }
                        }
                    },
                    { data: 'units', name: 'units',
                        render: function(data, type, row) {
                            let unidadesHtml = '';

                            if (Array.isArray(row.units) && row.units.length > 0) {
                                unidadesHtml += `<div class="d-flex flex-wrap justify-content-start gap-2 g-2">`; // Contenedor flexible

                                row.units.forEach(function(unidad, index) {
                                    unidadesHtml += `
                                        <div class="col-2 p-3 text-center bg-white m-1 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center">
                                            <i class="fas fa-car fa-2x mb-2"></i>
                                            <div class="fw-bold">${unidad.plate}</div>
                                            <button class="btn btn-sm btn-info show-unit mt-2" 
                                                data-id="${unidad.uuid}" 
                                                data-plate="${unidad.plate}">
                                                Ver detalles
                                            </button>
                                        </div>
                                    `;
                                });

                                unidadesHtml += `</div>`; // Cierre del contenedor
                            } else {
                                unidadesHtml = '<p>No hay unidades disponibles</p>';
                            }

                            return unidadesHtml;
                        }
                    },
                    // { // Botones
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false
                    // }
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

            modal.modal('show');

            if ($.fn.DataTable.isDataTable('#detalles')) {
                $('#detalles').DataTable().destroy();
                $('#detalles tbody').empty();
            }

            $('#detalles').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [[1, 'desc']],
                ajax: `/osinergmin-retransmission/${encodeURIComponent(unidadId)}`,
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'code', name: 'id', searchable: false },
                    { data: 'event', name: 'event', defaultContent: 'Sin registro' },
                    { data: 'speed', name: 'speed', defaultContent: 'Sin registro' },
                    { data: 'latitude', name: 'latitude', defaultContent: 'Sin registro' },
                    { data: 'longitude', name: 'longitude', defaultContent: 'Sin registro' },
                    { data: 'gpsDate', name: 'gpsDate', defaultContent: 'Sin registro' },
                    { data: 'odometer', name: 'odometer', defaultContent: 'Sin registro' },
                    { data: 'response_timestamp', name: 'response_timestamp', defaultContent: 'Sin registro' },
                    { data: 'response_message', name: 'response_message', defaultContent: 'Sin registro' },
                    { data: 'response_suggestion', name: 'response_suggestion', defaultContent: 'Sin registro' },
                    { data: 'response_status', name: 'response_status', defaultContent: 'Sin registro' }
                ],
                language: {
                    emptyTable: 'No hay detalles registrados',
                    processing: 'Consultando retransmisiones...',
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    paginate: { previous: 'Anterior', next: 'Siguiente' }
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
