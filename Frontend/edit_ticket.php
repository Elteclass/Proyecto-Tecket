<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ticket - TECKET</title>
    <link rel="stylesheet" href="CSS/edit_ticket.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <a href="admin.html" class="back-link">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Volver al panel
                </a>
                <h1><ion-icon name="create-outline"></ion-icon> Editar Ticket</h1>
            </div>
        </div>

        <div class="edit-card">
            <div class="ticket-id">
                <span>ID del Ticket:</span>
                <strong id="ticket-id"></strong>
            </div>

            <form id="edit-form" action="../Backend/update_ticket.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nucontrol">No. Control</label>
                        <input type="text" id="nucontrol" name="nucontrol" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" id="correo" name="correo" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="area">Área del problema</label>
                        <select name="area" id="area" class="form-select" required>
                            <option value="Entrada peatonal">Entrada peatonal</option>
                            <option value="Edificio academico">Edificio académico</option>
                            <option value="Edificio Administrativo">Edificio Administrativo</option>
                            <option value="Edificio 100">Edificio 100</option>
                            <option value="Edificio 200">Edificio 200</option>
                            <option value="Edificio 300">Edificio 300</option>
                            <option value="Edificio 500">Edificio 500</option>
                            <option value="Edificio 600">Edificio 600</option>
                            <option value="Edificio 900">Edificio 900</option>
                            <option value="Cafeteria">Cafetería</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="failure">Tipo de falla</label>
                        <select name="failure" id="failure" class="form-select" required>
                            <option value="Electricidad">Electricidad</option>
                            <option value="Mobiliario">Mobiliario</option>
                            <option value="Plomería">Plomería</option>
                            <option value="Limpieza">Limpieza</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="pending">Pendiente</option>
                            <option value="in-progress">En Proceso</option>
                            <option value="resolved">Resuelto</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-textarea" required></textarea>
                </div>
                
                <div class="image-section">
                    <div class="current-image">
                        <label>Imagen actual</label>
                        <div class="image-container">
                            <img id="current-image" src="/placeholder.svg" alt="Imagen del problema">
                            <div class="no-image" id="no-image-message">No hay imagen</div>
                        </div>
                    </div>
                    
                    <div class="new-image">
                        <label>Cambiar imagen (opcional)</label>
                        <div class="upload-area">
                            <div class="upload-content">
                                <img src="IMG/subir-foto.svg" alt="Subir imagen">
                                <p>Haz clic para subir una nueva imagen</p>
                                <span class="upload-info">
                                    <ion-icon name="information-circle-outline"></ion-icon>
                                    JPG o PNG, tamaño máx: 500KB
                                </span>
                            </div>
                            <input id="upload-image" name="imagen" class="upload-input" type="file" accept="image/png, image/jpg, image/jpeg">
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="remove-image" name="remove_image">
                            <label for="remove-image">Eliminar imagen actual</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="cancel-btn" class="btn-cancel">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notificación -->
    <div id="notification" class="notification"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="JS/edit_ticket.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>