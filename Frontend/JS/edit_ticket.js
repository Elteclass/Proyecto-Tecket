$(document).ready(() => {
  // Obtener el ID del ticket de la URL
  const urlParams = new URLSearchParams(window.location.search)
  const ticketId = urlParams.get("id")

  if (!ticketId) {
    showNotification("ID de ticket no válido", "error")
    setTimeout(() => {
      window.location.href = "admin.html"
    }, 2000)
    return
  }

  // Mostrar el ID del ticket en la página
  $("#ticket-id").text(ticketId)
  $("#id").val(ticketId)

  // Cargar los datos del ticket
  loadTicketData(ticketId)

  // Manejar el botón de cancelar
  $("#cancel-btn").on("click", () => {
    if (confirm("¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.")) {
      window.location.href = "admin.html"
    }
  })

  // Manejar el checkbox de eliminar imagen
  $("#remove-image").on("change", function () {
    if (this.checked) {
      $("#upload-image").prop("disabled", true)
      $(".upload-area").css("opacity", "0.5")
      $(".upload-content p").text("Imagen será eliminada")
    } else {
      $("#upload-image").prop("disabled", false)
      $(".upload-area").css("opacity", "1")
      $(".upload-content p").text("Haz clic para subir una nueva imagen")
    }
  })

  // Mostrar nombre del archivo seleccionado
  $("#upload-image").on("change", function () {
    const fileName = this.files[0]?.name
    if (fileName) {
      $(".upload-content p").text(`Archivo seleccionado: ${fileName}`)
      // Desmarcar la opción de eliminar imagen si se selecciona una nueva
      $("#remove-image").prop("checked", false)
      $("#upload-image").prop("disabled", false)
      $(".upload-area").css("opacity", "1")
    } else {
      $(".upload-content p").text("Haz clic para subir una nueva imagen")
    }
  })

  // Manejar envío del formulario
  $("#edit-form").on("submit", function (e) {
    e.preventDefault()

    // Validar campos requeridos
    const requiredFields = ["nucontrol", "nombre", "correo", "area", "failure", "descripcion", "estado"]
    let isValid = true

    requiredFields.forEach((field) => {
      const value = $(`#${field}`).val().trim()
      if (!value) {
        showNotification(`El campo ${field} es requerido`, "error")
        isValid = false
        return false
      }
    })

    if (!isValid) return

    // Validar email
    const email = $("#correo").val().trim()
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(email)) {
      showNotification("Por favor ingresa un correo válido", "error")
      return
    }

    // Crear FormData para enviar los datos incluyendo la imagen
    const formData = new FormData(this)

    // Añadir flag para indicar si se debe eliminar la imagen
    formData.append("remove_image", $("#remove-image").is(":checked") ? "1" : "0")

    // Mostrar indicador de carga
    const submitBtn = $('button[type="submit"]')
    const originalText = submitBtn.text()
    submitBtn.prop("disabled", true).text("Guardando...")

    // Enviar los datos al servidor
    $.ajax({
      url: "../Backend/update_ticket.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      timeout: 30000,
      success: (response) => {
        try {
          const data = typeof response === "string" ? JSON.parse(response) : response
          if (data.success) {
            showNotification(data.message || "Ticket actualizado correctamente", "success")
            setTimeout(() => {
              window.location.href = "admin.html"
            }, 2000)
          } else {
            showNotification(data.message || "Error al actualizar el ticket", "error")
          }
        } catch (e) {
          console.error("Error parsing response:", e)
          showNotification("Error en la respuesta del servidor", "error")
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX Error:", status, error)
        if (status === "timeout") {
          showNotification("Tiempo de espera agotado. Inténtalo de nuevo.", "error")
        } else {
          showNotification("Error al comunicarse con el servidor", "error")
        }
      },
      complete: () => {
        // Restaurar botón
        submitBtn.prop("disabled", false).text(originalText)
      },
    })
  })

  // Función para cargar los datos del ticket
  function loadTicketData(ticketId) {
    $.ajax({
      url: "../Backend/get_ticket_details.php",
      type: "GET",
      data: { id: ticketId },
      dataType: "json",
      success: (data) => {
        if (data.error) {
          showNotification(data.error, "error")
          return
        }

        // Llenar el formulario con los datos del ticket
        $("#nucontrol").val(data.nucontrol || "")
        $("#nombre").val(data.nombre || "")
        $("#correo").val(data.correo || "")
        $("#area").val(data.area || "")
        $("#failure").val(data.failure || "")
        $("#estado").val(data.estado || "")
        $("#descripcion").val(data.descripcion || "")

        // Mostrar la imagen si existe
        if (data.imagen && data.imagen.trim() !== "") {
          const imagePath = "../uploads/" + data.imagen
          $("#current-image")
            .attr("src", imagePath)
            .show()
            .on("error", function () {
              $(this).hide()
              $("#no-image-message").show()
            })
          $("#no-image-message").hide()
        } else {
          $("#current-image").hide()
          $("#no-image-message").show()
        }
      },
      error: (xhr, status, error) => {
        console.error("Error loading ticket data:", status, error)
        showNotification("Error al cargar los datos del ticket", "error")
      },
    })
  }

  // Función para mostrar notificaciones
  function showNotification(message, type) {
    const notification = $("#notification")
    notification.text(message)
    notification.removeClass("success error").addClass(type)
    notification.addClass("show")

    setTimeout(() => {
      notification.removeClass("show")
    }, 5000)
  }
})
