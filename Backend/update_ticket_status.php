<?php
// Evitar que se muestren errores en la salida
error_reporting(E_ALL);
ini_set('display_errors', 1); // Habilitamos errores para depuración

// Incluir archivo de configuración de la base de datos
include __DIR__ . '/config.php';

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(array("error" => "Método no permitido"));
    exit;
}

// Verificar si se proporcionaron los datos necesarios
if (!isset($_POST['id']) || empty($_POST['id']) || !isset($_POST['estado'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array("error" => "Datos incompletos"));
    exit;
}

$id = $_POST['id'];
$estado = $_POST['estado'];

try {
    // Validar el estado
    $estados_validos = array('pending', 'in-progress', 'resolved');
    if (!in_array($estado, $estados_validos)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(array("error" => "Estado no válido: " . $estado));
        exit;
    }

    // Actualizar el estado del ticket
    $sql = "UPDATE tickets SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("si", $estado, $id);
    $result = $stmt->execute();

    if ($result) {
        // Registrar el cambio en la tabla de seguimiento (opcional)
        $fecha_actual = date('Y-m-d H:i:s');
        $descripcion = "Cambio de estado a: " . $estado;
        
        $sql_seguimiento = "INSERT INTO seguimiento (id_ticket, fecha, descripcion, estado_nuevo) VALUES (?, ?, ?, ?)";
        $stmt_seguimiento = $conn->prepare($sql_seguimiento);
        if ($stmt_seguimiento) {
            $stmt_seguimiento->bind_param("isss", $id, $fecha_actual, $descripcion, $estado);
            $stmt_seguimiento->execute();
            $stmt_seguimiento->close();
        }
        
        // Cerrar conexión
        $stmt->close();
        $conn->close();
        
        // Devolver respuesta exitosa
        header('Content-Type: application/json');
        echo json_encode(array(
            "success" => true, 
            "message" => "Estado actualizado correctamente",
            "id" => $id,
            "estado" => $estado
        ));
    } else {
        throw new Exception("Error al actualizar: " . $stmt->error);
    }
} catch (Exception $e) {
    // Mostrar error para depuración
    header('Content-Type: application/json');
    echo json_encode(array("error" => $e->getMessage()));
    exit;
}
?>
