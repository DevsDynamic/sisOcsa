<!-- Modal para cambiar el estado del usuario -->
<div class="modal fade" id="modal-change-status" tabindex="-1" role="dialog" aria-labelledby="modalChangeStatusLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title texttitle" id="modalChangeStatusLabel"><span class="texttitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea <strong><span class="textstatus"></span></strong> este <span class="entity"></span> con código <strong><span class="textcode"></span></strong>?
            </div>
            <div class="modal-footer">
                <form id="formChangeStatus" method="POST">
                    @csrf
                    <input type="hidden" id="hiddenIDchangeStatus" name="type_customer_id">
                    <input type="hidden" id="hiddenStatusAction" name="status_action">
                    <button type="submit" class="btn btn-primary btn-confirm-status">
                        <i class="icon-status"></i> Confirmar
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>