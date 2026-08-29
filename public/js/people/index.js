$(document).ready(function() {
    // Configuración global para las solicitudes AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.select2').select2({
        placeholder: 'Seleccione una opción',
        allowClear: true,
        width: '100%' // Asegurarse de que ocupe el 100% del ancho del contenedor
    });

    // Función para manejar el evento change en el campo type_document
    function handleTypeDocumentChange(modalSelector) {
        $(modalSelector).on('change', 'select[name="type_document"]', function() {
            console.log('change option type_document');
            var modal = $(modalSelector);
            var selectedOption = $(this).find(':selected');
            var maxLength = selectedOption.data('max-length');
            var placeholderText = maxLength ? '0/' + maxLength : 'Seleccione un tipo de documento';

            // Actualizar placeholder del document_number
            modal.find('input[name="document_number"]').attr('placeholder', placeholderText);

            // Limpiar el campo de número de documento y el campo de nombre
            modal.find('input[name="document_number"]').val('');
            modal.find('input[name="full_name"]').val('');
            console.log(selectedOption.text());
            console.log(maxLength);
            // Mostrar u ocultar el botón toggleSearch según la selección
            if (selectedOption.text() === 'DNI' || selectedOption.text() === 'RUC') {
                console.log('Is DNI or RUC');
                modal.find('#toggleSearchContainer').removeClass('hidden').show();

                // Al mostrar el botón el input debe tener las esquinas derechas esquinadas
                modal.find('input[name="document_number"]').css({
                    'border-top-right-radius': '0',
                    'border-bottom-right-radius': '0'
                });

                modal.find('input[name="document_number"]').attr('type', 'number');

                // Limitar la longitud del número con JavaScript
                modal.find('input[name="document_number"]').off('input').on('input', function() {
                    var value = $(this).val();
                    if (maxLength && value.length > maxLength) {
                        $(this).val(value.slice(0, maxLength));
                    }
                });
            } else {
                console.log('Is not DNI or RUC');
                modal.find('#toggleSearchContainer').addClass('hidden').hide();

                // Inicialmente el input debe tener las esquinas redondeadas
                modal.find('input[name="document_number"]').css({
                    'border-top-right-radius': '.25rem',
                    'border-bottom-right-radius': '.25rem'
                });
                
                modal.find('input[name="document_number"]').attr('type', 'text'); // Establecer el tipo de entrada a texto
                modal.find('input[name="document_number"]').off('input'); // Quitar la limitación para tipo texto
            }

            // Establecer el atributo maxlength si está definido
            if (maxLength) {
                modal.find('input[name="document_number"]').attr('maxlength', maxLength);
            } else {
                modal.find('input[name="document_number"]').removeAttr('maxlength');
            }
        });
    }

    // Vincular la función a los eventos change de los modales create y edit
    $(document).ready(function() {
        handleTypeDocumentChange('#modal-create');
        handleTypeDocumentChange('#modal-edit');
    });

    // Evento click para el botón "Buscar" dentro de cualquier modal
    $('.modal').on('click', '#toggleSearch', function() {
        // Obtener el modal en el que se encuentra el botón
        var modal = $(this).closest('.modal');

        // Obtener los valores del tipo de documento y número de documento dentro del mismo modal
        var typeDocument = modal.find('#type_document').val();
        var documentNumber = modal.find('#document_number').val();
        var selectedOption = modal.find('#type_document option:selected');
        var maxLength = selectedOption.data('max-length'); // Obtener el maxLength del tipo de documento seleccionado
        
        // Obtener el texto de la opción seleccionada                     
        var typeDocumentText = selectedOption.text().toLowerCase(); // Convertir a minúsculas                      

        // Limpiar el campo de nombre
        modal.find('#full_name').val('');

        // Verificar que se haya seleccionado un tipo de documento y que el número no esté vacío
        if (!typeDocument || !documentNumber) {
            // Mostrar mensaje de éxito
            Swal.fire({
                position: 'center',
                icon: 'info',
                title: 'Alerta',
                text: 'Debe seleccionar un tipo de documento y completar el número.',
                timer: 2000
            });
            console.log('Debe seleccionar un tipo de documento y completar el número.');
            return;
        }

        // Validar la longitud del número de documento según el tipo de documento
        if ((typeDocumentText == 'dni' || typeDocumentText == 'ruc') && documentNumber.length != maxLength) {
            // Mostrar mensaje de alerta
            Swal.fire({
                position: 'center',
                icon: 'info',
                title: 'Alerta',
                text: 'El ' + typeDocumentText.toUpperCase() + ' debe contener ' + maxLength + ' caracteres.',
                timer: 2000
            });
            console.log('El ' + typeDocumentText.toUpperCase() + ' debe contener ' + maxLength + ' caracteres.');
            return;
        }

        // Construir la URL con los valores concatenados
        var apiUrl = `https://apiperu.dev/api/${typeDocumentText}/${documentNumber}`;

        // Realizar la solicitud AJAX al API
        $.ajax({
            url: apiUrl, // Usar la URL construida
            method: 'GET', // Método HTTP GET para obtener datos
            data: {
                api_token: 'bfc1274d00e4cab1a47af15ce7ef4b52a6ca1246153f92a3d376f507d79df506'
            },
            success: function(response) {
                console.log('Response API: ', response);
                // Verificar si la respuesta contiene datos válidos para el nombre
                if (response && response.data) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Exitoso',
                        text: 'Datos encontrados',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    if ( typeDocumentText === 'DNI' || typeDocumentText === 'dni') {
                        // Llenar el campo con el nombre con la respuesta del API
                        modal.find('#full_name').val(response.data.nombre_completo);
                    } else {
                        // Llenar el campo con la razón social con la respuesta del API
                        modal.find('#full_name').val(response.data.nombre_o_razon_social);
                    }
                    
                } else {
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Error',
                        text: 'No se encontraron datos asociado al documento ingresado.',
                        timer: 2000
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al consultar el API:', error);
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al consultar el API. Por favor, inténtelo nuevamente.',
                    showConfirmButton: true
                });
            }
        });
    });
});

// // Función para seleccionar tipo de persona
// function selectTypePerson() {
//     var typeValue = $('input[name="type"]').val(); // Obtener el valor del campo 'type'
//     var selectTypePerson = $('#type_person'); // Seleccionar el dropdown de "Tipo de Persona"
    
//     // Primero, habilitar todas las opciones por defecto
//     selectTypePerson.find('option').prop('disabled', false);

//     // Remover cualquier input hidden previo que pueda existir
//     $('input[name="type_person_hidden"]').remove();

//     // Función para bloquear la selección y agregar un campo oculto
//     function setAndLockSelection(value) {
//         selectTypePerson.val(value).prop('disabled', true);
//         $('<input>').attr({
//             type: 'hidden',
//             name: 'type_person',
//             value: value
//         }).appendTo(selectTypePerson.parent()); // Agrega el campo oculto
//     }

//     // Escenario 1: Si 'type' es "co", seleccionar "contacto" y deshabilitarlo.
//     if (typeValue === 'co') {
//         setAndLockSelection(1);
//     }
//     // Escenario 2: Si 'type' es "cp", seleccionar "cliente potencial" (value 2) y deshabilitarlo
//     else if (typeValue === 'cp') {
//         setAndLockSelection(2);
//     }
//     // Escenario 3: Si es otro 'type', mostrar todas las opciones y no hacer nada
//     else {
//         selectTypePerson.prop('disabled', false); // Habilitar todas las opciones
//     }

//     // Hacer que el select se actualice si es necesario
//     selectTypePerson.trigger('change');

//     console.log("Type person: " . typeValue);
// }

// function evaluateTypePerson() {
//     var typeValue = $('input[name="type"]').val(); // Obtener el valor del campo 'type'
//     var selectTypePerson = $('#type_person'); // Seleccionar el dropdown de "Tipo de Persona"

//     // Verificar y mostrar los campos al cargar la página si es contcato
//     if (selectTypePerson.val() == "1") {
//         $("#contactFields").show();
//     }
// }

// Función para seleccionar tipo de persona (Se ejecuta en ambos modales)
function selectTypePerson(modal) {
    var typeValue = modal.find('input[name="type"]').val(); // Obtener el valor del campo 'type'
    var selectTypePerson = modal.find('#type_person'); // Seleccionar el dropdown de "Tipo de Persona"

    // Primero, habilitar todas las opciones por defecto
    selectTypePerson.find('option').prop('disabled', false);

    // Remover cualquier input hidden previo que pueda existir
    modal.find('input[name="type_person_hidden"]').remove();

    // Función para bloquear la selección y agregar un campo oculto
    function setAndLockSelection(value) {
        selectTypePerson.val(value).prop('disabled', true);
        $('<input>').attr({
            type: 'hidden',
            name: 'type_person',
            value: value
        }).appendTo(selectTypePerson.parent()); // Agrega el campo oculto
    }

    // Escenario 1: Si 'type' es "co", seleccionar "contacto" y deshabilitarlo.
    if (typeValue === 'co') {
        setAndLockSelection(1);
    }
    // Escenario 2: Si 'type' es "cp", seleccionar "cliente potencial" (value 2) y deshabilitarlo
    else if (typeValue === 'cp') {
        setAndLockSelection(2);
    }
    // Escenario 3: Si es otro 'type', mostrar todas las opciones y no hacer nada
    else {
        selectTypePerson.prop('disabled', false); // Habilitar todas las opciones
    }

    // Hacer que el select se actualice si es necesario
    selectTypePerson.trigger('change');
}

// Función para evaluar el tipo de persona y mostrar campos de contacto
function evaluateTypePerson(modal) {
    var selectTypePerson = modal.find('#type_person'); // Seleccionar el dropdown de "Tipo de Persona"
    var contactFields = modal.find("#contactFields"); // Campos ocultos de contacto

    // Mostrar los campos ocultos solo si es "Contacto" (value 1)
    if (selectTypePerson.val() == "1") {
        contactFields.show();
    } else {
        contactFields.hide();
    }
}

// document.addEventListener("DOMContentLoaded", function() {
//     let typePersonSelect = document.getElementById("type_person");
//     let contactFields = document.getElementById("contactFields");
//     let emailField = document.getElementById("email");

//     typePersonSelect.addEventListener("change", function() {
//         if (this.value === "1") { // Ajusta según el ID real de CO
//             contactFields.style.display = "block";
//             emailField.setAttribute("required", "required");
//         } else {
//             contactFields.style.display = "none";
//             emailField.removeAttribute("required");
//         }
//     });

//     // Disparar evento al cargar la página (para manejar valores preseleccionados)
//     typePersonSelect.dispatchEvent(new Event("change"));
// });

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

// Función para limpiar los errores del formulario EDITAR
function clearFormErrors(form) {
    $(form + ' .is-invalid').removeClass('is-invalid');
    $(form + ' .text-danger').remove();
}

// Restablecer el formulario cuando el MODAL CREAR se oculta
$('#modal-create').on('hidden.bs.modal', function() {
    $('#formCreateCustomer')[0].reset();
    $('#createErrorMessages').hide().empty();
    clearFormErrors();
});

// Restablecer el formulario cuando el MODAL EDITAR se oculta
$('#modal-edit').on('hidden.bs.modal', function() {
    $('#formEditCustomer')[0].reset();
    clearFormErrors();
});

function loadPersonHistory(personId, modal) {
    const container = modal.find('#crm-history');
    if (!container.length) return;
    container.html('<span class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando historial...</span>');
    $.get('/people/' + encodeURIComponent(personId) + '/history').done(function(items) {
        if (!Array.isArray(items) || !items.length) return container.html('<span class="text-muted">Sin cambios de etapa registrados.</span>');
        container.empty();
        items.forEach(function(item) {
            const row = $('<div class="crm-history-item">').append('<span class="crm-history-dot"></span>'), content = $('<div>');
            $('<strong>').text((item.from || 'Sin etapa') + ' → ' + (item.to || 'Sin etapa')).appendTo(content);
            $('<small>').text((item.date || '') + (item.changed_by ? ' · ' + item.changed_by : '')).appendTo(content);
            $('<p>').text(item.reason || 'Cambio de etapa').appendTo(content);
            if (item.notes) $('<p class="text-muted mb-0">').text(item.notes).appendTo(content);
            row.append(content).appendTo(container);
        });
    }).fail(function() { container.html('<span class="text-danger">No se pudo consultar el historial.</span>'); });
}
