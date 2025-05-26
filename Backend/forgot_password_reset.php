<?php
// Limpiar cualquier output previo y suprimir errores
ob_clean();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de las bases de datos
$servername = "localhost";
$username = "root";
$password = "";
$database_teckets = "teckets";
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
    $new_password = isset($input['new_password']) ? $input['new_password'] : '';

    if (empty($email) || empty($code) || empty($new_password)) {
        throw new Exception('Todos los campos son requeridos');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    if (!preg_match('/^\d{4}$/', $code)) {
        throw new Exception('El código debe ser de 4 dígitos');
    }

    // Validar fortaleza de la contraseña
    if (strlen($new_password) < 8) {
        throw new Exception('La contraseña debe tener al menos 8 caracteres');
    }

    if (!preg_match('/[A-Z]/', $new_password)) {
        throw new Exception('La contraseña debe contener al menos una letra mayúscula');
    }

    if (!preg_match('/[a-z]/', $new_password)) {
        throw new Exception('La contraseña debe contener al menos una letra minúscula');
    }

    if (!preg_match('/[0-9]/', $new_password) && !preg_match('/[^a-zA-Z0-9]/', $new_password)) {
        throw new Exception('La contraseña debe contener al menos un número o símbolo');
    }

    // Conectar a la base de datos tokens para verificar el código
    $conn_tokens = new mysqli($servername, $username, $password, $database_tokens);
    if ($conn_tokens->connect_error) {
        throw new Exception('Error de conexión a la base de datos de tokens');
    }
    $conn_tokens->set_charset("utf8");

    // Verificar el código una vez más
    $stmt = $conn_tokens->prepare("SELECT ID FROM compare WHERE correo = ? AND token = ?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Código de verificación incorrecto o expirado');
    }

    $stmt->close();

    // Conectar a la base de datos teckets para actualizar la contraseña
    $conn_teckets = new mysqli($servername, $username, $password, $database_teckets);
    if ($conn_teckets->connect_error) {
        throw new Exception('Error de conexión a la base de datos de usuarios');
    }
    $conn_teckets->set_charset("utf8");

    // Actualizar la contraseña (sin encriptar, como está en el sistema actual)
    $stmt = $conn_teckets->prepare("UPDATE usuarios SET contrasena = ? WHERE correo = ?");
    $stmt->bind_param("ss", $new_password, $email);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar la contraseña');
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('No se pudo actualizar la contraseña. Usuario no encontrado.');
    }

    $stmt->close();
    $conn_teckets->close();

    // Eliminar el código usado de la tabla tokens
    $stmt = $conn_tokens->prepare("DELETE FROM compare WHERE correo = ? AND token = ?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $stmt->close();
    $conn_tokens->close();

    echo json_encode([
        'success' => true,
        'message' => 'Contraseña actualizada correctamente'
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
