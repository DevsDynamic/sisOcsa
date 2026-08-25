@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])
@section('title','Recuperar contraseña')
@section('auth_header')<strong>Recupera tu acceso</strong><small class="d-block text-muted mt-1">Te enviaremos un enlace seguro</small>@stop
@section('auth_body')
@if(session('status'))<div class="alert alert-success">{{session('status')}}</div>@endif
<form method="POST" action="{{route('password.email')}}">@csrf
 <div class="form-group"><label for="username">Correo de acceso</label><div class="input-group"><input id="username" type="email" name="username" value="{{old('username')}}" class="form-control @error('username') is-invalid @enderror" required autofocus><div class="input-group-append"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>@error('username')<div class="invalid-feedback">{{$message}}</div>@enderror</div></div>
 <button class="btn btn-primary btn-block rounded-pill">Enviar enlace de recuperación</button>
</form>
@stop
@section('auth_footer')<a href="{{route('login')}}"><i class="fas fa-arrow-left mr-1"></i>Volver al inicio de sesión</a>@stop
