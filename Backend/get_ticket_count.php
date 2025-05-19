<?php
// Evitar que se muestren errores en la salida
error_reporting(E_ALL);
ini_set('display_errors', 1); // Habilitamos errores para depuración

// Incluir archivo de configuración de la base de datos (ajustado a la ruta correcta)
include __DIR__ . '/config.php';

try {
    // Inicializar contadores
    $counts = array(
        "pending" => 0,
        "inProgress" => 0,
        "resolved" => 0,
        "total" => 0
    );

    // Contar tickets pendientes
    $sql_pending = "SELECT COUNT(*) as count FROM tickets WHERE estado = 'pending' OR estado IS NULL OR estado = ''";
    $result_pending = $conn->query($sql_pending);
    if (!$result_pending) {
        throw new Exception("Error en la consulta de pendientes: " . $conn->error);
    }
    
    if ($result_pending->num_rows > 0) {
        $row = $result_pending->fetch_assoc();
        $counts["pending"] = (int)$row["count"];
    }

    // Contar tickets en proceso
    $sql_in_progress = "SELECT COUNT(*) as count FROM tickets WHERE estado = 'in-progress'";
    $result_in_progress = $conn->query($sql_in_progress);
    if (!$result_in_progress) {
        throw new Exception("Error en la consulta de en proceso: " . $conn->error);
    }
    
    if ($result_in_progress->num_rows > 0) {
        $row = $result_in_progress->fetch_assoc();
        $counts["inProgress"] = (int)$row["count"];
    }

    // Contar tickets resueltos
    $sql_resolved = "SELECT COUNT(*) as count FROM tickets WHERE estado = 'resolved'";
    $result_resolved = $conn->query($sql_resolved);
    if (!$result_resolved) {
        throw new Exception("Error en la consulta de resueltos: " . $conn->error);
    }
    
    if ($result_resolved->num_rows > 0) {
        $row = $result_resolved->fetch_assoc();
        $counts["resolved"] = (int)$row["count"];
    }

    // Contar total de tickets
    $sql_total = "SELECT COUNT(*) as count FROM tickets";
    $result_total = $conn->query($sql_total);
    if (!$result_total) {
        throw new Exception("Error en la consulta de total: " . $conn->error);
    }
    
    if ($result_total->num_rows > 0) {
        $row = $result_total->fetch_assoc();
        $counts["total"] = (int)$row["count"];
    }

    // Cerrar conexión
    $conn->close();

    // Devolver los contadores en formato JSON
    header('Content-Type: application/json');
    echo json_encode($counts);
} catch (Exception $e) {
    // Mostrar error para depuración
    header('Content-Type: application/json');
    echo json_encode(array("error" => $e->getMessage()));
    exit;
}
?>
