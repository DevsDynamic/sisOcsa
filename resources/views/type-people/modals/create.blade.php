<!-- Modal de Creación de tipo de clientes -->
<div class="modal fade" id="modal-create" tabindex="-1" aria-labelledby="createTypeCustomerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title">AGREGAR NUEVO <span class="texttitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formCreateTypeCustomer" method="POST" autocomplete="off" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="createErrorMessages" class="alert alert-danger" style="display: none;"></div>                   
                    @include('type-people.partials.form')
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>