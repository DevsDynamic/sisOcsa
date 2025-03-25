@extends('adminlte::page')

@section('title', 'Agregar Usuario')

@section('content_header')
    <div class="card" style="padding-bottom: 0px;margin-bottom: 0px;"> 
        <div class="card-header">
            <div class="row">
                <button type="button" onClick="history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> ATRÁS
                </button>&nbsp;&nbsp;&nbsp;
                <h1><b>AGREGAR USUARIO</b></h1>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('users.store') }}" method="POST" autocomplete="off" enctype="multipart/form-data" id="formulario">
            @csrf
            <!-- Campo oculto para el tipo -->
            <input type="hidden" name="type" value="{{ request()->query('type') }}">

            <div class="card-body">
                <input type="hidden" name="entity" value="user">
                @include('users.partials.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button type="button" onClick="history.back()" class="btn btn-danger">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
            </div>
        </form>
    </div>
@stop

@section('css')
    
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/users/create-edit.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('#formulario').submit(function (e) {
                e.preventDefault(); // Evita el envío tradicional del formulario

                var form = $(this);
                var formData = new FormData(this);
                var actionUrl = form.attr('action');

                $.ajax({
                    url: actionUrl,
                    type: form.attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        clearFormErrors('#formulario');
                    },
                    success: function (response) {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: response.message, // Mensaje dinámico
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = response.redirect_url; // Redirige al listado de usuarios
                        });
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            showFormErrors('#formulario', errors);
                        } else {
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error inesperado',
                                text: 'Ocurrió un error inesperado. Inténtalo nuevamente.',
                                showConfirmButton: true
                            });
                        }
                    }
                });
            });
        });
    </script>
@stop