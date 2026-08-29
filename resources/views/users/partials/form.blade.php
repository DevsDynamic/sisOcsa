<div class="row form-spaced user-editor">
    <div class="col-lg-4">
        <div class="form-section text-center h-100">
            <h6><i class="fas fa-camera mr-2"></i>Foto de perfil</h6><label for="photo" class="user-photo-picker"><img
                    id="picture_photo"
                    src="{{ isset($user) && $user->profile_photo_path ? asset('storage/users/photo/' . $user->profile_photo_path) : asset('image/user_preview.png') }}"
                    alt="Foto de perfil"><span><i class="fas fa-camera"></i></span></label><input id="photo"
                type="file" name="photo" class="form-control-file mt-3 @error('photo') is-invalid @enderror"
                accept="image/jpeg,image/png,image/webp"><small class="text-muted d-block mt-2">Opcional. Máximo 2
                MB.</small>
            @error('photo')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="col-lg-8">
        <div class="form-section h-100">
            <h6><i class="fas fa-shield-alt mr-2"></i>Credenciales y acceso</h6>
            <div class="form-group"><label for="email">Correo de acceso <span
                        class="text-danger">*</span></label><input id="email" type="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->username ?? '') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="row form-spaced">
                <div class="form-group col-md-6"><label for="password">Contraseña <span
                            class="text-danger">*</span></label>
                    <div class="input-group"><input id="password" type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror">
                        <div class="input-group-append"><button id="togglePassword" type="button"
                                class="btn btn-outline-secondary"><i id="toggleIcon" class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6"><label for="password_confirmation">Confirmar contraseña <span
                            class="text-danger">*</span></label>
                    <div class="input-group"><input id="password_confirmation" type="password"
                            name="password_confirmation" class="form-control">
                        <div class="input-group-append"><button id="toggleConfirmPassword" type="button"
                                class="btn btn-outline-secondary"><i id="toggleConfirmPasswordIcon"
                                    class="fas fa-eye"></i></button></div>
                    </div>
                </div>
            </div>
            @error('password')
                <small class="text-danger">{{ $message }}</small>
            @enderror
            <div class="callout callout-info mt-3 mb-0"><small>El usuario recibirá el rol correspondiente al listado
                    desde el que fue creado. Después puedes ajustar sus permisos desde Roles y permisos.</small></div>
        </div>
    </div>
</div>
<style>
    .user-photo-picker {
        position: relative;
        display: inline-block;
        cursor: pointer
    }

    .user-photo-picker img {
        width: 170px;
        height: 170px;
        object-fit: cover;
        border-radius: 22px;
        border: 1px solid #dbe2ea
    }

    .user-photo-picker span {
        position: absolute;
        right: -8px;
        bottom: -8px;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #1769aa;
        color: #fff;
        display: grid;
        place-items: center;
        border: 3px solid #fff
    }

    .user-editor .form-control {
        min-height: 42px
    }
</style>
