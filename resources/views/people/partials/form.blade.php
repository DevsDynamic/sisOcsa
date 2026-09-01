<div class="person-form">
    <div class="form-section">
        <h6><i class="fas fa-id-card mr-2"></i>Identificación</h6>
        <div class="row form-spaced">
            <div class="form-group col-lg-6"><label for="type_document">Tipo de documento <small
                        class="text-muted cp-optional">(opcional para prospectos)</small></label><select
                    id="type_document" name="type_document" class="form-control">
                    <option value="">Seleccione</option>
                    @foreach ($typeDocuments as $typeDocument)
                        <option value="{{ $typeDocument->id }}" data-max-length="{{ $typeDocument->max_length }}">
                            {{ $typeDocument->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-lg-6"><label for="document_number">Número de documento <small
                        class="text-muted cp-optional">(opcional para prospectos)</small></label>
                <div class="input-group"><input id="document_number" name="document_number" type="text"
                        class="form-control" placeholder="DNI o RUC"
                        value="{{ old('document_number', $customer->document_number ?? '') }}">
                    <div class="input-group-append" id="toggleSearchContainer"><button id="toggleSearch" type="button"
                            class="btn btn-outline-secondary"><i class="fas fa-search mr-1"></i><span
                                id="textSearch">Buscar</span></button></div>
                </div>
            </div>
            <div class="form-group col-lg-6"><label for="full_name">Nombre completo / razón social <span
                        class="text-danger">*</span></label><input id="full_name" name="full_name" class="form-control"
                    value="{{ old('full_name', $customer->full_name ?? '') }}" required></div>
            <div class="form-group col-lg-6"><label for="type_person">Etapa comercial <span
                        class="text-danger">*</span></label><select id="type_person" name="type_person"
                    class="form-control">
                    <option value="">Seleccione</option>
                    @foreach ($typePeople as $typePerson)
                        <option value="{{ $typePerson->id }}" data-code="{{ strtolower($typePerson->code) }}">
                            {{ $typePerson->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Una vez creado, el cambio de etapa se realiza mediante el flujo de
                    conversión.</small>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h6><i class="fas fa-address-book mr-2"></i>Contacto comercial</h6>
        <div class="row form-spaced">
            <div class="form-group col-lg-6"><label for="email">Correo <span
                        class="contact-required text-danger">*</span></label><input type="email" id="email"
                    name="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}"
                    placeholder="contacto@empresa.com"><small class="text-muted">Para prospectos se requiere correo o
                    teléfono.</small></div>
            <div class="form-group col-lg-6"><label for="phone_number">Teléfono</label><input id="phone_number"
                    name="phone_number" class="form-control"
                    value="{{ old('phone_number', $customer->phone_number ?? '') }}" maxlength="9" inputmode="numeric"
                    placeholder="999999999"></div>
            <div class="form-group col-lg-6"><label for="lead_source">Origen del prospecto</label><select
                    id="lead_source" name="lead_source" class="form-control">
                    <option value="">No especificado</option>
                    @foreach (['Referido', 'Página web', 'Redes sociales', 'Llamada', 'Evento', 'Campaña', 'Otro'] as $source)
                        <option value="{{ $source }}">{{ $source }}</option>
                    @endforeach
                </select></div>
            <div class="form-group col-lg-6"><label for="commercial_notes">Notas comerciales</label>
                <textarea id="commercial_notes" name="commercial_notes" class="form-control" rows="2" maxlength="3000"></textarea>
            </div>
            <div class="form-group col-12 mb-0">
                <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input"
                        id="marketing_consent" name="marketing_consent" value="1"><label
                        class="custom-control-label" for="marketing_consent">Autorizó recibir comunicaciones
                        comerciales</label></div><small class="text-muted">Debe existir una autorización real; sirve
                    como base para futuras campañas.</small>
            </div>
        </div>
    </div>

    <div id="contactFields" style="display:none" class="form-section">
        <h6><i class="fas fa-building mr-2"></i>Datos del contacto cliente</h6>
        <div class="row form-spaced">
            <div class="form-group col-lg-6"><label for="birthdate">Fecha de nacimiento</label><input type="date"
                    id="birthdate" name="birthdate" class="form-control"></div>
            <div class="form-group col-lg-6"><label for="address">Dirección</label><input id="address"
                    name="address" class="form-control"></div>
            <div class="form-group col-12"><label for="token">Token GPS OCSA <small class="text-muted">(solo si
                        usa la integración)</small></label><input id="token" name="token" class="form-control"
                    type="password" autocomplete="new-password" placeholder="Vacío conserva el token configurado">
                <small class="text-muted token-status"></small>
            </div>
        </div>
    </div>
</div>
