<!-- Modal de Visualización de rol -->
<div class="modal fade" id="modal-show" tabindex="-1" aria-labelledby="showRoleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">VER DETALLE DEL <span class="texttitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
          
            <div class="modal-body">
                A continuación se muestra el detalle del <span class="entity"></span> con código <strong><span class="textcode"></span></strong>
                <br><br>
                <div class="border rounded card-body border-secondary mb-3">
                    <div class="form-row">
                        <div class="card-body">
                            {{-- <div class="form-group">
                                <label for="name">Nombre del Rol</label>
                                <input type="text" name="name" class="form-control" placeholder="Ingrese nombre del rol" value="" disabled>
                            </div> --}}
                            <h1>LISTA DE PERMISOS</h1>
                            <br>
                            <div id="permissions-list">
                                <!-- Los permisos se cargarán aquí dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times-circle"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
