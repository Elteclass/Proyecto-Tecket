<?php
// Limpiar cualquier output previo y suprimir errores
ob_clean();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de la base de datos tokens
$servername = "localhost";
$username = "root";
$password = "";
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
    $code = isset($input['code']) ? trim($input['code']) : '';

    if (empty($email) || empty($code)) {
        throw new Exception('El correo y el código son requeridos');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    if (!preg_match('/^\d{4}$/', $code)) {
        throw new Exception('El código debe ser de 4 dígitos');
    }

    // Conectar a la base de datos tokens
    $conn_tokens = new mysqli($servername, $username, $password, $database_tokens);
    if ($conn_tokens->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }
    $conn_tokens->set_charset("utf8");

    // Verificar el código
    $stmt = $conn_tokens->prepare("SELECT ID FROM compare WHERE correo = ? AND token = ?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Código de verificación incorrecto o expirado');
    }

    $stmt->close();
    $conn_tokens->close();

    echo json_encode([
        'success' => true,
        'message' => 'Código verificado correctamente'
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
