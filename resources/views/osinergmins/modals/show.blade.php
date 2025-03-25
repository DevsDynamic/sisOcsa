<!-- Modal de Visualización de tipo de clientes -->
<div class="modal fade" id="modal-show" tabindex="-1" aria-labelledby="showCompanyLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">VER DETALLE DE LA <span class="texttitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
          
            <div class="modal-body">
                A continuación se muestra el detalle de la <span class="entity"></span> con código <strong><span class="textcode"></span></strong>
                <br><br>
                {{-- SECCION DEL CLIENTE --}}
                <div class="border rounded card-body border-secondary">
                    <div class="card-body">
                        <div class="form-row">
                            <!-- Número Documento -->
                            <div class="form-group col-lg-6">
                                <label for="document_number">Número de <span class="typeDocumentText"></span></label>
                                <input id="document_number" type="text" name="document_number" class="form-control" disabled>                               
                            </div>
                            <!-- Razon social -->
                            <div class="form-group col-lg-6">
                                <label for="business_name">Razón social</label>
                                <input id="business_name" type="text" name="business_name" class="form-control" disabled>                               
                            </div>
                            <!-- Dirección -->
                            <div class="form-group col-lg-12">
                                <label for="address">Dirección</label>
                                <input id="address" type="text" name="address" class="form-control" disabled>                               
                            </div>
                            <!-- Organizacion de ventas -->
                            <div class="form-group col-lg-6">
                                <label for="sales_organization">Organización de ventas</label>
                                <input id="sales_organization" type="text" name="sales_organization" class="form-control" disabled>                               
                            </div>
                            <!-- Nombre CET -->
                            <div class="form-group col-lg-6">
                                <label for="cet_name">Nombre CET</label>
                                <input id="cet_name" type="text" name="cet_name" class="form-control" disabled>                               
                            </div>
                            <!-- Oficina de ventas -->
                            <div class="form-group col-lg-6">
                                <label for="sales_office">Oficina de ventas</label>
                                <input id="sales_office" type="text" name="sales_office" class="form-control" disabled>                               
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