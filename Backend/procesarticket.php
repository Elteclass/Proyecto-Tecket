<?php
session_start();
include 'config.php'; // Incluye la configuración de la base de datos

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $nucontrol = $_POST["nucontrol"];
    $nombrealumno = $_POST["nombre"];
    $correo = $_POST["correo"];
    $lugar = $_POST["area"];
    $asunto = $_POST["failure"]; // Tipo de falla como asunto
    $descripcion = $_POST["descripcion"];
    
    // Establecer valores predeterminados
    $estado = "Pendiente"; // Estado inicial del ticket
    $fecha_creacion = date("Y-m-d H:i:s"); // Fecha y hora actual
    
    // Manejar la subida de imagen
    $imagen = NULL; // Valor predeterminado si no hay imagen
    
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
        // Directorio donde se guardarán las imágenes
        $directorio_destino = "../uploads/";
        
        // Crear el directorio si no existe
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }
        
        // Generar un nombre único para la imagen
        $extension = pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION);
        $nombre_archivo = "ticket_" . time() . "_" . rand(1000, 9999) . "." . $extension;
        $ruta_archivo = $directorio_destino . $nombre_archivo;
        
        // Verificar el tamaño del archivo (máximo 500KB = 512000 bytes)
        if ($_FILES["imagen"]["size"] <= 512000) {
            // Verificar el tipo de archivo
            $tipo_permitido = array("image/jpeg", "image/jpg", "image/png");
            if (in_array($_FILES["imagen"]["type"], $tipo_permitido)) {
                // Mover el archivo subido al directorio de destino
                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta_archivo)) {
                    $imagen = $nombre_archivo; // Guardar solo el nombre del archivo en la base de datos
                } else {
                    echo "<script>alert('Error al subir la imagen.'); window.location.href = '../Frontend/ticket.html';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Solo se permiten archivos JPG y PNG.'); window.location.href = '../Frontend/ticket.html';</script>";
                exit();
            }
        } else {
            echo "<script>alert('El tamaño de la imagen no debe exceder 500KB.'); window.location.href = '../Frontend/ticket.html';</script>";
            exit();
        }
    }
    
    // Preparar la consulta SQL para insertar el ticket
    $sql = "INSERT INTO tickets (asunto, lugar, nucontrol, correo, nombrealumno, descripcion, estado, imagen, fecha_creacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $asunto, $lugar, $nucontrol, $correo, $nombrealumno, $descripcion, $estado, $imagen, $fecha_creacion);
    
    if ($stmt->execute()) {
        // Ticket creado exitosamente
        echo "<script>alert('Ticket creado exitosamente.'); window.location.href = '../Frontend/index.html';</script>";
    } else {
        // Error al crear el ticket
        echo "<script>alert('Error al crear el ticket: " . $stmt->error . "'); window.location.href = '../Frontend/ticket.html';</script>";
    }
    
    $stmt->close();
} else {
    // Si no se envió el formulario, redirigir a la página del formulario
    header("Location: ../Frontend/ticket.html");
    exit();
}

$conn->close();
?>
