@extends('adminlte::page')
@section('title','Acceso restringido')
@section('content_header')<h1 class="h3 mb-0"><i class="fas fa-shield-alt text-warning mr-2"></i>Acceso restringido</h1>@stop
@section('content')
<div class="card access-denied-card"><div class="card-body text-center py-5"><div class="access-denied-icon"><i class="fas fa-lock"></i></div><h2 class="h4 mt-4">No tienes permiso para acceder a esta función</h2><p class="text-muted mx-auto">{{ $exception->getMessage() ?: 'Solicita al administrador que revise los permisos asignados a tu rol.' }}</p><div class="mt-4"><button type="button" class="btn btn-outline-secondary mr-2" onclick="history.length>1?history.back():location.href='{{ route('dashboard.index') }}'"><i class="fas fa-arrow-left mr-1"></i>Volver</button><a href="{{route('dashboard.index')}}" class="btn btn-primary"><i class="fas fa-home mr-1"></i>Ir al dashboard</a></div></div></div>
@stop
@section('css')<style>.access-denied-card{max-width:760px;margin:30px auto;border:0;border-radius:16px;box-shadow:0 8px 28px rgba(30,55,90,.09)}.access-denied-card p{max-width:560px}.access-denied-icon{display:grid;place-items:center;width:78px;height:78px;margin:auto;border-radius:50%;background:#fff2cc;color:#946800;font-size:30px}</style>@stop
