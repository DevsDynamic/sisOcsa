@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <div class="card-header"> 
        <div class="form-row">
            <h1>EDITAR USUARIO</h1> &nbsp;&nbsp;&nbsp;
            <button type="button" onclick="goBack()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> ATRÁS
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('users.update', $user->id) }}" method="POST" autocomplete="off" enctype="multipart/form-data" id="formulario">
            @csrf
            @method('PUT')
            <!-- Campo oculto para el tipo -->
            <input type="hidden" name="type" value="{{ request()->query('type') }}">

            <div class="card-body">
                <input type="hidden" name="entity" value="user">
                @include('users.partials.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Actualizar</button>
                <a href="{{ route('users.index') }}" class="btn btn-danger"><i class="fas fa-times-circle"></i> Cancelar</a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .input-group-append .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
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
                            title: response.message, 
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = response.redirect_url; 
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