<div class="modal fade" id="modal-convert-prospect" tabindex="-1" role="dialog" aria-labelledby="convertProspectTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div><small class="text-uppercase crm-eyebrow">Flujo comercial</small>
                    <h5 id="convertProspectTitle" class="modal-title">Convertir en contacto</h5>
                </div><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form id="form-convert-prospect" method="POST">@csrf
                <div class="modal-body">
                    <div class="alert alert-light border"><i class="fas fa-user-check text-primary mr-2"></i>El
                        prospecto <strong class="prospect-name"></strong> pasará a Contacto. El cambio quedará en su
                        historial.</div>
                    <div id="convert-errors" class="alert alert-danger d-none"></div>
                    <div class="form-section">
                        <h6>Identificación</h6>
                        <div class="row form-spaced">
                            <div class="form-group col-md-6"><label>Tipo de documento <span
                                        class="text-danger">*</span></label><select name="type_document_id"
                                    class="form-control">
                                    <option value="">Seleccionar</option>
                                    @foreach ($typeDocuments as $typeDocument)
                                        <option value="{{ $typeDocument->id }}">{{ $typeDocument->name }}</option>
                                    @endforeach
                                </select></div>
                            <div class="form-group col-md-6"><label>Número de documento <span
                                        class="text-danger">*</span></label><input name="document_number"
                                    class="form-control" maxlength="50"></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6>Datos de contacto</h6>
                        <div class="row form-spaced">
                            <div class="form-group col-md-6"><label>Correo <span
                                        class="text-danger">*</span></label><input type="email" name="email"
                                    class="form-control"></div>
                            <div class="form-group col-md-6"><label>Teléfono</label><input name="phone_number"
                                    class="form-control" maxlength="9" inputmode="numeric"></div>
                            <div class="form-group col-md-8"><label>Dirección</label><input name="address"
                                    class="form-control"></div>
                            <div class="form-group col-md-4"><label>Fecha de nacimiento</label><input type="date"
                                    name="birthdate" class="form-control"></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6>Conversión comercial</h6>
                        <div class="row form-spaced">
                            <div class="form-group col-md-6"><label>Motivo <span
                                        class="text-danger">*</span></label><select name="reason" class="form-control">
                                    <option value="">Seleccionar</option>
                                    <option>Contrató el servicio</option>
                                    <option>Solicitó alta como cliente</option>
                                    <option>Acuerdo comercial cerrado</option>
                                    <option>Otro</option>
                                </select></div>
                            <div class="form-group col-md-6"><label>Notas</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group col-12 mb-0">
                                <div class="custom-control custom-checkbox"><input type="checkbox"
                                        class="custom-control-input" id="convert-marketing-consent"
                                        name="marketing_consent" value="1"><label class="custom-control-label"
                                        for="convert-marketing-consent">Autorizó recibir comunicaciones
                                        comerciales</label></div><small class="text-muted">Registra el consentimiento;
                                    no debe marcarse automáticamente.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light border" data-dismiss="modal"><i
                            class="fas fa-times mr-1"></i>Cancelar</button><button type="submit"
                        class="btn btn-primary"><i class="fas fa-user-check mr-1"></i>Confirmar conversión</button>
                </div>
            </form>
        </div>
    </div>
</div>
