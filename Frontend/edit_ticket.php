<?php
// Database configuration
$host = 'localhost';
$dbname = 'teckets';
$username = 'root'; // Change as needed
$password = ''; // Change as needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get ticket ID from URL
$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch ticket data FIRST (before processing form)
try {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        die("Ticket no encontrado");
    }
} catch(PDOException $e) {
    die("Error al obtener ticket: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nucontrol = $_POST['nucontrol'];
        $nombrealumno = $_POST['nombrealumno'];
        $correo = $_POST['correo'];
        $lugar = $_POST['lugar'];
        $asunto = $_POST['asunto'];
        $estado = $_POST['estado'];
        $descripcion = $_POST['descripcion'];
        
        // Handle image upload
        $imagen_blob = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/'
;
            
            // Verificar que la carpeta uploads existe
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Validar tipo de archivo
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['imagen']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                // Obtener extensión del archivo
                $file_extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                
                // Generar nombre único para el archivo
                $new_filename = 'ticket_' . $ticket_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Mover archivo a la carpeta uploads
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_path)) {
                    // Eliminar imagen anterior si existe
                    if ($ticket['imagen'] && file_exists($upload_dir . $ticket['imagen'])) {
                        unlink($upload_dir . $ticket['imagen']);
                    }
                    
                    // Guardar solo el nombre del archivo en la base de datos como BLOB
                    $imagen_blob = $new_filename;
                } else {
                    $error_message = "Error al subir la imagen. Verifique los permisos de la carpeta uploads.";
                }
            } else {
                $error_message = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.";
            }
        }
        
        // Update query
        if ($imagen_blob) {
            $sql = "UPDATE tickets SET nucontrol = ?, nombrealumno = ?, correo = ?, lugar = ?, asunto = ?, estado = ?, descripcion = ?, imagen = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nucontrol, $nombrealumno, $correo, $lugar, $asunto, $estado, $descripcion, $imagen_blob, $ticket_id]);
        } else {
            $sql = "UPDATE tickets SET nucontrol = ?, nombrealumno = ?, correo = ?, lugar = ?, asunto = ?, estado = ?, descripcion = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nucontrol, $nombrealumno, $correo, $lugar, $asunto, $estado, $descripcion, $ticket_id]);
        }
        
        // Refresh ticket data after update
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $success_message = "Ticket actualizado exitosamente";
    } catch(PDOException $e) {
        $error_message = "Error al actualizar: " . $e->getMessage();
    }
}

// Get current image filename from BLOB
$current_image = $ticket['imagen'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ticket</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.5;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
        }
        
        .header {
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .back-link {
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link:hover {
            color: #495057;
        }
        
        .title {
            color: #17a2b8;
            font-size: 20px;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .ticket-id {
            margin-bottom: 30px;
            font-size: 14px;
            color: #495057;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 25px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        label {
            font-size: 14px;
            color: #495057;
            margin-bottom: 8px;
            font-weight: 400;
        }
        
        input[type="text"],
        input[type="email"],
        select,
        textarea {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            color: #495057;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        select {
            cursor: pointer;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }
        
        .image-section {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
        }
        
        .image-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .current-image-container {
            display: flex;
            flex-direction: column;
        }
        
        .image-preview {
            width: 100%;
            height: 200px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .no-image {
            color: #6c757d;
            font-size: 14px;
            text-align: center;
        }
        
        .file-input-container {
            display: flex;
            flex-direction: column;
        }
        
        input[type="file"] {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            margin-top: 8px;
        }
        
        .btn-container {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        .btn {
            padding: 8px 20px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            border-color: #545b62;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .form-row,
            .image-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .container {
                margin: 0;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .header {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="/Proyecto-Tecket/Frontend/admin.html" class="back-link">← Volver al panel</a>
            <div class="title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Editar Ticket
            </div>
        </div>
        
        <div class="form-container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <div class="ticket-id">
                ID del Ticket: <?php echo htmlspecialchars($ticket['id']); ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nucontrol">No. Control</label>
                        <input type="text" id="nucontrol" name="nucontrol" 
                               value="<?php echo htmlspecialchars($ticket['nucontrol']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombrealumno">Nombre Completo</label>
                        <input type="text" id="nombrealumno" name="nombrealumno" 
                               value="<?php echo htmlspecialchars($ticket['nombrealumno']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" id="correo" name="correo" 
                               value="<?php echo htmlspecialchars($ticket['correo']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lugar">Área del problema</label>
                        <select id="lugar" name="lugar" required>
                            <option value="">Seleccionar área</option>
                            <option value="Edificio 200" <?php echo $ticket['lugar'] === 'Edificio 200' ? 'selected' : ''; ?>>Edificio 200</option>
                            <option value="Edificio 300" <?php echo $ticket['lugar'] === 'Edificio 300' ? 'selected' : ''; ?>>Edificio 300</option>
                            <option value="Edificio 400" <?php echo $ticket['lugar'] === 'Edificio 400' ? 'selected' : ''; ?>>Edificio 400</option>
                            <option value="Laboratorios" <?php echo $ticket['lugar'] === 'Laboratorios' ? 'selected' : ''; ?>>Laboratorios</option>
                            <option value="Biblioteca" <?php echo $ticket['lugar'] === 'Biblioteca' ? 'selected' : ''; ?>>Biblioteca</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="asunto">Tipo de falla</label>
                        <select id="asunto" name="asunto" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="Mobiliario" <?php echo $ticket['asunto'] === 'Mobiliario' ? 'selected' : ''; ?>>Mobiliario</option>
                            <option value="Plomería" <?php echo $ticket['asunto'] === 'Plomería' ? 'selected' : ''; ?>>Plomería</option>
                            <option value="Eléctrico" <?php echo $ticket['asunto'] === 'Eléctrico' ? 'selected' : ''; ?>>Eléctrico</option>
                            <option value="Limpieza" <?php echo $ticket['asunto'] === 'Limpieza' ? 'selected' : ''; ?>>Limpieza</option>
                            <option value="Tecnología" <?php echo $ticket['asunto'] === 'Tecnología' ? 'selected' : ''; ?>>Tecnología</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="pendiente" <?php echo $ticket['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="proceso" <?php echo $ticket['estado'] === 'proceso' ? 'selected' : ''; ?>>En Proceso</option>
                            <option value="resuelto" <?php echo $ticket['estado'] === 'resuelto' ? 'selected' : ''; ?>>Resuelto</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($ticket['descripcion']); ?></textarea>
                    </div>
                </div>
                
                <div class="image-section">
                    <div class="image-row">
                        <div class="current-image-container">
                            <label>Imagen actual</label>
                            <div class="image-preview">
                                <?php if ($current_image): ?>
                                    <?php if (file_exists('../uploads/' . $current_image)): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($current_image); ?>" alt="Imagen actual">
                                    <?php else: ?>
                                        <div class="no-image">
                                            📁 Archivo: <?php echo htmlspecialchars($current_image); ?>
                                            <br><small style="color: #999;">Imagen no encontrada en uploads/</small>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="no-image">Sin imagen</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="file-input-container">
                            <label for="imagen">Cambiar imagen (opcional)</label>
                            <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/jpg,image/png,image/gif">
                            <small style="color: #666; margin-top: 5px; display: block;">
                                Formatos permitidos: JPG, PNG, GIF (máx. 5MB)
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="btn-container">
                    <a href="/Proyecto-Tecket/Frontend/admin.html" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Auto-hide success messages
        setTimeout(function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 300);
            }
        }, 3000);

        // Redirect after successful update
        <?php if (isset($success_message)): ?>
        setTimeout(function() {
            window.location.href = '/Proyecto-Tecket/Frontend/admin.html';
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>