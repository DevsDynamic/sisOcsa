{{-- SECCION DE USUARIO Y CONTRASEÑA --}}
<div class="border rounded card-body border-secondary" id="vertabla">
    <div class="card-body">
        <div class="form-row">
            <!-- photo -->
            <div class="form-group col-lg-6">
                <div class="card text-center" style="height: 370px">
                    <div class="card-header">
                        <label for="foto">Foto de Perfil</label><br>
                    </div>
                    <div class="card-body">
                        <div class="mt-2 text-center">
                            <img 
                                class="border border-secondary" 
                                id="picture_photo" 
                                src="{{ isset($user) && $user->profile_photo_path ? asset('storage/users/photo/' . $user->profile_photo_path) : asset('image/user_preview.png') }}" 
                                alt="Imagen de perfil" 
                                height="200px" 
                                width="200px">
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <input id="photo" type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                        @error('photo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form-group col-lg-6">
                <!-- email -->
                <label for="email">Correo Electrónico*</label>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Ingrese correo electrónico válido" value="{{ old('email', $user->email ?? '') }}" required>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                <br>
                <!-- password -->
                <label for="password">Contraseña</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Ingrese contraseña">
                    <div class="input-group-append">
                        <button id="togglePassword" type="button" class="btn btn-outline-secondary">
                            <i id="toggleIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                <br>
                <!-- password confirmation -->
                <label for="password_confirmation">Confirmar contraseña</label>
                <div class="input-group">
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Confirme contraseña">
                    <div class="input-group-append">
                        <button id="toggleConfirmPassword" type="button" class="btn btn-outline-secondary">
                            <i id="toggleConfirmPasswordIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
                @error('password_confirmation')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>
</div>