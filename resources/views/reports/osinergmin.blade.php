@extends('adminlte::page')

@section('title', 'Reporte de retransmisiones')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="mb-1">Retransmisiones a Osinergmin</h1>
            <p class="text-muted mb-0">Consulta paginada del historial conservado durante los últimos 30 días.</p>
        </div>
        <span class="badge badge-light border px-3 py-2">
            <i class="fas fa-database mr-1"></i> Procesamiento del lado del servidor
        </span>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form id="report-filters" autocomplete="off">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-2">
                        <label for="from">Desde</label>
                        <input type="date" id="from" name="from" class="form-control" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="to">Hasta</label>
                        <input type="date" id="to" name="to" class="form-control" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="unit">Unidad / placa</label>
                        <select id="unit" name="unit" class="form-control">
                            <option value="">Todas las unidades</option>
                            @foreach ($unitOptions as $unit)
                                <option value="{{ $unit['id'] }}">{{ $unit['plate'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="status">Estado</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="SUCCESS" @selected(request('status') === 'SUCCESS')>Exitosos</option>
                            <option value="ERROR" @selected(request('status') === 'ERROR')>Errores</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <button type="submit" id="bt_find" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Consultar
                        </button>
                    </div>
                </div>
            </form>

            <div id="report-message" class="alert d-none" role="alert"></div>

            @if ($unitOptions->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Todavía no hay unidades retransmitidas en la base local. El selector se completará después del primer envío.
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
                <div>
                    <h5 class="mb-0">Resultados</h5>
                    <small class="text-muted">La búsqueda global trabaja sobre el rango seleccionado.</small>
                </div>
                <button type="button" id="export-excel" class="btn btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                </button>
            </div>

            <div class="table-responsive">
                <table id="tablaPrincipal" class="table table-striped table-hover table-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Placa</th>
                            <th>Evento</th>
                            <th>Velocidad</th>
                            <th>Latitud</th>
                            <th>Longitud</th>
                            <th>Fecha de envío</th>
                            <th>Kilometraje</th>
                            <th>Fecha de respuesta</th>
                            <th>Mensaje</th>
                            <th>Sugerencia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>
        #tablaPrincipal td { vertical-align: middle; white-space: nowrap; }
        #tablaPrincipal td:nth-child(10), #tablaPrincipal td:nth-child(11) {
            white-space: normal; min-width: 220px;
        }
        .dataTables_processing { z-index: 10; }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            const today = new Date();
            const monthAgo = new Date(today);
            monthAgo.setDate(monthAgo.getDate() - 29);
            const asDate = date => {
                const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
                return local.toISOString().slice(0, 10);
            };
            const todayValue = asDate(today);
            const monthAgoValue = asDate(monthAgo);

            const initialFrom = @json(request('from')) || monthAgoValue;
            const initialTo = @json(request('to')) || todayValue;
            $('#from').val(initialFrom).attr({ min: monthAgoValue, max: todayValue });
            $('#to').val(initialTo).attr({ min: monthAgoValue, max: todayValue });

            const showMessage = (message, type = 'danger') => {
                $('#report-message')
                    .removeClass('d-none alert-danger alert-warning alert-success')
                    .addClass('alert-' + type)
                    .text(message);
            };

            const table = $('#tablaPrincipal').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('reports.view-osinergmin') }}",
                    data: d => {
                        d.unit = $('#unit').val();
                        d.from = $('#from').val();
                        d.to = $('#to').val();
                        d.status = $('#status').val();
                    },
                    error: xhr => {
                        const errors = xhr.responseJSON && xhr.responseJSON.errors;
                        const message = errors
                            ? Object.values(errors).flat().join(' ')
                            : 'No fue posible cargar el reporte. Revisa el registro de la aplicación.';
                        showMessage(message);
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'plate', name: 'plate' },
                    { data: 'event', name: 'event' },
                    { data: 'speed', name: 'speed' },
                    { data: 'latitude', name: 'latitude' },
                    { data: 'longitude', name: 'longitude' },
                    { data: 'gpsDate', name: 'gpsDate' },
                    { data: 'odometer', name: 'odometer' },
                    { data: 'response_timestamp', name: 'response_timestamp' },
                    { data: 'response_message', name: 'response_message' },
                    {
                        data: 'response_suggestion', name: 'response_suggestion', defaultContent: 'Sin sugerencia',
                        render: data => $('<div>').text(data && String(data).trim() ? data : 'Sin sugerencia').html()
                    },
                    {
                        data: 'response_status', name: 'response_status',
                        render: data => {
                            const ok = data === 'SUCCESS';
                            return '<span class="badge badge-' + (ok ? 'success' : 'danger') + '">' +
                                $('<div>').text(data || 'SIN ESTADO').html() + '</span>';
                        }
                    }
                ],
                language: {
                    emptyTable: 'No hay retransmisiones en el rango seleccionado.',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Sin registros',
                    lengthMenu: 'Mostrar _MENU_',
                    loadingRecords: 'Cargando...',
                    processing: 'Consultando...',
                    search: 'Buscar:',
                    zeroRecords: 'No se encontraron coincidencias',
                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' }
                }
            });

            $('#report-filters').on('submit', function (event) {
                event.preventDefault();
                $('#report-message').addClass('d-none');
                table.ajax.reload();
            });

            $('#export-excel').on('click', function () {
                const params = new URLSearchParams({
                    unit: $('#unit').val() || '',
                    from: $('#from').val(),
                    to: $('#to').val(),
                    status: $('#status').val() || ''
                });
                window.location.href = "{{ route('reports.export-osinergmin') }}?" + params.toString();
            });
        });
    </script>
@stop
