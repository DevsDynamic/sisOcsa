{{-- SECCION DE CLIENTE --}}
<div class="border rounded card-body border-secondary">
    <div class="card-body">
        <div class="form-row">
            <!-- Tipo Documento -->
            <div class="form-group col-lg-6">
                <label for="type_document">Tipo Doc. Identidad<span class="text-danger">*</span></label>
                <select id="type_document" name="type_document" class="form-control" style="width: 100%" autocomplete="off">
                    <option value="">Seleccione un tipo de documento</option>
                    @foreach($typeDocuments as $typeDocument)
                        <option value="{{ $typeDocument->id }}" data-max-length="{{ $typeDocument->max_length }}">{{ $typeDocument->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Número Documento -->
            <div class="form-group col-lg-6">
                <label for="document_number">Número<span class="text-danger">*</span></label>
                <div class="input-group">
                    <input id="document_number" name="document_number" type="text" class="form-control @error('document_number') is-invalid @enderror" placeholder="Ingrese número de documento" value="{{ old('document_number', $customer->document_number ?? '') }}" required autocomplete="document-number">
                    <div class="input-group-append" id="toggleSearchContainer">
                        <button id="toggleSearch" type="button" class="btn btn-outline-secondary">
                            <i class="fas fa-search"></i>
                            <span id="textSearch">Buscar</span>
                        </button>
                    </div>
                </div>
                @error('document_number')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            
            <!-- Nombres completo -->
            <div class="form-group col-lg-6">
                <label for="full_name">Nombre completo<span class="text-danger">*</span></label>
                <input id="full_name" name="full_name" type="text" class="form-control @error('full_name') is-invalid @enderror" placeholder="Ingrese nombre del cliente" value="{{ old('full_name', $customer->full_name ?? '') }}" required> <!-- autocomplete="name" -->
                @error('full_name')  
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            
            <!-- Tipo de Cliente -->
            <div class="form-group col-lg-6">
                <label for="type_person">Tipo de cliente<span class="text-danger">*</span></label>
                <select id="type_person" name="type_person" class="form-control" style="width: 100%" autocomplete="off">
                    <option value="">Seleccione un tipo de cliente</option>
                    @foreach($typePeople as $TypePerson)
                        <option value="{{ $TypePerson->id }}" data-code="{{ strtolower($TypePerson->code) }}">{{ $TypePerson->name }}</option>
                    @endforeach
                </select>
                @error('type_person')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Sección de campos adicionales para contactos (solo si es contacto) -->
            <div id="contactFields" style="display: none;" class="form-group col-lg-12">
                <div class="row">
                    <!-- email  -->
                    <div class="form-group col-lg-6 email">
                        <label for="email">Correo Electrónico<span style="color: red">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Ingrese correo electrónico" value="{{ old('email', $user->email ?? '') }}">
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <!-- Fecha de Nacimiento -->
                    <div class="form-group col-lg-6">
                        <label for="birthdate">Fecha de Nacimiento</label>
                        <input type="date" id="birthdate" name="birthdate" class="form-control @error('birthdate') is-invalid @enderror" value="{{ old('birthdate', $user->birthdate ?? '') }}">
                        @error('birthdate')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <!-- Dirección -->
                    <div class="form-group col-lg-6 address">
                        <label for="address">Dirección</label>
                        <input type="text" id="address" name="address" class="form-control @error('address') is-invalid @enderror" placeholder="Ingrese la dirección" value="{{ old('address', $user->address ?? '') }}">
                        @error('address')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <!-- Teléfono (Máximo 9 dígitos, solo números) -->
                    <div class="form-group col-lg-6">
                        <label for="phone_number">Teléfono</label>
                        <input type="text" id="phone_number" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" placeholder="Ingrese el teléfono" value="{{ old('phone_number', $user->phone_number ?? '') }}" maxlength="9" pattern="[0-9]{9}">
                        @error('phone_number')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <!-- Token (Alfanumérico) -->
                    <div class="form-group col-lg-12">
                        <label for="token">Token</label>
                        <input type="text" id="token" name="token" class="form-control @error('token') is-invalid @enderror" placeholder="Ingrese el token" value="{{ old('token', $user->token ?? '') }}"> {{-- pattern="[A-Za-z0-9]+" --}}
                        @error('token')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
