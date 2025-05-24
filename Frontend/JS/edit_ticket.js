$(document).ready(function() {
    // Obtener el ID del ticket de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const ticketId = urlParams.get('id');
    
    if (!ticketId) {
        showNotification('ID de ticket no válido', 'error');
        setTimeout(() => {
            window.location.href = 'admin.html';
        }, 2000);
        return;
    }
    
    // Mostrar el ID del ticket en la página
    $('#ticket-id').text(ticketId);
    $('#id').val(ticketId);
    
    // Cargar los datos del ticket
    loadTicketData(ticketId);
    
    // Manejar el botón de cancelar
    $('#cancel-btn').on('click', function() {
        window.location.href = 'admin.html';
    });
    
    // Manejar el checkbox de eliminar imagen
    $('#remove-image').on('change', function() {
        if (this.checked) {
            $('#upload-image').prop('disabled', true);
            $('.upload-area').css('opacity', '0.5');
        } else {
            $('#upload-image').prop('disabled', false);
            $('.upload-area').css('opacity', '1');
        }
    });
    
    // Mostrar nombre del archivo seleccionado
    $('#upload-image').on('change', function() {
        const fileName = this.files[0]?.name;
        if (fileName) {
            $('.upload-content p').text(fileName);
            // Desmarcar la opción de eliminar imagen si se selecciona una nueva
            $('#remove-image').prop('checked', false);
        } else {
            $('.upload-content p').text('Haz clic para subir una nueva imagen');
        }
    });
    
    // Manejar envío del formulario
    $('#edit-form').on('submit', function(e) {
        e.preventDefault();
        
        // Crear FormData para enviar los datos incluyendo la imagen
        const formData = new FormData(this);
        
        // Añadir flag para indicar si se debe eliminar la imagen
        formData.append('remove_image', $('#remove-image').is(':checked') ? '1' : '0');
        
        // Enviar los datos al servidor
        $.ajax({
            url: '../Backend/update_ticket.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showNotification('Ticket actualizado correctamente', 'success');
                        setTimeout(() => {
                            window.location.href = 'admin.html';
                        }, 2000);
                    } else {
                        showNotification(data.message || 'Error al actualizar el ticket', 'error');
                    }
                } catch (e) {
                    showNotification('Error en la respuesta del servidor', 'error');
                }
            },
            error: function() {
                showNotification('Error al comunicarse con el servidor', 'error');
            }
        });
    });
    
    // Función para cargar los datos del ticket
    function loadTicketData(ticketId) {
        $.ajax({
            url: '../Backend/get_ticket_details.php',
            type: 'GET',
            data: { id: ticketId },
            dataType: 'json',
            success: function(data) {
                // Llenar el formulario con los datos del ticket
                $('#nucontrol').val(data.nucontrol);
                $('#nombre').val(data.nombre);
                $('#correo').val(data.correo);
                $('#area').val(data.area);
                $('#failure').val(data.failure);
                $('#estado').val(data.estado);
                $('#descripcion').val(data.descripcion);
                
                // Mostrar la imagen si existe
                if (data.imagen) {
                    $('#current-image').attr('src', '../uploads/' + data.imagen).show();
                    $('#no-image-message').hide();
                } else {
                    $('#current-image').hide();
                    $('#no-image-message').show();
                }
            },
            error: function() {
                showNotification('Error al cargar los datos del ticket', 'error');
            }
        });
    }
    
    // Función para mostrar notificaciones
    function showNotification(message, type) {
        const notification = $('#notification');
        notification.text(message);
        notification.removeClass('success error').addClass(type);
        notification.addClass('show');
        
        setTimeout(() => {
            notification.removeClass('show');
        }, 5000);
    }
});