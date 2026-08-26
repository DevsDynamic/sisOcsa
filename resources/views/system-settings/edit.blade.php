@extends('adminlte::page')
@section('title', 'Configuración del sistema')

@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <div><h1 class="mb-1"><i class="fas fa-sliders-h mr-2"></i>Configuración del sistema</h1><p class="text-muted mb-0">Integraciones, ambientes y canales de alerta en un solo lugar.</p></div>
    <span class="badge badge-{{ auth()->user()->is_system_owner ? 'dark' : 'primary' }} p-2"><i class="fas fa-user-shield mr-1"></i>{{ auth()->user()->is_system_owner ? 'Dueño del sistema' : 'Administrador' }}</span>
</div>
@stop

@section('content')
@php
    $requestedSection = request('section', session('active_settings_section', old('_section')));
    $activeSection = $requestedSection ?: (auth()->user()->can('system.integrations.manage') ? 'integrations' : 'notifications');
@endphp

@if(session('status'))<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>{{ session('status') }}</div>@endif
<div class="alert alert-light border d-flex align-items-center"><i class="fas fa-lock text-primary fa-lg mr-3"></i><div><strong>Acceso controlado por permisos.</strong><br><small class="text-muted">Las contraseñas y tokens se guardan cifrados y nunca vuelven a mostrarse.</small></div></div>

<div class="card settings-shell">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs settings-tabs" role="tablist">
            @can('system.integrations.manage')
            <li class="nav-item"><a class="nav-link {{ $activeSection === 'integrations' ? 'active' : '' }}" data-toggle="tab" href="#integrations"><i class="fas fa-broadcast-tower mr-2"></i>Retransmisiones</a></li>
            @endcan
            @can('system.notifications.manage')
            <li class="nav-item"><a class="nav-link {{ $activeSection === 'notifications' ? 'active' : '' }}" data-toggle="tab" href="#notifications"><i class="fas fa-bell mr-2"></i>Notificaciones</a></li>
            @endcan
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#access"><i class="fas fa-shield-alt mr-2"></i>Permisos</a></li>
        </ul>
    </div>
    <div class="card-body tab-content">
        @can('system.integrations.manage')
        <div id="integrations" class="tab-pane fade {{ $activeSection === 'integrations' ? 'show active' : '' }}">
            <form method="POST" action="{{ route('system-settings.update') }}">@csrf @method('PUT')<input type="hidden" name="_section" value="integrations">
                <div class="section-heading"><div class="section-icon bg-primary"><i class="fas fa-route"></i></div><div><h3>Configuración de retransmisiones</h3><p>Ambiente y credenciales técnicas. Sección exclusiva del dueño.</p></div></div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title">Control general</h3></div><div class="card-body">
                            <div class="form-group"><label>Ambiente activo</label><select name="osinergmin_environment" class="form-control"><option value="development" @selected(old('osinergmin_environment',$values['osinergmin_environment'])==='development')>Demo / certificación</option><option value="production" @selected(old('osinergmin_environment',$values['osinergmin_environment'])==='production')>Producción</option></select><small class="text-muted">El cron lo usará en la próxima ejecución.</small></div>
                            <div class="form-group mb-0"><label>Proveedor GPS OCSA — URL base</label><input name="ocsa_base_url" value="{{ old('ocsa_base_url',$values['ocsa_base_url']) }}" class="form-control @error('ocsa_base_url') is-invalid @enderror">@error('ocsa_base_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        </div></div>
                        @foreach(['atu'=>'ATU','ositran'=>'OSITRAN'] as $code=>$name)
                        <div class="integration-coming"><span class="integration-logo">{{ $name }}</span><div><strong>{{ $name }}</strong><small>Preparado para una futura integración</small></div><span class="badge badge-light">Próximamente</span></div>
                        @endforeach
                    </div>
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-3"><span class="entity-mark">O</span><div><h4 class="mb-0">OSINERGMIN</h4><small class="text-muted">Endpoints individual y por lote</small></div></div>
                        @foreach(['development'=>'Demo / certificación','production'=>'Producción'] as $environment=>$label)
                        <div class="card card-outline {{ $environment==='production' ? 'card-success':'card-warning' }}"><div class="card-header"><h3 class="card-title">{{ $label }}</h3></div><div class="card-body">
                            <div class="form-group"><label>URL base <small class="text-muted">(sin /api/...)</small></label><input name="osinergmin_base_url_{{ $environment }}" value="{{ old('osinergmin_base_url_'.$environment,$values['osinergmin_base_url_'.$environment]) }}" class="form-control osinergmin-base-url"><small class="endpoint-preview"><b>Individual:</b> <code class="endpoint-unit"></code><br><b>Lote:</b> <code class="endpoint-batch"></code></small></div>
                            <div class="form-group mb-0"><label>Token</label><div class="input-group"><input type="password" name="osinergmin_token_{{ $environment }}" class="form-control secret-field" placeholder="Vacío conserva el token actual"><div class="input-group-append"><button class="btn btn-outline-secondary toggle-secret" type="button"><i class="fas fa-eye"></i></button></div></div><small class="text-muted">Token configurado: {{ filled($values['osinergmin_token_'.$environment]) ? 'sí':'no' }}</small></div>
                        </div></div>
                        @endforeach
                    </div>
                </div>
                <div class="text-right"><button class="btn btn-primary btn-lg"><i class="fas fa-save mr-1"></i>Guardar retransmisiones</button></div>
            </form>
        </div>
        @endcan

        @can('system.notifications.manage')
        <div id="notifications" class="tab-pane fade {{ $activeSection === 'notifications' ? 'show active' : '' }}">
            <div class="section-heading"><div class="section-icon bg-success"><i class="fas fa-bell"></i></div><div><h3>Canales de notificación</h3><p>Correo como respaldo y Telegram para avisos inmediatos.</p></div></div>
            <div class="row">
                <div class="col-xl-7">
                    <form method="POST" action="{{ route('system-settings.update-mail') }}" class="card card-outline card-info">@csrf @method('PUT')<input type="hidden" name="_section" value="notifications">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-envelope mr-2"></i>Correo saliente</h3><span class="card-tools badge badge-light">Respaldo y detalle</span></div>
                        <div class="card-body">
                            @if(session('mail_status'))<div class="alert alert-success">{{ session('mail_status') }}</div>@endif @error('mail_test')<div class="alert alert-danger">{{ $message }}</div>@enderror
                            <div class="row">
                                <div class="form-group col-md-8"><label>Servidor SMTP</label><input name="mail_host" value="{{ old('mail_host',$values['mail_host']) }}" class="form-control @error('mail_host') is-invalid @enderror">@error('mail_host')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="form-group col-md-4"><label>Puerto</label><input type="number" name="mail_port" value="{{ old('mail_port',$values['mail_port'] ?: 587) }}" class="form-control"></div>
                                <div class="form-group col-md-6"><label>Usuario SMTP</label><input name="mail_username" value="{{ old('mail_username',$values['mail_username']) }}" class="form-control" autocomplete="off"></div>
                                <div class="form-group col-md-6"><label>Contraseña SMTP</label><div class="input-group"><input type="password" name="mail_password" class="form-control secret-field" autocomplete="new-password" placeholder="Vacío conserva la actual"><div class="input-group-append"><button class="btn btn-outline-secondary toggle-secret" type="button"><i class="fas fa-eye"></i></button></div></div><small class="text-muted">Contraseña guardada: {{ $values['mail_password_configured'] ? 'sí':'no' }}</small></div>
                                <div class="form-group col-md-4"><label>Cifrado</label><select name="mail_encryption" class="form-control"><option value="tls" @selected(old('mail_encryption',$values['mail_encryption'])==='tls')>TLS (587)</option><option value="ssl" @selected(old('mail_encryption',$values['mail_encryption'])==='ssl')>SSL (465)</option><option value="">Sin cifrado</option></select></div>
                                <div class="form-group col-md-4"><label>Correo remitente</label><input type="email" name="mail_from_address" value="{{ old('mail_from_address',$values['mail_from_address']) }}" class="form-control"></div>
                                <div class="form-group col-md-4"><label>Nombre remitente</label><input name="mail_from_name" value="{{ old('mail_from_name',$values['mail_from_name']) }}" class="form-control"></div>
                                <div class="form-group col-12"><label>Destinatarios de alertas</label><textarea name="mail_alert_recipients" class="form-control" rows="2">{{ old('mail_alert_recipients',$values['mail_alert_recipients']) }}</textarea></div>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-end"><div class="form-group mb-0 flex-grow-1 mr-2"><label>Enviar prueba a</label><input type="email" name="mail_test_recipient" value="{{ old('mail_test_recipient',auth()->user()->username) }}" class="form-control"></div><button type="submit" class="btn btn-outline-info mr-2" formaction="{{ route('system-settings.test-mail') }}" formmethod="POST" onclick="this.form.querySelector('[name=_method]').value='POST'"><i class="fas fa-paper-plane mr-1"></i>Probar</button><button class="btn btn-info"><i class="fas fa-save mr-1"></i>Guardar</button></div>
                        </div>
                    </form>
                </div>
                <div class="col-xl-5">
                    <form method="POST" action="{{ route('system-settings.update-telegram') }}" class="card card-outline card-primary">@csrf @method('PUT')<input type="hidden" name="_section" value="notifications">
                        <div class="card-header"><h3 class="card-title"><i class="fab fa-telegram-plane mr-2"></i>Telegram</h3><div class="card-tools custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="telegram_enabled" name="telegram_enabled" value="1" @checked(old('telegram_enabled',$values['telegram_enabled']))><label class="custom-control-label" for="telegram_enabled">Activo</label></div></div>
                        <div class="card-body">
                            @if(session('telegram_status'))<div class="alert alert-success">{{ session('telegram_status') }}</div>@endif @error('telegram_test')<div class="alert alert-danger">{{ $message }}</div>@enderror
                            <div class="form-group"><label>Token del bot</label><div class="input-group"><input type="password" name="telegram_bot_token" class="form-control secret-field" placeholder="Vacío conserva el token actual"><div class="input-group-append"><button class="btn btn-outline-secondary toggle-secret" type="button"><i class="fas fa-eye"></i></button></div></div><small class="text-muted">Token guardado: {{ $values['telegram_bot_token_configured'] ? 'sí':'no' }}</small></div>
                            <div class="form-group"><label>Chat ID de destinatarios</label><textarea name="telegram_chat_ids" rows="3" class="form-control @error('telegram_chat_ids') is-invalid @enderror" placeholder="123456789, -1001234567890">{{ old('telegram_chat_ids',$values['telegram_chat_ids']) }}</textarea><small class="text-muted">Persona o grupo. Separa varios con coma o una línea nueva.</small>@error('telegram_chat_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="telegram-help"><b>Configuración rápida</b><ol><li>Abre <code>@BotFather</code> y ejecuta <code>/newbot</code>.</li><li>Copia el token y escríbele al bot.</li><li>Abre <code>api.telegram.org/botTOKEN/getUpdates</code> y copia <code>chat.id</code>.</li></ol></div>
                            <div class="text-right"><button type="submit" class="btn btn-outline-primary mr-2" formaction="{{ route('system-settings.test-telegram') }}" formmethod="POST" onclick="this.form.querySelector('[name=_method]').value='POST'"><i class="fas fa-paper-plane mr-1"></i>Probar</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Guardar</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        <div id="access" class="tab-pane fade"><div class="section-heading"><div class="section-icon bg-dark"><i class="fas fa-key"></i></div><div><h3>Distribución de responsabilidades</h3><p>Los accesos se asignan desde Roles y permisos.</p></div></div><div class="row"><div class="col-md-6"><div class="access-card owner"><i class="fas fa-crown"></i><div><h5>Dueño del sistema</h5><p>Ambientes, endpoints, tokens de entidades, OCSA, correo, Telegram y acceso total.</p></div></div></div><div class="col-md-6"><div class="access-card admin"><i class="fas fa-user-cog"></i><div><h5>Administrador</h5><p>Correo y Telegram cuando tenga el permiso “Configurar correo y Telegram”. No puede cambiar las retransmisiones.</p></div></div></div></div></div>
    </div>
</div>
@stop

@section('css')
<style>
.settings-shell{border:0;box-shadow:0 8px 28px rgba(30,55,90,.08)}.settings-tabs .nav-link{padding:1rem 1.35rem;font-weight:700;color:#687386}.settings-tabs .nav-link.active{color:#1478e8;border-top:3px solid #1478e8}.section-heading{display:flex;align-items:center;margin-bottom:1.5rem}.section-heading h3{font-size:1.35rem;margin:0}.section-heading p{color:#758094;margin:2px 0 0}.section-icon{width:48px;height:48px;border-radius:14px;color:#fff;display:flex;align-items:center;justify-content:center;margin-right:14px}.endpoint-preview{display:block;background:#f5f7fa;padding:9px 12px;border-radius:7px;margin-top:8px;overflow-wrap:anywhere}.integration-coming{display:flex;align-items:center;gap:10px;padding:12px;margin-bottom:10px;border:1px dashed #ccd3dc;border-radius:10px;background:#fafbfc}.integration-coming small{display:block;color:#87909d}.integration-coming .badge{margin-left:auto}.integration-logo,.entity-mark{width:36px;height:36px;border-radius:10px;background:#e8f2ff;color:#1478e8;display:flex;align-items:center;justify-content:center;font-weight:800}.entity-mark{margin-right:10px}.telegram-help{background:#eef7ff;border-left:4px solid #229ed9;padding:12px 15px;border-radius:6px;margin-bottom:1rem}.telegram-help ol{padding-left:18px;margin:7px 0 0}.access-card{display:flex;gap:15px;padding:20px;border-radius:12px;height:100%;border:1px solid #e2e6ea}.access-card>i{font-size:28px}.access-card p{margin:0;color:#687386}.access-card.owner>i{color:#d69e00}.access-card.admin>i{color:#1478e8}@media(max-width:767px){.settings-tabs .nav-link{padding:.8rem;font-size:.9rem}.card-body{padding:1rem}}
</style>
@stop

@section('js')
<script>
$('.toggle-secret').on('click',function(){const input=$(this).closest('.input-group').find('input');input.attr('type',input.attr('type')==='password'?'text':'password');$(this).find('i').toggleClass('fa-eye fa-eye-slash');});
function cleanOsinergminBase(value){return value.trim().replace(/\/$/,'').replace(/\/api-gps-ingesta(?:-batch)?(?:\/api\/v1\/(?:trama|trama-batch))?\/?$/i,'');}
$('.osinergmin-base-url').on('input',function(){const card=$(this).closest('.card');const base=cleanOsinergminBase($(this).val());card.find('.endpoint-unit').text(base+'/api-gps-ingesta/api/v1/trama');card.find('.endpoint-batch').text(base+'/api-gps-ingesta-batch/api/v1/trama-batch');}).trigger('input');
</script>
@stop
