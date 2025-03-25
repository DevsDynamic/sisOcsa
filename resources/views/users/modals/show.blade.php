<!-- Modal de Visualización de usuario -->
<div class="modal fade" id="modal-show" tabindex="-1" aria-labelledby="showTypeCustomerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">VER DETALLE DE <span class="texttitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
          
            <div class="modal-body">
                A continuación se muestra el detalle del <span class="entity"></span> con código <strong><span class="textcode"></span></strong>
                <br><br>
                {{-- DATOS --}}
                <div class="border rounded card-body border-secondary">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-lg-12">
                                <label for="email">Correo</label>
                                <input id="email" type="text" class="form-control" disabled>
                            </div>
                            <div class="form-group col-lg-6">
                                <label for="role">Rol</label>
                                <input id="role" type="text" class="form-control" disabled>
                            </div>  
                            <div class="form-group col-lg-6">
                                <label for="created_date">Fecha de creación</label>
                                <input id="created_date" type="text" class="form-control" disabled>
                            </div>
                            <div class="form-group col-lg-6">
                                <label for="access">Acceso al sistema</label>
                                <div id="access" class="alert d-flex align-items-center" role="alert">
                                    <i id="access-icon" class="mr-2"></i>
                                    <span id="access-text"></span>
                                </div>
                            </div>
                            <div class="form-group col-lg-6">
                                <label for="status">Estado</label>
                                <div id="status" class="alert d-flex align-items-center" role="alert">
                                    <i id="status-icon" class="mr-2"></i>
                                    <span id="status-text"></span>
                                </div>
                            </div>
                            <div class="form-group col-lg-6">
                                <label>Foto perfil</label><br>
                                <div class="d-flex justify-content-center">
                                    <img id="profile_photo" src="" class="img-fluid rounded" style="max-height: 100px;">
                                </div>
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
