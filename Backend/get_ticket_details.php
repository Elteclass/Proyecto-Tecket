<?php
// Evitar que se muestren errores en la salida
error_reporting(E_ALL);
ini_set('display_errors', 1); // Habilitamos errores para depuración

// Incluir archivo de configuración de la base de datos (ajustado a la ruta correcta)
include __DIR__ . '/config.php';

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array("error" => "ID de ticket no proporcionado"));
    exit;
}

$id = $_GET['id'];

try {
    // Consulta para obtener los detalles del ticket con el mapeo correcto
    $sql = "SELECT 
                id, 
                nucontrol, 
                nombrealumno as nombre, 
                correo, 
                lugar as area, 
                asunto as failure, 
                descripcion, 
                fecha_creacion as fecha, 
                estado 
            FROM tickets 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Obtener los detalles del ticket
        $ticket = $result->fetch_assoc();
        
        // Convertir el estado a los valores esperados por el frontend
        if ($ticket["estado"] === null || $ticket["estado"] === "" || empty($ticket["estado"])) {
            $ticket["estado"] = "pending";
        }
        
        // Verificar si hay una imagen asociada (esto es un ejemplo, ajusta según tu estructura)
        $ticket["imagen"] = null; // Por defecto no hay imagen
        
        // Cerrar conexión
        $stmt->close();
        $conn->close();
        
        // Devolver los detalles en formato JSON
        header('Content-Type: application/json');
        echo json_encode($ticket);
    } else {
        // No se encontró el ticket
        header('HTTP/1.1 404 Not Found');
        echo json_encode(array("error" => "Ticket no encontrado"));
    }
} catch (Exception $e) {
    // Mostrar error para depuración
    header('Content-Type: application/json');
    echo json_encode(array("error" => $e->getMessage()));
    exit;
}
?>
