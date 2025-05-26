<?php
// Incluir la configuración de la base de datos
require_once 'config.php';

header('Content-Type: application/json');

// Verificar si se recibieron los datos requeridos
if (!isset($_POST['id']) || !isset($_POST['estado'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Obtener los datos del formulario
$id = intval($_POST['id']);
$estado_frontend = trim($_POST['estado']);

// Función para mapear estados de frontend a DB
function mapEstadoToDB($estado) {
    switch($estado) {
        case 'pending':
            return 'pendiente';
        case 'in-progress':
            return 'proceso';
        case 'resolved':
            return 'resuelto';
        default:
            return 'pendiente'; // Por defecto
    }
}

// Mapear el estado del frontend al formato de la base de datos
$estado_db = mapEstadoToDB($estado_frontend);

try {
    // Verificar si el ticket existe
    $stmt = $conn->prepare("SELECT id FROM tickets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'El ticket no existe']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Actualizar solo el estado del ticket en la base de datos
    $stmt = $conn->prepare("UPDATE tickets SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado_db, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } else {
            echo json_encode(['success' => true, 'message' => 'No se realizaron cambios']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado: ' . $stmt->error]);
    }

    // Cerrar conexión
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>