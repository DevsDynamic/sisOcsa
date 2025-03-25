// Modal ver
$(document).on('click', '.ver-rol', function () {
    let roleId = $(this).data('id'); // ID del rol
    let modal = $('#modal-show'); // Definir el modal correctamente
    let entity = "rol"; // Tipo de entidad

    console.log('open modal show permissions for role');

    var formattedId = 'ROL' + ('00000' + roleId).slice(-5);
    modal.find('.texttitle').text((entity).toUpperCase()); // Título del modal
    modal.find('.entity').text(entity); 
    modal.find('.textcode').text(formattedId);

    $.ajax({
        url: '/roles/' + roleId,
        method: 'GET',
        success: function (response) {
            console.log(response); // Verifica la estructura JSON en la consola

            // Actualizar el título del rol y código
            modal.find('.texttitle').text('ROL ' + response.role.toUpperCase());
            modal.find('.textcode').text('ROL' + ('00000' + roleId).slice(-5));

            let permissionsHTML = '';

            if (response.permissions && Object.keys(response.permissions).length > 0) {
                Object.keys(response.permissions).forEach(moduleKey => {
                    const subModules = response.permissions[moduleKey];

                    // Obtener la descripción del módulo
                    const moduleData = subModules[""] ? subModules[""][0] : null;
                    let moduleTitle = moduleData ? moduleData.description : moduleKey;
                    let modulePermission = moduleData ? moduleData.description : '';
                    
                    // Contar submódulos (excluyendo la clave "")
                    const subModuleKeys = Object.keys(subModules).filter(key => key !== "");
                    
                    permissionsHTML += `<div class="card border-secondary mb-3">
                        <div class="card-header bg-info text-white">
                            <strong>${moduleTitle}</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">`;

                    // Si hay submódulos, los organizamos en columnas
                    if (subModuleKeys.length > 0) {
                        subModuleKeys.forEach(subModuleKey => {
                            let subModulePermissions = subModules[subModuleKey];
                            let subModuleTitle = subModulePermissions.find(p => p.name.includes(".submodule")) 
                                ? subModulePermissions.find(p => p.name.includes(".submodule")).description 
                                : '';
                            
                            permissionsHTML += `<div class="col-lg-4 col-md-6 col-12">
                                <div class="card border-light shadow-sm mb-3">
                                    ${subModuleTitle ? `<div class="card-header bg-secondary text-white"><strong>${subModuleTitle}</strong></div>` : ''}
                                    <div class="card-body">`;
                            
                            subModulePermissions.forEach(permission => {
                                // Filtramos los permisos que contienen ".submodule" en el nombre
                                if (!permission.name.includes(".submodule")) {
                                    permissionsHTML += `<div class="mb-2">
                                        <label class="d-block border rounded p-2">
                                            <input type="checkbox" checked disabled>
                                            ${permission.description}
                                        </label>
                                    </div>`;
                                }
                            });
                            
                            permissionsHTML += `</div></div></div>`;
                        });
                    } else {
                        // Si no hay submódulos, los permisos se agrupan en columnas directamente
                        let permissionsList = subModules[""] || [];
                        
                        permissionsHTML += `<div class="col-lg-4 col-md-6 col-12">`;
                        permissionsList.forEach(permission => {
                            if (permission.description !== modulePermission) {
                                permissionsHTML += `<div class="mb-2">
                                    <label class="d-block border rounded p-2">
                                        <input type="checkbox" checked disabled>
                                        ${permission.description}
                                    </label>
                                </div>`;
                            }
                        });
                        permissionsHTML += `</div>`;
                    }

                    permissionsHTML += `</div></div></div>`;
                });
            } else {
                permissionsHTML = '<p class="text-center text-danger"><strong>No hay permisos asignados a este rol.</strong></p>';
            }

            // Insertar permisos en el modal
            modal.find('#permissions-list').html(permissionsHTML);

            // Mostrar el modal
            $('#modal-show').modal('show');
        },
        error: function () {
            alert('Error al obtener los permisos del rol.');
        }
    });
});

// Tooltip
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});