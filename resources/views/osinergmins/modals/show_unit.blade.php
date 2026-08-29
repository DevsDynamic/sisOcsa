<div class="modal fade" id="modal-show-unit" tabindex="-1" role="dialog" aria-labelledby="showUnitTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content retransmission-modal">
            <div class="modal-header bg-info text-white">
                <div>
                    <small class="d-block text-uppercase modal-eyebrow">Historial de retransmisión</small>
                    <h5 class="modal-title" id="showUnitTitle">Unidad <strong class="plate"></strong></h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="history-summary">
                    <div><i class="far fa-calendar-alt"></i><span><strong>Últimos 30 días</strong><small>Los registros
                                más recientes aparecen primero.</small></span></div>
                    <div class="status-legend"><span class="status-pill success">Aceptado</span><span
                            class="status-pill error">Rechazado</span><span class="status-pill unknown">Sin
                            confirmación</span></div>
                </div>
                <div id="unit-history-notice" class="alert alert-danger d-none" role="alert"></div>
                <div class="history-table-shell">
                    <table id="detalles" class="table table-hover mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>N.º</th>
                                <th>Evento</th>
                                <th>Movimiento</th>
                                <th>Coordenadas</th>
                                <th>Fecha GPS</th>
                                <th>Respuesta de Osinergmin</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light border" data-dismiss="modal"><i
                        class="fas fa-times mr-1"></i>Cerrar</button></div>
        </div>
    </div>
</div>
