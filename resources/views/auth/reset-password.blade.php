@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])
@section('title','Nueva contraseña')
@section('auth_header')<strong>Crea una nueva contraseña</strong>@stop
@section('auth_body')
<form method="POST" action="{{route('password.update')}}">@csrf
 <input type="hidden" name="token" value="{{$request->route('token')}}">
 <div class="form-group"><label>Correo de acceso</label><input type="email" name="username" value="{{old('username',$request->username ?? $request->email)}}" class="form-control @error('username') is-invalid @enderror" required>@error('username')<div class="invalid-feedback">{{$message}}</div>@enderror</div>
 <div class="form-group"><label>Nueva contraseña</label><input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>@error('password')<div class="invalid-feedback">{{$message}}</div>@enderror</div>
 <div class="form-group"><label>Confirmar contraseña</label><input type="password" name="password_confirmation" class="form-control" required></div>
 <button class="btn btn-primary btn-block rounded-pill">Actualizar contraseña</button>
</form>
@stop
