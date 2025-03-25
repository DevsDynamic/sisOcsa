@extends('adminlte::page')

@section('title', 'Editar Rol')

@section('content_header')
    <div class="card-header"> 
        <div class="form-row">
            <h1>EDITAR ROL</h1> &nbsp;&nbsp;&nbsp;
            <button type="button" onclick="goBack()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> ATRÁS
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('roles.update', $role->id) }}" method="POST" autocomplete="off" enctype="multipart/form-data" id="formulario">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('roles.partials.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Actualizar</button>
                <a href="{{ route('roles.index') }}" class="btn btn-danger"><i class="fas fa-times-circle"></i> Cancelar</a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/roles/create-edit.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/roles/create-edit.js') }}"></script>
@stop