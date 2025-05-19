<?php
// Evitar que se muestren errores en la salida
error_reporting(E_ALL);
ini_set('display_errors', 1); // Habilitamos errores para depuración

// Incluir archivo de configuración de la base de datos (ajustado a la ruta correcta)
include __DIR__ . '/config.php';

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(array("error" => "Método no permitido"));
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array("error" => "ID de ticket no proporcionado"));
    exit;
}

$id = $_POST['id'];

try {
    // Eliminar registros de seguimiento relacionados primero (para mantener integridad referencial)
    $sql_seguimiento = "DELETE FROM seguimiento WHERE id_ticket = ?";
    $stmt_seguimiento = $conn->prepare($sql_seguimiento);
    if ($stmt_seguimiento) {
        $stmt_seguimiento->bind_param("i", $id);
        $stmt_seguimiento->execute();
        $stmt_seguimiento->close();
    }

    // Eliminar el ticket
    $sql = "DELETE FROM tickets WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    if ($result) {
        // Cerrar conexión
        $stmt->close();
        $conn->close();
        
        // Devolver respuesta exitosa
        header('Content-Type: application/json');
        echo json_encode(array("success" => true, "message" => "Ticket eliminado correctamente"));
    } else {
        throw new Exception("Error al eliminar: " . $stmt->error);
    }
} catch (Exception $e) {
    // Mostrar error para depuración
    header('Content-Type: application/json');
    echo json_encode(array("error" => $e->getMessage()));
    exit;
}
?>
