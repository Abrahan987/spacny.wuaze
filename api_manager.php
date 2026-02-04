<?php
// api_manager.php - Gestor de API Keys para la interfaz web

require_once 'auth_config.php';
require_once 'auth_functions.php';
require_once 'api_config.php';
require_once 'api_functions.php';

// Verificar que el usuario esté logueado
requireLogin();

$userEmail = getCurrentUser();

// Manejar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_key':
            $name = $_POST['name'] ?? 'API Key';
            $result = createApiKey($apiKeysFile, $maxApiKeys, $userEmail, $name);
            echo json_encode($result);
            break;
            
        case 'delete_key':
            $keyToDelete = $_POST['key'] ?? '';
            $result = deleteApiKey($apiKeysFile, $keyToDelete, $userEmail);
            echo json_encode($result);
            break;
            
        case 'get_keys':
            $userKeys = getUserApiKeys($apiKeysFile, $userEmail);
            echo json_encode(['success' => true, 'keys' => array_values($userKeys)]);
            break;
            
        case 'logout':
            logoutUser();
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }
    
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Keys Manager</title>
    <style>
        .api-section {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 24px;
            margin-top: 30px;
        }
        
        .api-title {
            font-size: 18px;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .api-subtitle {
            font-size: 14px;
            color: #888;
            margin-bottom: 24px;
        }
        
        .create-key-form {
            background: #1a1a1a;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            color: #ccc;
            margin-bottom: 6px;
        }
        
        .form-input {
            width: 100%;
            background: #000;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 10px 12px;
            color: #fff;
            font-size: 14px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #0066cc;
        }
        
        .create-btn {
            background: #0066cc;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .create-btn:hover {
            background: #0052a3;
        }
        
        .create-btn:disabled {
            background: #333;
            cursor: not-allowed;
        }
        
        .keys-list {
            display: none;
        }
        
        .key-item {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }
        
        .key-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .key-name {
            font-size: 14px;
            color: #fff;
            font-weight: 500;
        }
        
        .key-status {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            background: #1b2d1b;
            color: #51cf66;
        }
        
        .key-value {
            background: #000;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 12px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 12px;
            color: #0066cc;
            word-break: break-all;
            position: relative;
        }
        
        .key-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 12px;
        }
        
        .key-actions {
            display: flex;
            gap: 8px;
        }
        
        .copy-key-btn {
            background: transparent;
            color: #0066cc;
            border: 1px solid #0066cc;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .copy-key-btn:hover {
            background: #0066cc;
            color: #fff;
        }
        
        .delete-key-btn {
            background: transparent;
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .delete-key-btn:hover {
            background: #ff6b6b;
            color: #fff;
        }
        
        .api-docs {
            background: #0a0a0a;
            border-radius: 6px;
            padding: 16px;
            margin-top: 20px;
        }
        
        .docs-title {
            font-size: 14px;
            color: #0066cc;
            margin-bottom: 12px;
        }
        
        .code-example {
            background: #000;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 12px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 11px;
            color: #ccc;
            overflow-x: auto;
            margin-bottom: 8px;
        }
        
        .limit-info {
            background: #2d2d1b;
            border: 1px solid #5c5c26;
            color: #ffd43b;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="api-section">
        <h2 class="api-title">🔑 API Keys Manager</h2>
        <p class="api-subtitle">Genera API Keys para usar con bots de WhatsApp y otras aplicaciones</p>
        
        <div class="limit-info">
            📊 Límite: <?php echo count($userKeys); ?>/<?php echo $maxApiKeys; ?> API Keys generadas
        </div>
        
        <?php if (count($userKeys) < $maxApiKeys): ?>
        <div class="create-key-form">
            <div class="form-group">
                <label class="form-label">Nombre de la API Key:</label>
                <input type="text" class="form-input" id="keyName" placeholder="Ej: Bot WhatsApp, Mi App, etc." maxlength="50">
            </div>
            <button class="create-btn" id="createKeyBtn">Generar nueva API Key</button>
        </div>
        <?php endif; ?>
        
        <div class="keys-list" id="keysList">
            <!-- Las API Keys aparecerán aquí -->
        </div>
        
        <div class="api-docs">
            <h3 class="docs-title">📚 Cómo usar con tu bot de WhatsApp</h3>
            
            <p style="color: #ccc; font-size: 13px; margin-bottom: 12px;">Endpoint para subir archivos:</p>
            <div class="code-example">POST https://spacny.wuaze.com/api_upload.php</div>
            
            <p style="color: #ccc; font-size: 13px; margin-bottom: 8px;">Ejemplo con cURL:</p>
            <div class="code-example">curl -X POST https://spacny.wuaze.com/api_upload.php \<br>
     -H "X-API-Key: TU_API_KEY" \<br>
     -F "file=@imagen.jpg"</div>
            
            <p style="color: #ccc; font-size: 13px; margin-bottom: 8px;">Respuesta JSON:</p>
            <div class="code-example">{<br>
  "status": "success",<br>
  "message": "File uploaded successfully",<br>
  "data": {<br>
    "url": "https://spacny.wuaze.com/uploads/archivo.jpg",<br>
    "filename": "archivo.jpg",<br>
    "size": "1.5 MB"<br>
  }<br>
}</div>
        </div>
    </div>

    <script>
        const createKeyBtn = document.getElementById('createKeyBtn');
        const keysList = document.getElementById('keysList');
        const keyNameInput = document.getElementById('keyName');

        // Cargar API Keys al iniciar
        loadApiKeys();

        // Evento para crear nueva API Key
        if (createKeyBtn) {
            createKeyBtn.addEventListener('click', createNewApiKey);
        }

        async function createNewApiKey() {
            const name = keyNameInput.value.trim() || 'API Key';
            
            createKeyBtn.disabled = true;
            createKeyBtn.textContent = 'Generando...';

            try {
                const formData = new FormData();
                formData.append('action', 'create_key');
                formData.append('name', name);

                const response = await fetch('api_manager.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    keyNameInput.value = '';
                    loadApiKeys();
                    alert('✅ API Key generada exitosamente!');
                } else {
                    alert('❌ Error: ' + result.error);
                }
            } catch (error) {
                alert('❌ Error al generar API Key');
                console.error(error);
            }

            createKeyBtn.disabled = false;
            createKeyBtn.textContent = 'Generar nueva API Key';
        }

        async function loadApiKeys() {
            try {
                const formData = new FormData();
                formData.append('action', 'get_keys');

                const response = await fetch('api_manager.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success && result.keys.length > 0) {
                    displayApiKeys(result.keys);
                    keysList.style.display = 'block';
                } else {
                    keysList.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading API keys:', error);
            }
        }

        function displayApiKeys(keys) {
            keysList.innerHTML = '';

            keys.forEach(key => {
                const keyElement = document.createElement('div');
                keyElement.className = 'key-item';
                keyElement.innerHTML = `
                    <div class="key-header">
                        <div class="key-name">${key.name}</div>
                        <div class="key-status">Activa</div>
                    </div>
                    
                    <div class="key-value">${key.key}</div>
                    
                    <div class="key-info">
                        <span>Creada: ${key.created_at}</span>
                        <span>Usos: ${key.usage_count}</span>
                    </div>
                    
                    <div class="key-actions">
                        <button class="copy-key-btn" onclick="copyApiKey('${key.key}')">Copiar</button>
                        <button class="delete-key-btn" onclick="deleteApiKey('${key.key}')">Eliminar</button>
                    </div>
                `;
                keysList.appendChild(keyElement);
            });
        }

        function copyApiKey(apiKey) {
            navigator.clipboard.writeText(apiKey).then(() => {
                alert('✅ API Key copiada al portapapeles');
            });
        }

        async function deleteApiKey(apiKey) {
            if (!confirm('¿Estás seguro de eliminar esta API Key? Esta acción no se puede deshacer.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'delete_key');
                formData.append('key', apiKey);

                const response = await fetch('api_manager.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    loadApiKeys();
                    alert('✅ API Key eliminada exitosamente');
                } else {
                    alert('❌ Error al eliminar API Key');
                }
            } catch (error) {
                alert('❌ Error al eliminar API Key');
                console.error(error);
            }
        }
    </script>
</body>
</html>