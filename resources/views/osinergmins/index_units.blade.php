@extends('adminlte::page')
@section('title', 'Mis unidades')
@section('content_header')<div>
        <h1 class="mb-1"><i class="fas fa-car-side text-info mr-2"></i>Mis unidades</h1>
        <p class="text-muted mb-0">Unidades disponibles en OCSA y su historial de retransmisión.</p>
</div>@stop
@section('content')
    <div id="units-notice" class="alert alert-warning d-none"></div>
    <div class="unit-health-guide" role="note">
        <div><i class="fas fa-info-circle"></i><span><strong>Cómo interpretar el estado</strong><small><b>Operativo:</b>
                    GPS reciente y envío aceptado. <b>Posición antigua:</b> Osinergmin acepta, pero OCSA entrega una
                    posición antigua. <b>Alerta:</b> no hay transmisión reciente o el envío fue rechazado.</small></span></div>
        <div class="health-legend"><span class="health-key success">Operativo</span><span
                class="health-key warning">Posición antigua</span><span class="health-key danger">Alerta</span><span
                class="health-key unknown">Sin historial</span></div>
    </div>
    <div class="card">
        <div class="card-body">
            <table id="tablaPrincipal" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>Placa</th>
                        <th>Cliente</th>
                        <th>Modelo</th>
                        <th>Kilometraje</th>
                        <th>Última posición OCSA</th>
                        <th>Último envío</th>
                        <th>Estado operativo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    @include('osinergmins.modals.show_unit')
@stop
@section('css')<style>
        .unit-plate {
            font-weight: 800;
            color: #23364a
        }

        .show-unit-row {
            cursor: pointer
        }

        .show-unit-row:hover {
            background: #f0f9fb !important
        }

        .show-unit-row td:last-child {
            color: #16889a;
            font-weight: 700;
            white-space: nowrap
        }

        .unit-health-guide { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px 18px;
            margin-bottom:16px; border:1px solid #d9e4ec; border-radius:12px; background:#fff }
        .unit-health-guide>div:first-child { display:flex; align-items:center; gap:12px }
        .unit-health-guide i { color:#1fa3b8; font-size:1.45rem }
        .unit-health-guide strong, .unit-health-guide small { display:block }
        .unit-health-guide small { color:#687789 }
        .health-legend { display:flex; flex-wrap:wrap; gap:7px }
        .health-key, .operational-badge { display:inline-flex; align-items:center; gap:7px; border-radius:999px;
            padding:5px 10px; font-size:.76rem; font-weight:800; white-space:nowrap }
        .health-key::before, .operational-badge::before { content:''; width:8px; height:8px; border-radius:50%;
            background:currentColor; box-shadow:0 0 0 3px rgba(0,0,0,.04) }
        .health-key.success, .operational-badge.success { color:#17743b; background:#e4f4e9 }
        .health-key.warning, .operational-badge.warning { color:#8a6200; background:#fff1c7 }
        .health-key.danger, .operational-badge.danger { color:#bd2b25; background:#fde6e4 }
        .health-key.unknown, .operational-badge.unknown { color:#647384; background:#eaf0f4 }
        .date-cell strong, .date-cell small { display:block; white-space:nowrap }
        .date-cell small { color:#718093; margin-top:2px }
        @media(max-width:767px) { .unit-health-guide { align-items:flex-start; flex-direction:column } }
</style>@stop
@section('js')
    <script>
        $(function() {
            const escapeHtml = value => $('<div>').text(value == null ? '' : String(value)).html(),
                empty = value => value == null || String(value).trim() === '',
                display = value => empty(value) ? 'Sin registro' : escapeHtml(value);
            const formatDate = value => {
                if (empty(value)) return 'Sin registro';
                const normalized = String(value).includes('T') ? String(value) : String(value).replace(' ',
                    'T') + '-05:00',
                    date = new Date(normalized);
                return isNaN(date.getTime()) ? escapeHtml(value) : new Intl.DateTimeFormat('es-PE', {
                    dateStyle: 'short',
                    timeStyle: 'medium',
                    timeZone: 'America/Lima'
                }).format(date);
            };
            const statusBadge = status => {
                const value = String(status || 'UNKNOWN').toUpperCase();
                if (['SUCCESS', 'CREATED', 'ACCEPTED', 'OK'].includes(value))
                return '<span class="status-pill success" title="Estado técnico: ' + escapeHtml(value) +
                    '">Aceptado</span>';
                if (['ERROR', 'REJECTED', 'FAILED'].includes(value))
                return '<span class="status-pill error" title="Estado técnico: ' + escapeHtml(value) +
                    '">Rechazado</span>';
                return '<span class="status-pill unknown" title="Osinergmin no informó aceptación ni rechazo. Estado técnico: ' +
                    escapeHtml(value) + '">Sin confirmación</span>';
            };
            const operationalCell = operational => {
                const state = operational || {};
                const tone = ['success', 'warning', 'danger', 'unknown'].includes(state.tone) ? state.tone : 'unknown';
                return '<div class="operational-cell" title="' + escapeHtml(state.detail || '') + '"><span class="operational-badge ' + tone + '">' +
                    display(state.label || 'Sin historial') + '</span></div>';
            };
            const transmissionCell = operational => {
                const state = operational || {};
                const result = ['SUCCESS', 'CREATED', 'ACCEPTED', 'OK'].includes(String(state.response_status || '').toUpperCase()) ? 'Aceptado' :
                    (['ERROR', 'REJECTED', 'FAILED'].includes(String(state.response_status || '').toUpperCase()) ? 'Rechazado' : 'Sin confirmación');
                return '<div class="date-cell"><strong>' + formatDate(state.last_transmission_at) +
                    '</strong><small>Resultado: ' + result + '</small></div>';
            };
            const unitIcon = row => {
                const type = String((row.icon || '') + ' ' + (row.name_unit || '')).toLowerCase();
                if (type.includes('cisterna') || type.includes('tank')) return 'fa-truck-moving';
                if (type.includes('camion') || type.includes('truck')) return 'fa-truck';
                if (type.includes('bus')) return 'fa-bus';
                if (type.includes('moto')) return 'fa-motorcycle';
                if (type.includes('van')) return 'fa-shuttle-van';
                return 'fa-car-side';
            };
            const unitsTable = $('#tablaPrincipal').DataTable({
                responsive: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 25,
                order: [],
                ajax: {
                    url: @json(route('osinergmins.index-units-data')),
                    dataSrc: function(json) {
                        $('#units-notice').toggleClass('d-none', !json.notice).text(json.notice || '');
                        return json.data;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '45px'
                    },
                    {
                        data: 'plate',
                        name: 'plate',
                        render: (data, type, row) =>
                            '<span class="unit-plate"><i class="fas ' + unitIcon(row) + ' text-info mr-2"></i>' +
                            display(data) + '</span>'
                    },
                    {
                        data: 'client_name',
                        name: 'client_name',
                        defaultContent: '-'
                    }, {
                        data: 'name_unit',
                        name: 'name_unit',
                        render: data => display(data)
                    }, {
                        data: 'mileage',
                        name: 'mileage',
                        render: data => display(data) + ' km'
                    }, {
                        data: 'last_update',
                        name: 'last_update',
                        render: data => formatDate(data)
                    }, {
                        data: 'operational',
                        orderable: false,
                        searchable: false,
                        render: data => transmissionCell(data)
                    }, {
                        data: 'operational',
                        orderable: false,
                        searchable: false,
                        render: data => operationalCell(data)
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: () => '<i class="fas fa-history mr-1"></i>Ver historial'
                    }
                ],
                createdRow: function(row, data) {
                    $(row).addClass('show-unit-row').attr({
                        tabindex: 0,
                        'data-id': data.uuid,
                        'data-plate': data.plate,
                        'data-operational': JSON.stringify(data.operational || {})
                    });
                },
                language: {
                    emptyTable: 'No hay unidades disponibles',
                    info: 'Mostrando _START_–_END_ de _TOTAL_',
                    infoEmpty: 'Sin registros',
                    lengthMenu: 'Mostrar _MENU_',
                    processing: 'Consultando OCSA...',
                    search: 'Buscar',
                    zeroRecords: 'Sin coincidencias',
                    paginate: {
                        previous: 'Anterior',
                        next: 'Siguiente'
                    }
                }
            });
            const refreshUnits = window.setInterval(() => {
                if (!document.hidden && !$('#modal-show-unit').hasClass('show')) unitsTable.ajax.reload(null, false);
            }, 60000);
            const refreshHistory = window.setInterval(() => {
                if (!document.hidden && $('#modal-show-unit').hasClass('show') && $.fn.DataTable.isDataTable('#detalles')) {
                    $('#detalles').DataTable().ajax.reload(null, false);
                }
            }, 60000);
            $(window).on('beforeunload', () => { window.clearInterval(refreshUnits); window.clearInterval(refreshHistory); });
            $(document).on('click', '.show-unit-row', function() {
                openHistory($(this).data('id'), $(this).data('plate'), $(this).attr('data-operational'));
            }).on('keydown', '.show-unit-row', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).trigger('click');
                }
            });

            function openHistory(unitId, plate, operational) {
                const modal = $('#modal-show-unit');
                modal.find('.plate').text(plate || 'Sin placa');
                let state = {};
                try { state = JSON.parse(operational || '{}'); } catch (error) { state = {}; }
                modal.find('.unit-current-health').removeClass('d-none').html(operationalCell(state));
                $('#unit-history-notice').addClass('d-none').empty();
                modal.modal('show');
                if ($.fn.DataTable.isDataTable('#detalles')) {
                    $('#detalles').DataTable().destroy();
                    $('#detalles tbody').empty();
                }
                $('#detalles').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: false,
                    autoWidth: false,
                    scrollX: true,
                    scrollCollapse: true,
                    pageLength: 25,
                    order: [],
                    ajax: {
                        url: @json(url('/osinergmin-retransmission')) + '/' + encodeURIComponent(unitId),
                        error: function(xhr) {
                            $('#unit-history-notice').removeClass('d-none').text(xhr.status === 403 ?
                                'No tienes permiso para consultar esta unidad.' :
                                'No se pudo cargar el historial.');
                        }
                    },
                    columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '45px'
                    }, {
                        data: 'event',
                        name: 'event',
                        render: data => {
                            const labels = {acc_on:'Encendido',acc_off:'Apagado',battery_dc:'Batería desconectada',none:'Sin evento'};
                            return '<strong title="Código técnico: ' + escapeHtml(data || '') + '">' + display(labels[data] || data) + '</strong>';
                        }
                    }, {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: row => '<div class="movement-data"><strong>' + display(row.speed) +
                            ' km/h</strong><small>' + display(row.odometer) + ' km</small></div>'
                    }, {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: row => '<span class="coordinate-value">' + display(row.latitude) +
                            '<br>' + display(row.longitude) + '</span>'
                    }, {
                        data: 'gpsDate',
                        name: 'gpsDate',
                        render: data => formatDate(data)
                    }, {
                        data: null,
                        orderable: false,
                        searchable: true,
                        render: row =>
                            '<div class="history-response"><small class="response-date">' +
                            formatDate(row.response_timestamp) + '</small><strong>' + display(empty(
                                    row.response_message) ? 'Sin mensaje descriptivo' : row
                                .response_message) + '</strong><small>' + display(empty(row
                                    .response_suggestion) ? 'Sin sugerencia adicional' : row
                                .response_suggestion) + '</small></div>'
                    }, {
                        data: 'response_status',
                        name: 'response_status',
                        render: data => statusBadge(data)
                    }],
                    language: {
                        emptyTable: 'No hay retransmisiones en los últimos 30 días',
                        processing: 'Consultando historial...',
                        search: 'Buscar',
                        lengthMenu: 'Mostrar _MENU_',
                        info: 'Mostrando _START_–_END_ de _TOTAL_',
                        infoEmpty: 'Sin registros',
                        zeroRecords: 'Sin coincidencias',
                        paginate: {
                            previous: 'Anterior',
                            next: 'Siguiente'
                        }
                    }
                });
            }
            $('#modal-show-unit').on('shown.bs.modal', function() {
                if ($.fn.DataTable.isDataTable('#detalles')) $('#detalles').DataTable().columns.adjust();
            });
        });
    </script>
@stop
