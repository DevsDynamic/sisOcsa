<!-- Modal para cambiar el acceso al sistema -->
<div class="modal fade" id="modal-access-system" tabindex="-1" role="dialog" aria-labelledby="modalAccessSystemLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title texttitle" id="modalAccessSystemLabel"><span class="texttitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea <strong><span class="textaccess"></span></strong> al <span class="entity"></span> con código <strong><span class="textcode"></span></strong>?
            </div>
            <div class="modal-footer">
                <form id="formAccessSystem" method="POST">
                    @csrf
                    <input type="hidden" id="hiddenIDaccessSystem" name="user_id">
                    <input type="hidden" id="hiddenAccessSystem" name="access_action">
                    <button type="submit" class="btn btn-primary btn-confirm-access">
                        <i class="icon-access"></i> Confirmar
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>