@extends('adminlte::page')

@section('title', 'Reportes')

@section('content_header')
    <div class="card mb-0">
        <div class="card-header">
            <h1><i>REPORTE</i> - <b>RETRANSMISIONES OSINERGMIN</b></h1>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form id="formulario" name="formulario">
                <div class="row">
                    <div class="form-group col-lg-3">
                        <label for="unit">Placa</label>
                        <select id="unit" name="unit"
                            class="form-control selectpicker @error('unit') is-invalid @enderror" style="width: 100%"
                            data-live-search="true">
                            <option value="">---- TODOS ----</option>
                            @foreach ($unitOptions as $unit)
                                <option value="{{ $unit['id'] }}">{{ $unit['plate'] }}</option>
                            @endforeach
                        </select>
                        @error('unit')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group col-lg-2 d-flex align-items-end">
                        <button type="button" id="bt_find" class="btn btn-success">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
            <br>
            <h4>Resultado</h4>
            <div class="table-responsive">
                <table id="tablaPrincipal" class="table table-striped table-centered" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Evento</th>
                            <th scope="col">Velocidad</th>
                            <th scope="col">Latitud</th>
                            <th scope="col">Longitud</th>
                            <th scope="col">Fecha de envío</th>
                            <th scope="col">Kilometraje</th>
                            <th scope="col">Fecha de respuesta</th>
                            <th scope="col">Mensaje</th>
                            <th scope="col">Sugerencia</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- La data se cargará dinámicamente por AJAX aquí -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Evento</th>
                            <th scope="col">Velocidad</th>
                            <th scope="col">Latitud</th>
                            <th scope="col">Longitud</th>
                            <th scope="col">Fecha de envío</th>
                            <th scope="col">Kilometraje</th>
                            <th scope="col">Fecha de respuesta</th>
                            <th scope="col">Mensaje</th>
                            <th scope="col">Sugerencia</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.1/css/buttons.bootstrap5.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {

            $.fn.dataTable.ext.buttons.excelFull = {
                text: '<i class="fas fa-file-excel"></i>',
                className: 'btn btn-success',
                action: function(e, dt, button, config) {
                    let oldStart = dt.settings()[0]._iDisplayStart;

                    dt.one('preXhr', function(e, s, data) {
                        data.start = 0;
                        data.length = -1; // traer todo
                    });

                    dt.one('xhr', function(e, s, data) {
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button,
                            config);

                        dt.one('preXhr', function(e, s, data) {
                            data.start = oldStart;
                        });

                        setTimeout(dt.ajax.reload, 0);
                    });

                    dt.ajax.reload();
                }
            };



            const tabla = $('#tablaPrincipal').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.view-osinergmin') }}",
                    data: function(d) {
                        d.unit = $('#unit').val(); // Enviar parámetro dinámico
                    }
                },
                language: {
                    emptyTable: "No hay información",
                    info: "Mostrando del _START_ al _END_ de _TOTAL_ Registros",
                    search: "Buscar:",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    "lengthMenu": "Mostrar " +
                        `<select class="custom-select custom-select-sm form-control form-control-sm">
                                    <option value = '10'>10</option>
                                    <option value = '25'>25</option>
                                    <option value = '50'>50</option>
                                    <option value = '100'>100</option>
                                    <option value = '-1'>All</option>
                                </select>` +
                        " Registros",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'event',
                        name: 'event'
                    },
                    {
                        data: 'speed',
                        name: 'speed'
                    },
                    {
                        data: 'latitude',
                        name: 'latitude'
                    },
                    {
                        data: 'longitude',
                        name: 'longitude'
                    },
                    {
                        data: 'gpsDate',
                        name: 'gpsDate'
                    },
                    {
                        data: 'odometer',
                        name: 'odometer'
                    },
                    {
                        data: 'response_timestamp',
                        name: 'response_timestamp'
                    },
                    {
                        data: 'response_message',
                        name: 'response_message'
                    },
                    {
                        data: 'response_suggestion',
                        name: 'response_suggestion'
                    },
                    {
                        data: 'response_status',
                        name: 'response_status'
                    }
                ],
                dom: 'Bfrtilp',
                buttons: [{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> ',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> ',
                        className: 'btn btn-light'
                    },
                    // {
                    //     extend: 'excel',
                    //     text: '<i class="fas fa-file-excel"></i> ',
                    //     className: 'btn btn-success'
                    // },
                    //'excelFull', // <-- este
                    $.fn.dataTable.ext.buttons.excelFull, // <-- ahora sí
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> ',
                        className: 'btn btn-danger'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> ',
                        className: 'btn btn-info'
                    },
                    {
                        extend: 'colvis',
                        text: 'Filtrar columnas'
                    }
                ]
            });

            $('#bt_find').click(function() {
                const unit = $('#unit').val();
                if (!unit) {
                    Swal.fire({
                        title: '¿Generar reporte para todas las unidades?',
                        text: "No has seleccionado una placa, ¿quieres generar el reporte con todas?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, generar',
                        cancelButtonText: 'No, cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            tabla.ajax.reload();
                        }
                    });
                } else {
                    tabla.ajax.reload();
                }
            });
        });
    </script>
@stop
