@extends('adminlte::page')
@section('title', 'Administración del sistema')
@section('content_header')
<h1><i class="fas fa-sliders-h mr-2"></i>Administración del sistema</h1>
@stop
@section('content')
<form method="POST" action="{{ route('system-settings.update') }}">
    @csrf @method('PUT')
    @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
    <div class="alert alert-warning"><i class="fas fa-lock mr-1"></i>Sección exclusiva del dueño. Los valores sensibles se guardan cifrados.</div>
    <div class="row">
        <div class="col-lg-5">
            <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title">Ambiente activo</h3></div><div class="card-body">
                <select name="osinergmin_environment" class="form-control">
                    <option value="development" @selected($values['osinergmin_environment']==='development')>Demo / certificación</option>
                    <option value="production" @selected($values['osinergmin_environment']==='production')>Producción</option>
                </select><small class="text-muted">El cron usará este ambiente en su siguiente ejecución.</small>
            </div></div>
            <div class="card card-outline card-info"><div class="card-header"><h3 class="card-title">Proveedor GPS OCSA</h3></div><div class="card-body">
                <label>URL base</label><input name="ocsa_base_url" value="{{ old('ocsa_base_url',$values['ocsa_base_url']) }}" class="form-control @error('ocsa_base_url') is-invalid @enderror">
                @error('ocsa_base_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div></div>
        </div>
        <div class="col-lg-7">
            @foreach(['development'=>'Demo / certificación','production'=>'Producción'] as $environment=>$label)
            <div class="card card-outline {{ $environment==='production' ? 'card-success':'card-warning' }}"><div class="card-header"><h3 class="card-title">Osinergmin — {{ $label }}</h3></div><div class="card-body">
                <div class="form-group"><label>URL base</label><input name="osinergmin_base_url_{{ $environment }}" value="{{ old('osinergmin_base_url_'.$environment,$values['osinergmin_base_url_'.$environment]) }}" class="form-control"></div>
                <div class="form-group mb-0"><label>Token</label><div class="input-group"><input type="password" name="osinergmin_token_{{ $environment }}" class="form-control secret-field" placeholder="Déjalo vacío para conservar el actual"><div class="input-group-append"><button class="btn btn-outline-secondary toggle-secret" type="button"><i class="fas fa-eye"></i></button></div></div><small class="text-muted">Token actual configurado: {{ filled($values['osinergmin_token_'.$environment]) ? 'sí':'no' }}</small></div>
            </div></div>
            @endforeach
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-envelope mr-2"></i>Correo saliente y alertas</h3></div>
        <div class="card-body">
            @if(session('mail_status')) <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>{{ session('mail_status') }}</div> @endif
            @error('mail_test') <div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}</div> @enderror
            <div class="row">
                <div class="col-lg-8"><div class="row">
                    <div class="form-group col-md-8"><label>Servidor SMTP</label><input name="mail_host" value="{{ old('mail_host',$values['mail_host']) }}" class="form-control @error('mail_host') is-invalid @enderror" placeholder="smtp.tuproveedor.com">@error('mail_host')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group col-md-4"><label>Puerto</label><input type="number" name="mail_port" value="{{ old('mail_port',$values['mail_port'] ?: 587) }}" class="form-control @error('mail_port') is-invalid @enderror">@error('mail_port')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group col-md-6"><label>Usuario SMTP</label><input name="mail_username" value="{{ old('mail_username',$values['mail_username']) }}" autocomplete="off" class="form-control" placeholder="avisos@tudominio.com"></div>
                    <div class="form-group col-md-6"><label>Contraseña SMTP</label><div class="input-group"><input type="password" name="mail_password" autocomplete="new-password" class="form-control secret-field" placeholder="{{ $values['mail_password_configured'] ? 'Vacío conserva la contraseña actual' : 'Contraseña o clave de aplicación' }}"><div class="input-group-append"><button class="btn btn-outline-secondary toggle-secret" type="button"><i class="fas fa-eye"></i></button></div></div><small class="text-muted">Contraseña guardada: {{ $values['mail_password_configured'] ? 'sí':'no' }}. Nunca se vuelve a mostrar.</small></div>
                    <div class="form-group col-md-4"><label>Cifrado</label><select name="mail_encryption" class="form-control"><option value="tls" @selected(old('mail_encryption',$values['mail_encryption'])==='tls')>TLS (587)</option><option value="ssl" @selected(old('mail_encryption',$values['mail_encryption'])==='ssl')>SSL (465)</option><option value="" @selected(blank(old('mail_encryption',$values['mail_encryption'])))>Sin cifrado</option></select></div>
                    <div class="form-group col-md-4"><label>Correo remitente</label><input type="email" name="mail_from_address" value="{{ old('mail_from_address',$values['mail_from_address']) }}" class="form-control @error('mail_from_address') is-invalid @enderror">@error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group col-md-4"><label>Nombre remitente</label><input name="mail_from_name" value="{{ old('mail_from_name',$values['mail_from_name']) }}" class="form-control @error('mail_from_name') is-invalid @enderror">@error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group col-12"><label>Destinatarios de alertas</label><textarea name="mail_alert_recipients" class="form-control @error('mail_alert_recipients') is-invalid @enderror" rows="2" placeholder="soporte@empresa.com, operaciones@empresa.com">{{ old('mail_alert_recipients',$values['mail_alert_recipients']) }}</textarea><small class="text-muted">Separa varios correos con coma, punto y coma o una línea nueva.</small>@error('mail_alert_recipients')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="input-group" style="max-width:620px"><input type="email" name="mail_test_recipient" value="{{ old('mail_test_recipient',auth()->user()->username) }}" class="form-control" placeholder="Correo que recibirá la prueba"><div class="input-group-append"><button id="test-mail-button" type="submit" class="btn btn-success" formaction="{{ route('system-settings.test-mail') }}" formmethod="POST"><i class="fas fa-paper-plane mr-1"></i>Probar correo</button></div></div>
                @error('mail_test_recipient')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-lg-4"><div class="callout callout-info mb-0">
                    <h5><i class="fas fa-info-circle mr-1"></i>¿De dónde salen estos datos?</h5>
                    <p><strong>Correo del hosting:</strong> cPanel → Cuentas de correo → Conectar dispositivos; copia la configuración SMTP saliente.</p>
                    <p><strong>Gmail:</strong> activa verificación en dos pasos y crea una contraseña de aplicación. Usa <code>smtp.gmail.com</code>, 587 y TLS.</p>
                    <p><strong>Microsoft 365:</strong> usa <code>smtp.office365.com</code>, 587 y TLS; el buzón debe permitir SMTP autenticado.</p>
                    <p class="mb-0">Prueba primero y luego guarda. Normalmente el remitente debe coincidir con el usuario SMTP.</p>
                </div></div>
            </div>
        </div>
    </div>
    <div class="text-right mb-4"><button class="btn btn-primary btn-lg"><i class="fas fa-save mr-1"></i>Guardar configuración</button></div>
</form>
@stop
@section('js')
<script>
$('.toggle-secret').on('click',function(){const i=$(this).closest('.input-group').find('input');i.attr('type',i.attr('type')==='password'?'text':'password');});
$('#test-mail-button').on('click',function(){$(this).closest('form').find('input[name="_method"]').val('POST');});
</script>
@stop
