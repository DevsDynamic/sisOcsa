<!-- Modal -->
<div class="modal fade" id="modal-assign-role" tabindex="-1" aria-labelledby="assignRoleLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="assignRoleLabel">ASIGNAR ROL A <span class="textcode"></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="formAssignRole" name="formAssignRole" method="POST">
        @csrf
        <input type="hidden" id="hiddenIDassignRole" name="user_id" class="form-control">
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group col-lg-12">
                    <label>Nombres y Apellidos:</label>
                    <input id="name" type="text" name="name" class="form-control textname" disabled="">

                    <label>Rol actual:</label>
                    <input id="currentrole" type="text" name="currentrole" class="form-control textcurrentrole" disabled="">
                    <br>
                    <div class="border rounded card-body border-secondary">
                      <label for="roleList">Lista de roles disponibles:</label>
                      <div class="card-body">
                        
                        <div id="roleList">
                            <!-- Los roles se cargarán aquí -->
                        </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                <i class="fas fa-times-circle"></i> Cerrar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Confirmar
            </button>
        </div>
      </form>       
    </div>
  </div>
</div>