{{-- SECCION DE EMPRESA --}}
<div class="border rounded card-body border-secondary">
    <div class="card-body">
        <div class="form-row">
            <!-- Campos ocultos -->
            <div class="form-group col-lg-12">
                <!-- Tipo Documento -->
                <input id="type_document" name="type_document" type="hidden" class="form-control" value="{{ $typeDocuments->id }}">
                <!-- Nombre de Tipo Documento -->
                <input id="type_document_name" name="type_document_name" type="hidden" class="form-control" value="{{ $typeDocuments->name }}">
                <!-- Longitud de caracteres de Tipo Documento -->
                <input id="type_document_max_length" name="type_document_max_length" type="hidden" class="form-control" value="{{ $typeDocuments->max_length }}">
            </div>
            <!-- Número Documento -->
            <div class="form-group col-lg-6">
                <label for="document_number">Número de <span class="typeDocumentText"></span><span class="text-danger">*</span></label>
                <div class="input-group">
                    <input id="document_number" name="document_number" type="text" class="form-control @error('document_number') is-invalid @enderror" placeholder="Ingrese número de documento" value="{{ old('document_number', $company->document_number ?? '') }}" required>
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
            
            <!-- Razon social -->
            <div class="form-group col-lg-6">
                <label for="business_name">Razón social<span class="text-danger">*</span></label>
                <input id="business_name" name="business_name" type="text" class="form-control @error('business_name') is-invalid @enderror" placeholder="Ingrese nombre del cliente" value="{{ old('business_name', $company->business_name ?? '') }}" required>
                @error('business_name')  
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            
            <!-- Dirección -->
            <div class="form-group col-lg-12">
                <label for="address">Dirección</label>
                <input id="address" name="address" type="text" class="form-control @error('address') is-invalid @enderror" placeholder="Ingrese nombre del cliente" value="{{ old('address', $company->address ?? '') }}" required>
                @error('address')  
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            
            <!-- Organizacion de ventas -->
            <div class="form-group col-lg-6">
                <label for="sales_organization">Organización de ventas</label>
                <input id="sales_organization" name="sales_organization" type="text" class="form-control @error('sales_organization') is-invalid @enderror" placeholder="Ingrese nombre del cliente" value="{{ old('sales_organization', $company->sales_organization ?? '') }}" required>
                @error('sales_organization')  
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Nombre CET -->
            <div class="form-group col-lg-6">
                <label for="cet_name">Nombre CET</label>
                <input id="cet_name" name="cet_name" type="text" class="form-control @error('cet_name') is-invalid @enderror" placeholder="Ingrese nombre del cliente" value="{{ old('cet_name', $company->cet_name ?? '') }}" required>
                @error('cet_name')  
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Oficina de ventas -->
            <div class="form-group col-lg-6">
                <label for="sales_office">Oficina de ventas</label>
                <input id="sales_office" name="sales_office" type="text" class="form-control @error('sales_office') is-invalid @enderror" placeholder="Ingrese nombre del cliente" value="{{ old('sales_office', $company->sales_office ?? '') }}" required>
                @error('sales_office')  
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>
</div>