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
                            <!-- Tipo de Cliente -->
                            <div class="form-group col-lg-6">
                                <label for="type_person">Tipo de cliente</label>
                                <input id="type_person" type="text" name="type_person" class="form-control" disabled>                               
                            </div>

                            <!-- Sección de campos adicionales para contactos (solo si es contacto) -->
                            <div id="contactFields" style="display: none;" class="form-group col-lg-12">
                                <div class="row">
                                    <!-- email  -->
                                    <div class="form-group col-lg-6">
                                        <label for="email">Correo Electrónico</label>
                                        <input id="email" type="text" name="email" class="form-control" disabled>                               
                                    </div>
                                    <!-- Fecha de Nacimiento -->
                                    <div class="form-group col-lg-6">
                                        <label for="birthdate">Fecha de Nacimiento</label>
                                        <input id="birthdate" type="text" name="birthdate" class="form-control" disabled>                               
                                    </div>
                                    <!-- Dirección -->
                                    <div class="form-group col-lg-6">
                                        <label for="address">Dirección</label>
                                        <input id="address" type="text" name="address" class="form-control" disabled>                               
                                    </div>
                                    <!-- Teléfono -->
                                    <div class="form-group col-lg-6">
                                        <label for="phone_number">Teléfono</label>
                                        <input id="phone_number" type="text" name="phone_number" class="form-control" disabled>                               
                                    </div>
                                    <!-- Token (Alfanumérico) -->
                                    <div class="form-group col-lg-12">
                                        <label for="token">Token</label>
                                        <input id="token" type="text" name="token" class="form-control" disabled>                               
                                    </div>
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