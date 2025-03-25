<!-- Modal de Asignación de Roles -->
<div class="modal fade" id="modal-assign-role" tabindex="-1" aria-labelledby="assignRoleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Asignar Rol a Usuarios</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="border rounded card-body border-secondary">
                    <p>Usuarios con el rol: <strong class="role-name"></strong></p>
                    <ul id="usersWithRoleList">
                        <!-- Lista de usuarios con el rol -->
                    </ul>
                </div>
                <br>
                <div class="border rounded card-body border-secondary">
                    <p>Selecciona los usuarios a asignar el rol: <strong class="role-name"></strong>
                        <i class="fas fa-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="right" title="Si dejas a un usuario sin ningún rol puede causar problemas en algunas vistas del sistema"></i>
                    </p>
                    <select id="usersWithoutRoleList" class="form-control" multiple="multiple" style="width: 100%">
                        <!-- Opciones se llenarán dinámicamente con select2 -->
                    </select>
                </div>
            </div>
            
          
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times-circle"></i> Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-shield"></i> Asignar Rol
                </button>
            </div>
        </div>
    </div>
</div>