@extends('adminlte::page')
@section('title', 'Retransmisiones a Osinergmin')

@section('content_header')
    <div>
        <h1 class="mb-1"><i class="fas fa-broadcast-tower text-info mr-2"></i>Retransmisiones a Osinergmin</h1>
        <p class="text-muted mb-0">Clientes y unidades vinculados al proveedor GPS OCSA.</p>
    </div>
@stop

@section('content')
    <div class="integration-state-note"><i class="fas fa-info-circle"></i><span><strong>Estado de las unidades</strong>
            <small><b>Operativo</b> confirma GPS reciente y envÃ­o aceptado. <b>GPS desactualizado</b> significa que
                Osinergmin acepta la trama, pero la posiciÃ³n recibida desde OCSA es antigua.</small></span></div>
    <div class="card client-units-card">
        <div class="card-body">
            <table id="tablaPrincipal" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>Cliente</th>
                        <th>Empresa</th>
                        <th>Unidades</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    @include('osinergmins.modals.show_unit')
@stop

@section('css')
    <style>
        .client-units-card>.card-body {
            padding: 1.25rem
        }
        .integration-state-note { display:flex; align-items:center; gap:12px; padding:13px 16px; margin-bottom:16px;
            border:1px solid #d9e4ec; border-radius:12px; background:#fff }
        .integration-state-note i { color:#1fa3b8; font-size:1.35rem }
        .integration-state-note strong, .integration-state-note small { display:block }
        .integration-state-note small { color:#687789 }

        .company-detail strong {
            display: block;
            font-size: .95rem
        }

        .company-detail small {
            display: block;
            color: #6d7885;
            line-height: 1.45
        }

        .unit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(125px, 1fr));
            gap: 10px;
            min-width: 280px
        }

        .unit-tile {
            border: 1px solid #e0e6ed;
            border-radius: 10px;
            padding: 11px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 9px;
            transition: .18s
        }

        .unit-tile:hover {
            border-color: #1fa3b8;
            box-shadow: 0 4px 12px rgba(31, 163, 184, .12)
        }

        .unit-tile i {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #e9f7fa;
            color: #16889a;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .unit-tile strong {
            display: block;
            font-size: .84rem
        }

        .unit-tile button {
            padding: 0;
            border: 0;
            background: none;
            color: #16889a;
            font-size: .75rem;
            font-weight: 700
        }

        .modal-eyebrow {
            font-size: .67rem;
            letter-spacing: .12em;
            opacity: .85
        }

        .history-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 12px 14px;
            margin-bottom: 16px;
            border: 1px solid #dce4ec;
            border-radius: 10px;
            background: #f8fafc
        }

        .history-summary>div:first-child {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .history-summary i {
            font-size: 1.4rem;
            color: #1fa3b8
        }

        .history-summary strong,
        .history-summary small {
            display: block
        }

        .history-summary small {
            color: #6c7887
        }

        .status-legend {
            display: flex;
            gap: 6px;
            flex-wrap: wrap
        }

        .history-table-shell {
            border: 1px solid #dfe5ec;
            border-radius: 10px;
            padding: 14px;
            background: #fff;
            overflow: hidden
        }

        .history-table-shell .dataTables_scroll {
            border: 1px solid #e5eaf0;
            border-radius: 8px;
            overflow: hidden
        }

        .history-table-shell .dataTables_scrollHead {
            background: #eef4f8
        }

        .history-table-shell table.dataTable thead th {
            background: #eef4f8;
            color: #314355;
            border-bottom: 2px solid #cfdae4 !important
        }

        .history-table-shell table.dataTable tbody td {
            vertical-align: top;
            line-height: 1.35
        }

        .history-response {
            min-width: 255px;
            max-width: 380px
        }

        .history-response strong {
            display: block;
            font-size: .82rem
        }

        .history-response small {
            display: block;
            color: #657384;
            margin-top: 3px
        }

        .history-response .response-date {
            color: #1769aa;
            margin-bottom: 4px
        }

        .coordinate-value {
            font-family: monospace;
            font-size: .78rem;
            white-space: nowrap
        }

        .movement-data {
            white-space: nowrap
        }

        .movement-data small {
            display: block;
            color: #6c7887
        }

        .history-table-shell .dataTables_filter,
        .history-table-shell .dataTables_length {
            margin-bottom: 10px
        }

        .history-table-shell .dataTables_scrollBody {
            overflow-x: auto !important
        }

        .history-table-shell .dataTables_wrapper {
            overflow: visible !important
        }

        @media(max-width:767px) {
            .history-summary {
                align-items: flex-start;
                flex-direction: column
            }

            .status-legend {
                display: none
            }

            .history-table-shell {
                padding: 8px
            }

            .retransmission-modal .modal-body {
                padding: .8rem
            }

            .unit-grid {
                grid-template-columns: repeat(2, minmax(115px, 1fr))
            }
        }

        .unit-tile.show-unit {
            cursor: pointer
        }

        .unit-tile.show-unit:hover,
        .unit-tile.show-unit:focus {
            border-color: #1fa3b8;
            box-shadow: 0 4px 12px rgba(31, 163, 184, .12);
            outline: 0;
            transform: translateY(-1px)
        }

        .unit-history-link {
            color: #16889a;
            font-size: .75rem;
            font-weight: 700
        }
        .unit-health-dot { display:inline-flex; align-items:center; gap:5px; margin-top:4px; font-size:.68rem; font-weight:800 }
        .unit-health-dot::before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor }
        .unit-health-dot.success { color:#188044 }.unit-health-dot.warning { color:#936900 }
        .unit-health-dot.danger { color:#c7332d }.unit-health-dot.unknown { color:#748292 }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            const escapeHtml = value => $('<div>').text(value == null ? '' : String(value)).html();
            const empty = value => value === null || value === undefined || String(value).trim() === '';
            const display = value => empty(value) ? 'Sin registro' : escapeHtml(value);
            const formatDate = value => {
                if (empty(value)) return 'Sin registro';
                const normalized = String(value).includes('T') ? String(value) : String(value).replace(' ',
                    'T') + '-05:00';
                const date = new Date(normalized);
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
                return '<span class="status-pill unknown" title="Osinergmin respondió, pero no informó aceptación ni rechazo. Estado técnico: ' +
                    escapeHtml(value) + '">Sin confirmación</span>';
            };

            $('#tablaPrincipal').DataTable({
                responsive: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                searching: true,
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                ajax: {
                    url: "{{ route('osinergmins.index-table') }}",
                    error: function(xhr) {
                        Swal.fire('No se pudo cargar',
                            'La consulta de clientes y unidades no respondió correctamente.',
                            'error');
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '45px'
                    },
                    {
                        data: 'nombre_cliente',
                        name: 'nombre_cliente',
                        render: data => '<strong>' + display(data) + '</strong>'
                    },
                    {
                        data: 'empresa',
                        name: 'empresa',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            if (!data || !data.company_name)
                            return '<span class="text-muted">Sin empresa registrada</span>';
                            return '<div class="company-detail"><strong>' + display(data
                                    .company_name) + '</strong><small>' + display(data.address) +
                                '</small><small>ID OCSA: ' + display(data.company_id) +
                                '</small></div>';
                        }
                    },
                    {
                        data: 'units',
                        name: 'units',
                        orderable: false,
                        searchable: false,
                        render: function(units) {
                            if (!Array.isArray(units) || !units.length)
                            return '<span class="text-muted">Sin unidades</span>';
                            return '<div class="unit-grid">' + units.map(unit =>
                                '<div class="unit-tile show-unit" role="button" tabindex="0" data-id="' +
                                escapeHtml(unit.uuid) + '" data-plate="' + escapeHtml(unit
                                    .plate) + '"><i class="fas fa-car-side"></i><div><strong>' +
                                display(unit.plate) +
                                '</strong><span class="unit-health-dot ' + escapeHtml(unit.operational?.tone || 'unknown') + '">' +
                                display(unit.operational?.label || 'Sin historial') + '</span><span class="unit-history-link">Ver historial</span></div></div>'
                                ).join('') + '</div>';
                        }
                    }
                ],
                language: {
                    emptyTable: 'No hay clientes con unidades disponibles',
                    info: 'Mostrando _START_–_END_ de _TOTAL_',
                    infoEmpty: 'Sin registros',
                    lengthMenu: 'Mostrar _MENU_',
                    loadingRecords: 'Cargando...',
                    processing: 'Consultando OCSA...',
                    search: 'Buscar',
                    zeroRecords: 'No se encontraron coincidencias',
                    paginate: {
                        next: 'Siguiente',
                        previous: 'Anterior'
                    }
                }
            });

            const refreshUnits = window.setInterval(() => {
                if (!document.hidden && !$('#modal-show-unit').hasClass('show')) {
                    $('#tablaPrincipal').DataTable().ajax.reload(null, false);
                }
            }, 60000);
            const refreshHistory = window.setInterval(() => {
                if (!document.hidden && $('#modal-show-unit').hasClass('show') && $.fn.DataTable.isDataTable('#detalles')) {
                    $('#detalles').DataTable().ajax.reload(null, false);
                }
            }, 60000);
            $(window).on('beforeunload', () => { window.clearInterval(refreshUnits); window.clearInterval(refreshHistory); });

            $(document).on('click', '.show-unit', function() {
                const unitId = $(this).data('id'),
                    plate = $(this).data('plate'),
                    modal = $('#modal-show-unit');
                modal.find('.plate').text(plate || 'Sin placa');
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
                        url: '{{ url('/osinergmin-retransmission') }}/' + encodeURIComponent(
                            unitId),
                        error: function(xhr) {
                            $('#unit-history-notice').removeClass('d-none').text(xhr.status ===
                                403 ? 'No tienes permiso para consultar esta unidad.' :
                                'No se pudo cargar el historial. Revisa el monitor de integración o intenta nuevamente.'
                                );
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            width: '45px'
                        },
                        {
                            data: 'event',
                            name: 'event',
                            render: data => '<strong>' + display(data) + '</strong>'
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: row => '<div class="movement-data"><strong>' + display(row
                                    .speed) + ' km/h</strong><small>' + display(row.odometer) +
                                ' km</small></div>'
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: row => '<span class="coordinate-value">' + display(row
                                .latitude) + '<br>' + display(row.longitude) + '</span>'
                        },
                        {
                            data: 'gpsDate',
                            name: 'gpsDate',
                            render: data => formatDate(data)
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: true,
                            render: function(row) {
                                const message = empty(row.response_message) ?
                                    'Sin mensaje descriptivo' : row.response_message;
                                const suggestion = empty(row.response_suggestion) ?
                                    'Sin sugerencia adicional' : row.response_suggestion;
                                return '<div class="history-response"><small class="response-date">' +
                                    formatDate(row.response_timestamp) +
                                    '</small><strong>' + display(message) +
                                    '</strong><small>' + display(suggestion) +
                                    '</small></div>';
                            }
                        },
                        {
                            data: 'response_status',
                            name: 'response_status',
                            render: data => statusBadge(data)
                        }
                    ],
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
                    },
                    drawCallback: function() {
                        $.fn.dataTable.tables({
                            visible: true,
                            api: true
                        }).columns.adjust();
                    }
                });
            });
            $(document).on('keydown', '.unit-tile.show-unit', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $(this).trigger('click');
                }
            });
            $('#modal-show-unit').on('shown.bs.modal', function() {
                if ($.fn.DataTable.isDataTable('#detalles')) $('#detalles').DataTable().columns.adjust();
            });
        });
    </script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: @json(session('success')),
                showConfirmButton: false,
                timer: 1700
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: @json(session('error'))
            });
        </script>
    @endif
@stop
