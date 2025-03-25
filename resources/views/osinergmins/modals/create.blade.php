<!-- Modal de Creación de empresas -->
<div class="modal fade" id="modal-create" tabindex="-1" aria-labelledby="createCompanyLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title">AGREGAR NUEVA <span class="texttitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formCreateCompany" method="POST" autocomplete="off" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="createErrorMessages" class="alert alert-danger" style="display: none;"></div>                   
                    @include('companies.partials.form')
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