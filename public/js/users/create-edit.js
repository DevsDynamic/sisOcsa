document.addEventListener('DOMContentLoaded', () => {
    const fieldsToUpdate = [
        { inputId: 'photo', imgId: 'picture_photo' }
    ];

    fieldsToUpdate.forEach(({ inputId, imgId }) => {
        const inputElement = document.getElementById(inputId);
        if (inputElement) {
            inputElement.addEventListener('change', (event) => updatePreview(event, imgId));
        }
    });
});

function updatePreview(event, imgId) {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
        document.getElementById(imgId).setAttribute('src', e.target.result);
    };

    if (file) {
        reader.readAsDataURL(file);
    }
}

// MOSTRAR U OCULTAR CONTRASEÑA 
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    // Alterna el tipo de input entre password y text
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
    const confirmPasswordField = document.getElementById('password_confirmation');
    const toggleConfirmPasswordIcon = document.getElementById('toggleConfirmPasswordIcon');
    
    // Alterna el tipo de input entre password y text
    if (confirmPasswordField.type === 'password') {
        confirmPasswordField.type = 'text';
        toggleConfirmPasswordIcon.classList.remove('fa-eye');
        toggleConfirmPasswordIcon.classList.add('fa-eye-slash');
    } else {
        confirmPasswordField.type = 'password';
        toggleConfirmPasswordIcon.classList.remove('fa-eye-slash');
        toggleConfirmPasswordIcon.classList.add('fa-eye');
    }
});

// Tooltip
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Función para mostrar los errores de validación de los formularios
function showFormErrors(form, errors) {
    clearFormErrors(form);
    $.each(errors, function(key, value) {
        var input = $( form + ' [name="' + key + '"]');
        var errorContainer = input.siblings('.text-danger'); // Busca el contenedor de error existente como hermano

        // Si no encuentra un contenedor de error como hermano, busca dentro del form-group
        if (!errorContainer.length) {
            errorContainer = input.closest('.form-group').find('.text-danger');
        }

        // Agrega la clase is-invalid al input
        input.addClass('is-invalid');

        // Si ya existe un contenedor de error, actualiza su contenido
        if (errorContainer.length) {
            errorContainer.text(value[0]);
        } else {
            // Si no existe, crea uno nuevo después del input-group
            if (input.closest('.input-group').length) {
                input.closest('.input-group').after('<small class="text-danger">' + value[0] + '</small>');
            } else {
                input.after('<small class="text-danger">' + value[0] + '</small>');
            }
        }
    });

    Swal.fire({
        position: 'center',
        icon: 'error',
        title: 'Error',
        text: 'Hay errores en el formulario. Por favor, corrígelos.',
        showConfirmButton: true
    });
}

// Función para limpiar errores previos en el formulario
function clearFormErrors(form) {
    $(form + ' .is-invalid').removeClass('is-invalid');
    $(form + ' .text-danger').remove();
}