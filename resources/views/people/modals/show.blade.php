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
                {{-- SECCION DEL CLIENTE --}}
                <div class="border rounded card-body border-secondary">
                    <div class="card-body">
                        <div class="form-row">
                            <!-- Tipo Documento -->
                            <div class="form-group col-lg-6">
                                <label for="type_document">Tipo Doc. Identidad</label>
                                <input id="type_document" type="text" name="type_document" class="form-control" disabled>                                
                            </div>
                            <!-- Número Documento -->
                            <div class="form-group col-lg-6">
                                <label for="document_number">Número Doc. Identidad</label>
                                <input id="document_number" type="text" name="document_number" class="form-control" disabled>                               
                            </div>
                            <!-- Nombres completo -->
                            <div class="form-group col-lg-6">
                                <label for="full_name">Nombre completo</label>
                                <input id="full_name" type="text" name="full_name" class="form-control" disabled>                               
                            </div>
                            <!-- Empresa -->
                            <div class="form-group col-lg-6">
                                <label for="company">Empresa</label>
                                <input id="company" type="text" name="company" class="form-control" disabled>                               
                            </div>
                            <!-- Tipo de Cliente -->
                            <div class="form-group col-lg-6">
                                <label for="type_customer">Tipo de cliente</label>
                                <input id="type_customer" type="text" name="type_customer" class="form-control" disabled>                               
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