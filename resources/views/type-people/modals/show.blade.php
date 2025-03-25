<!-- Modal de Visualización de tipo de clientes -->
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
                                <label for="name">Nombre</label>
                                <input id="name" type="text" name="name" class="form-control" disabled>                                
                            </div>
                            <div class="form-group col-lg-6">
                                <label for="code">Código</label>
                                <input id="code" type="text" name="code" class="form-control" disabled>                               
                            </div>
                            <div class="form-group col-lg-6">
                                <label for="description">Descripción</label>
                                <input id="description" type="text" name="description" class="form-control" disabled>                               
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