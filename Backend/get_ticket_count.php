<?php
// Evitar que se muestren errores en la salida
error_reporting(E_ALL);
ini_set('display_errors', 0); // Deshabilitamos errores para producción

// Incluir archivo de configuración de la base de datos
include __DIR__ . '/config.php';

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');

try {
    // Verificar la conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Inicializar contadores
    $counts = array(
        "pending" => 0,
        "inProgress" => 0,
        "resolved" => 0,
        "total" => 0
    );

    // Contar tickets pendientes (mapear 'pendiente' a 'pending')
    $sql_pending = "SELECT COUNT(*) as count FROM tickets WHERE estado = 'pendiente' OR estado IS NULL OR estado = ''";
    $result_pending = $conn->query($sql_pending);
    if ($result_pending && $result_pending->num_rows > 0) {
        $row = $result_pending->fetch_assoc();
        $counts["pending"] = (int)$row["count"];
    }

    // Contar tickets en proceso (mapear 'proceso' a 'inProgress')
    $sql_in_progress = "SELECT COUNT(*) as count FROM tickets WHERE estado = 'proceso'";
    $result_in_progress = $conn->query($sql_in_progress);
    if ($result_in_progress && $result_in_progress->num_rows > 0) {
        $row = $result_in_progress->fetch_assoc();
        $counts["inProgress"] = (int)$row["count"];
    }

    // Contar tickets resueltos (mapear 'resuelto' a 'resolved')
    $sql_resolved = "SELECT COUNT(*) as count FROM tickets WHERE estado = 'resuelto'";
    $result_resolved = $conn->query($sql_resolved);
    if ($result_resolved && $result_resolved->num_rows > 0) {
        $row = $result_resolved->fetch_assoc();
        $counts["resolved"] = (int)$row["count"];
    }

    // Contar total de tickets
    $sql_total = "SELECT COUNT(*) as count FROM tickets";
    $result_total = $conn->query($sql_total);
    if ($result_total && $result_total->num_rows > 0) {
        $row = $result_total->fetch_assoc();
        $counts["total"] = (int)$row["count"];
    }

    // Cerrar conexión
    $conn->close();

    // Devolver los contadores en formato JSON
    echo json_encode($counts);

} catch (Exception $e) {
    // Devolver error en formato JSON
    echo json_encode(array("error" => $e->getMessage()));
}
?>