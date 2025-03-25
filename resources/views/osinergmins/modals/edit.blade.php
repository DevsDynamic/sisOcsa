<!-- Modal de Edición de clientes -->
<div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="editCompanyLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">EDITAR <span class="texttitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formEditCompany" method="POST" autocomplete="off" enctype="multipart/form-data">
                @csrf      
                @method('PUT') <!-- Método para editar -->      
                <div class="modal-body">
                    ¿Está seguro que desea <strong>editar</strong> al <span class="entity"></span> con código <strong><span class="textcode"></span></strong>?
                    <input type="hidden" id="hiddenIDCompany" name="user_id" class="form-control">
                    <br><br>
                    <div id="createErrorMessages" class="alert alert-danger" style="display: none;"></div> 
                    @include('companies.partials.form')
                </div>
            
                <div class="modal-footer">                
                    <button type="submit" class="btn btn-warning">
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