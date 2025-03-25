{{-- SECCION DE TIPO DE CLIENTE --}}
<div class="border rounded card-body border-secondary">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-12">
                <label for="name">Nombre<span style="color: red">*</span></label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Ingrese nombre del tipo de cliente" value="{{ old('name', $type_person->name ?? '') }}" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group col-lg-6">
                <label for="code">Código<span style="color: red">*</span></label>
                <input id="code" type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="Ingrese código del tipo de cliente" value="{{ old('code', $type_person->code ?? '') }}" required>
                @error('code')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group col-lg-6">
                <label for="description">Descripción</label>
                <input id="description" type="text" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Ingrese la descripción del tipo de cliente" value="{{ old('description', $type_person->description ?? '') }}">
                @error('description')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>
</div>