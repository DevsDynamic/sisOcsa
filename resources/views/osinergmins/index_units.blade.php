@extends('adminlte::page')

@section('title', 'Empresas')

@section('content_header')
    <h1>MIS UNIDADES CON RETRANSMISION A OSINERGMIN</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div id="units-notice" class="alert alert-warning d-none"></div>
            <table id="tablaPrincipal" class="table table-striped table-centered">
                <thead>
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">ID</th>
                        <th scope="col">Cliente</th>
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
                ajax: {
                    url: "{{ route('osinergmins.index-units-data') }}",
                    dataSrc: function(json) {
                        if (json.notice) $('#units-notice').removeClass('d-none').text(json.notice);
                        else $('#units-notice').addClass('d-none');
                        return json.data;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'uuid', name: 'uuid' },
                    { data: 'client_name', name: 'client_name', defaultContent: '-' },
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
