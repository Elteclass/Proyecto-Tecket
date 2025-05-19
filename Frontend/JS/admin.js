$(document).ready(() => {
  // Inicializar DataTable
  const ticketsTable = $("#tickets-table").DataTable({
    language: {
      url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json",
    },
    responsive: true,
    processing: true,
    ajax: {
      url: "../Backend/get_tickets.php",
      dataSrc: (json) => {
        // Para depuración
        console.log("Datos recibidos:", json)

        // Si hay un error, mostrarlo en la consola
        if (json && json.error) {
          console.error("Error del servidor:", json.error)
          return []
        }

        // Si json es null o undefined, devolver un array vacío
        if (!json) {
          console.error("No se recibieron datos")
          return []
        }

        return json
      },
    },
    columns: [
      { data: "id" },
      { data: "nucontrol" },
      { data: "nombre" },
      { data: "area" },
      { data: "failure" },
      { data: "fecha" },
      {
        data: "estado",
        render: (data) => {
          let statusClass = ""
          let statusText = ""

          switch (data) {
            case "pending":
              statusClass = "status-pending"
              statusText = "Pendiente"
              break
            case "in-progress":
              statusClass = "status-in-progress"
              statusText = "En Proceso"
              break
            case "resolved":
              statusClass = "status-resolved"
              statusText = "Resuelto"
              break
            default:
              statusClass = "status-pending"
              statusText = "Pendiente"
          }

          return `<span class="status-badge ${statusClass}">${statusText}</span>`
        },
      },
      {
        data: null,
        render: (data) => `
                        <div class="action-buttons">
                            <button class="btn-view" data-id="${data.id}">
                                <ion-icon name="eye-outline"></ion-icon>
                            </button>
                            <button class="btn-edit" data-id="${data.id}">
                                <ion-icon name="create-outline"></ion-icon>
                            </button>
                            <button class="btn-delete" data-id="${data.id}">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </div>
                    `,
      },
    ],
    initComplete: () => {
      updateTicketCounts()
    },
  })

  // Función para actualizar los contadores de tickets
  function updateTicketCounts() {
    $.ajax({
      url: "../Backend/get_ticket_counts.php",
      type: "GET",
      dataType: "json",
      success: (data) => {
        console.log("Contadores recibidos:", data)

        if (data && data.error) {
          console.error("Error al obtener contadores:", data.error)
          return
        }

        $("#pending-count").text(data.pending || 0)
        $("#in-progress-count").text(data.inProgress || 0)
        $("#resolved-count").text(data.resolved || 0)
        $("#total-count").text(data.total || 0)
      },
      error: (xhr, status, error) => {
        console.error("Error AJAX al obtener contadores:", error)
        console.error("Respuesta:", xhr.responseText)
      },
    })
  }

  // Filtros para la tabla - Modificados para aplicarse automáticamente al cambiar
  $("#status-filter").on("change", function () {
    const value = $(this).val()

    if (value === "all") {
      ticketsTable.column(6).search("").draw()
    } else {
      // Mapear los valores del dropdown a los valores en la tabla
      let searchValue = ""
      switch (value) {
        case "pending":
          searchValue = "Pendiente"
          break
        case "in-progress":
          searchValue = "En Proceso"
          break
        case "resolved":
          searchValue = "Resuelto"
          break
      }
      ticketsTable.column(6).search(searchValue).draw()
    }
  })

  $("#area-filter").on("change", function () {
    const value = $(this).val()

    if (value === "all") {
      ticketsTable.column(3).search("").draw()
    } else {
      ticketsTable.column(3).search(value).draw()
    }
  })

  $("#failure-filter").on("change", function () {
    const value = $(this).val()

    if (value === "all") {
      ticketsTable.column(4).search("").draw()
    } else {
      ticketsTable.column(4).search(value).draw()
    }
  })

  // Restablecer filtros
  $("#reset-filters").on("click", () => {
    $("#status-filter").val("all")
    $("#area-filter").val("all")
    $("#failure-filter").val("all")
    ticketsTable.search("").columns().search("").draw()
  })

  // Modal para ver detalles del ticket
  const modal = document.getElementById("ticket-modal")
  const closeBtn = document.getElementsByClassName("close")[0]

  // Cerrar modal al hacer clic en la X
  closeBtn.onclick = () => {
    modal.style.display = "none"
  }

  // Cerrar modal al hacer clic fuera del contenido
  window.onclick = (event) => {
    if (event.target === modal) {
      modal.style.display = "none"
    }
  }

  // Abrir modal al hacer clic en el botón de ver
  $(document).on("click", ".btn-view", function () {
    const ticketId = $(this).data("id")
    loadTicketDetails(ticketId)
  })

  // Cargar detalles del ticket
  function loadTicketDetails(ticketId) {
    $.ajax({
      url: "../Backend/get_ticket_details.php",
      type: "GET",
      data: { id: ticketId },
      dataType: "json",
      success: (data) => {
        if (data && data.error) {
          console.error("Error al cargar detalles:", data.error)
          return
        }

        $("#modal-id").text(data.id)
        $("#modal-nucontrol").text(data.nucontrol)
        $("#modal-nombre").text(data.nombre)
        $("#modal-correo").text(data.correo)
        $("#modal-area").text(data.area)
        $("#modal-failure").text(data.failure)
        $("#modal-fecha").text(data.fecha)
        $("#modal-descripcion").text(data.descripcion)

        // Establecer el valor del dropdown de estado
        $("#modal-status").val(data.estado || "pending")

        // Mostrar imagen si existe
        if (data.imagen) {
          $("#modal-imagen")
            .attr("src", "../uploads/" + data.imagen)
            .show()
        } else {
          $("#modal-imagen").hide()
        }

        // Mostrar el modal
        modal.style.display = "block"
      },
      error: (xhr, status, error) => {
        console.error("Error AJAX al cargar detalles:", error)
        console.error("Respuesta:", xhr.responseText)
      },
    })
  }

  // Guardar cambios en el estado del ticket
  $("#save-status").on("click", function () {
    const ticketId = $("#modal-id").text()
    const newStatus = $("#modal-status").val()

    // Mostrar indicador de carga
    const originalText = $(this).text()
    $(this).prop("disabled", true).text("Guardando...")

    $.ajax({
      url: "../Backend/update_ticket_status.php",
      type: "POST",
      data: {
        id: ticketId,
        estado: newStatus,
      },
      dataType: "json",
      success: (response) => {
        if (response && response.error) {
          console.error("Error al actualizar estado:", response.error)
          alert("Error al actualizar el estado: " + response.error)
          $("#save-status").prop("disabled", false).text(originalText)
          return
        }

        console.log("Estado actualizado correctamente:", response)

        // Cerrar el modal
        modal.style.display = "none"

        // Recargar la tabla
        ticketsTable.ajax.reload()

        // Actualizar contadores
        updateTicketCounts()

        // Restaurar el botón
        $("#save-status").prop("disabled", false).text(originalText)
      },
      error: (xhr, status, error) => {
        console.error("Error AJAX al actualizar estado:", error)
        console.error("Respuesta:", xhr.responseText)
        alert("Error al actualizar el estado. Por favor, inténtalo de nuevo.")
        $("#save-status").prop("disabled", false).text(originalText)
      },
    })
  })

  // Editar ticket
  $(document).on("click", ".btn-edit", function () {
    const ticketId = $(this).data("id")
    // Redirigir a la página de edición
    window.location.href = `edit_ticket.php?id=${ticketId}`
  })

  // Eliminar ticket
  $(document).on("click", ".btn-delete", function () {
    const ticketId = $(this).data("id")

    if (confirm("¿Estás seguro de que deseas eliminar este ticket?")) {
      $.ajax({
        url: "../Backend/delete_ticket.php",
        type: "POST",
        data: { id: ticketId },
        success: (response) => {
          // Recargar la tabla
          ticketsTable.ajax.reload()

          // Actualizar contadores
          updateTicketCounts()
        },
        error: (xhr, status, error) => {
          console.error("Error AJAX al eliminar ticket:", error)
          console.error("Respuesta:", xhr.responseText)
        },
      })
    }
  })

  // Actualizar datos cada 30 segundos
  setInterval(() => {
    ticketsTable.ajax.reload(null, false)
    updateTicketCounts()
  }, 30000)
})
