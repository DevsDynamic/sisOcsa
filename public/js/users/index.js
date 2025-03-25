document.addEventListener("DOMContentLoaded", function () {
    // Modal ver usuario
    $(document).on('click', '.ver-usuario', function () {
        let userId = $(this).data('id');
        let modal = $('#modal-show'); // Definir el modal correctamente
        let entity = "usuario";

        console.log('open modal show user');

        var formattedId = 'USER' + ('00000' + userId).slice(-5);
        modal.find('.texttitle').text((entity).toUpperCase()); // Cambiar .text() por .val() para input
        modal.find('.entity').text(entity);
        modal.find('.textcode').text(formattedId);

        $.ajax({
            url: '/users/' + userId, // Ruta para obtener datos del usuario
            method: 'GET',
            success: function (response) {
                //console.log(response); // Verificar si response tiene los datos esperados

                $('#modal-show').modal('show');

                // Función para aplicar estilos a los inputs con valores predeterminados
                function applyInputStyles() {
                    $('input, .document-display').each(function () {
                        let value = $(this).text() || $(this).val();
                        if (value.includes('Sin ')) {
                            $(this).css({ "color": "red", "font-weight": "bold" });
                        } else {
                            $(this).css({ "color": "black", "font-weight": "normal" });
                        }
                    });
                }

                $('#email').val(response.username?.trim() || 'Sin registrar');
                $('#role').val(response.role?.trim() || 'Sin asignar');
                $('#created_date').val(response.created_date?.trim() || 'Sin registrar');

                // Aplicar estilos a los inputs con valores predeterminados
                applyInputStyles();

                // Imagen de perfil y firma
                // Si hay imagen, mostrarla; de lo contrario, poner una por defecto
                let profilePhoto = response.profile_photo_path 
                    ? '/storage/users/photo/' + response.profile_photo_path 
                    : '/image/user_preview.png';

                let signaturePhoto = response.signature_path 
                    ? '/storage/users/signature/' + response.signature_path 
                    : '/image/signature_preview.png';

                $('#profile_photo').attr('src', profilePhoto);
                $('#signature_photo').attr('src', signaturePhoto);

                // Acceso al sistema
                $('#access').removeClass('alert-danger alert-success')
                            .addClass(response.access == 1 ? 'alert-success' : 'alert-danger');
                $('#access-icon').attr('class', response.access == 1 ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger');
                $('#access-text').text(response.access == 1 ? 'SI' : 'NO');

                // Estado del usuario
                $('#status').removeClass('alert-danger alert-success')
                            .addClass(response.status == 1 ? 'alert-success' : 'alert-danger');
                $('#status-icon').attr('class', response.status == 1 ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger');
                $('#status-text').text(response.status == 1 ? 'ACTIVO' : 'INACTIVO');

                // ✅ Mostrar empresas en lista
                let companyList = $('#company_list');
                companyList.empty(); // Limpiar lista anterior

                if (!response.company_names || response.company_names.trim() === '') {
                    companyList.append('<li style="color: red; font-weight: bold;">Sin asignar</li>');
                } else {
                    let companies = response.company_names.split(','); // Separar proyectos
                    companies.forEach(company => {
                        companyList.append(`
                            <li style="list-style: none;"> 
                                <i class="fas fa-industry" style="margin-right: 5px; color: #000;"></i> ${company.trim()}
                            </li>
                            `);
                    });
                }

                // ✅ Mostrar proyectos en lista
                let projectList = $('#project_list');
                projectList.empty(); // Limpiar lista anterior

                if (!response.project_names || response.project_names.trim() === '') {
                    projectList.append('<li style="color: red; font-weight: bold;">Sin asignar</li>');
                } else {
                    let projects = response.project_names.split(','); // Separar proyectos
                    projects.forEach(project => {
                        projectList.append(`
                            <li style="list-style: none;">
                                <i class="fas fa-building" style="margin-right: 5px; color: #000;"></i> ${project.trim()}
                            </li>
                            `);
                    });
                }
            },
            error: function () {
                alert('Error al obtener los datos del usuario.');
            }
        });
    });
});