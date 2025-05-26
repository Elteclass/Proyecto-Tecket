<?php
// Limpiar cualquier output previo y suprimir errores
ob_clean();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de la base de datos teckets
$servername = "localhost";
$username = "root";
$password = "";
$database_teckets = "teckets";

// Configuración de la base de datos tokens
$database_tokens = "tokens";

try {
    // Verificar si la solicitud es POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verificar que el JSON se decodificó correctamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error en el formato de datos JSON');
    }
    
    $email = isset($input['email']) ? trim($input['email']) : '';

    if (empty($email)) {
        throw new Exception('El correo electrónico es requerido');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    // Conectar a la base de datos teckets para verificar el usuario
    $conn_teckets = new mysqli($servername, $username, $password, $database_teckets);
    if ($conn_teckets->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }
    $conn_teckets->set_charset("utf8");

    // Verificar si el correo existe en la tabla usuarios
    $stmt = $conn_teckets->prepare("SELECT id, nombre FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('El correo electrónico no está registrado en el sistema');
    }

    $user = $result->fetch_assoc();
    $stmt->close();
    $conn_teckets->close();

    // Generar código de verificación de 4 dígitos
    $verification_code = sprintf('%04d', rand(0, 9999));

    // Conectar a la base de datos tokens
    $conn_tokens = new mysqli($servername, $username, $password, $database_tokens);
    if ($conn_tokens->connect_error) {
        throw new Exception('Error de conexión a la base de datos de tokens');
    }
    $conn_tokens->set_charset("utf8");

    // Eliminar códigos anteriores para este correo
    $stmt = $conn_tokens->prepare("DELETE FROM compare WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    // Insertar nuevo código
    $stmt = $conn_tokens->prepare("INSERT INTO compare (correo, token) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $verification_code);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al guardar el código de verificación');
    }
    $stmt->close();
    $conn_tokens->close();

    // MODO DESARROLLO: Mostrar el código en la respuesta
    // En producción, aquí enviarías el correo electrónico
    echo json_encode([
        'success' => true,
        'message' => 'Código de verificación generado. Para desarrollo, el código es: ' . $verification_code,
        'development_mode' => true,
        'verification_code' => $verification_code // Solo para desarrollo
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>
