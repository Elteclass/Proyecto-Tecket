<?php
// Evitar que se muestren errores en la salida
error_reporting(E_ALL);
ini_set('display_errors', 1); // Habilitamos errores para depuración

// Incluir archivo de configuración de la base de datos (ajustado a la ruta correcta)
include __DIR__ . '/config.php';

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar la estructura de la tabla
try {
    // Consulta para obtener todos los tickets con el mapeo correcto
    $sql = "SELECT 
                id, 
                nucontrol, 
                nombrealumno as nombre, 
                lugar as area, 
                asunto as failure, 
                fecha_creacion as fecha, 
                estado 
            FROM tickets 
            ORDER BY id DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }

    // Array para almacenar los tickets
    $tickets = array();

    if ($result->num_rows > 0) {
        // Recorrer los resultados y agregarlos al array
        while($row = $result->fetch_assoc()) {
            // Convertir el estado a los valores esperados por el frontend
            if ($row["estado"] === null || $row["estado"] === "" || empty($row["estado"])) {
                $row["estado"] = "pending";
            }
            
            $tickets[] = $row;
        }
    }

    // Cerrar conexión
    $conn->close();

    // Asegurarse de que no haya salida antes de los encabezados
    if (ob_get_length()) ob_clean();

    // Devolver los tickets en formato JSON
    header('Content-Type: application/json');
    echo json_encode($tickets);
    exit;
} catch (Exception $e) {
    // Mostrar error para depuración
    header('Content-Type: application/json');
    echo json_encode(array("error" => $e->getMessage()));
    exit;
}
?>
