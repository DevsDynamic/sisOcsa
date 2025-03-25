function goBack() {
    window.location.href = document.referrer;
}

document.addEventListener('DOMContentLoaded', function () {
    // Manejar la selección de todos los permisos dentro de un submódulo
    document.querySelectorAll('.submodule-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            console.log("Submódulo checkbox cambiado:", this); // Ver qué checkbox está siendo marcado/desmarcado
            let subModule = this.dataset.submodule;
            let checkboxes = document.querySelectorAll(`.submodule-permission[data-submodule="${subModule}"]`);

            checkboxes.forEach(cb => cb.checked = checkbox.checked);

            // Verificar si el módulo debe marcarse o desmarcarse
            let moduleCard = checkbox.closest('.card');
            if (moduleCard) updateModuleCheckbox(moduleCard);
        });
    });

    // Manejar la selección de permisos individuales dentro de un submódulo
    document.querySelectorAll('.submodule-permission').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            console.log("Permiso individual cambiado:", this); // Ver qué permiso está siendo marcado/desmarcado
            let subModule = this.dataset.submodule;
            let parentCheckbox = document.querySelector(`.submodule-checkbox[data-submodule="${subModule}"]`);
            let allCheckboxes = document.querySelectorAll(`.submodule-permission[data-submodule="${subModule}"]`);
            let allChecked = Array.from(allCheckboxes).every(cb => cb.checked);

            if (parentCheckbox) {
                parentCheckbox.checked = allChecked;
            }

            // Verificar si el módulo debe marcarse o desmarcarse
            let moduleCard = checkbox.closest('.card');
            if (moduleCard) updateModuleCheckbox(moduleCard);
        });
    });

    // Manejar la selección de todos los submódulos dentro de un módulo
    document.querySelectorAll('.module-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            console.log("Módulo checkbox cambiado:", this); // Ver qué checkbox de módulo se marca/desmarca
            let moduleCard = checkbox.closest('.card');
            if (!moduleCard) return;

            let submoduleCheckboxes = moduleCard.querySelectorAll('.submodule-checkbox');
            let permissionCheckboxes = moduleCard.querySelectorAll('.submodule-permission');

            submoduleCheckboxes.forEach(cb => cb.checked = checkbox.checked);
            permissionCheckboxes.forEach(cb => cb.checked = checkbox.checked);

            // Actualizar el módulo después de marcar todos los submódulos/permisos
            updateModuleCheckbox(moduleCard);
        });
    });

    // Verificar si un módulo debe marcarse cuando todos sus submódulos y permisos están seleccionados
    function updateModuleCheckbox(moduleCard) {
        if (!moduleCard) return;

        let moduleCheckbox = moduleCard.querySelector('.module-checkbox');
        if (!moduleCheckbox) return;

        // Verificación de todos los submódulos y permisos
        let allSubmoduleCheckboxes = moduleCard.querySelectorAll('.submodule-checkbox');
        let allPermissionsCheckboxes = moduleCard.querySelectorAll('.submodule-permission');

        // Verificación de si existen submódulos, si no hay, los permisos directos deben ser suficientes
        let allSubmodulesChecked = allSubmoduleCheckboxes.length > 0 ? Array.from(allSubmoduleCheckboxes).every(cb => cb.checked) : true;
        let allPermissionsChecked = Array.from(allPermissionsCheckboxes).every(cb => cb.checked);

        console.log("Estado de los submódulos y permisos del módulo:", allSubmodulesChecked, allPermissionsChecked); // Ver el estado antes de actualizar el checkbox del módulo

        // Si todos los submódulos y permisos están marcados, marcar el módulo
        moduleCheckbox.checked = allSubmodulesChecked && allPermissionsChecked;

        console.log("Estado del módulo actualizado:", moduleCheckbox.checked); // Ver el estado después de actualizar
    }

    // Asegurar que cada checkbox individual actualice el estado del módulo al cambiar
    document.querySelectorAll('.submodule-checkbox, .submodule-permission').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            let moduleCard = checkbox.closest('.card');
            if (moduleCard) updateModuleCheckbox(moduleCard);
        });
    });
});