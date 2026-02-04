<?php

// index.php - Interfaz principal limpia

require_once 'config.php';

require_once 'functions.php';

require_once 'auth_config.php';

require_once 'auth_functions.php';

require_once 'api_config.php';

require_once 'api_functions.php';

// Inicializar aplicación

initializeApp($uploadDir, $databaseFile);

// Verificar login solo para API

$isLoggedIn = isLoggedIn();

if ($isLoggedIn) {

    checkSessionTimeout();

}

$userEmail = $isLoggedIn ? getCurrentUser() : null;

$userKeys = $isLoggedIn ? getUserApiKeys($apiKeysFile, $userEmail) : [];

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Subir archivo</title>

    <link rel="stylesheet" href="styles.css">

    <style>

        .tabs {

            display: flex;

            background: #111;

            border-radius: 12px 12px 0 0;

            margin-top: 20px;

            border: 1px solid #333;

            border-bottom: none;

        }

        .tab-button {

            flex: 1;

            background: transparent;

            color: #888;

            border: none;

            padding: 16px;

            font-size: 14px;

            cursor: pointer;

            transition: all 0.3s;

            border-radius: 12px 12px 0 0;

        }

        .tab-button.active {

            background: #0066cc;

            color: #fff;

        }

        .tab-content {

            background: #111;

            border: 1px solid #333;

            border-radius: 0 0 12px 12px;

            padding: 24px;

            display: none;

        }

        .tab-content.active {

            display: block;

        }

        .login-prompt {

            text-align: center;

            padding: 40px 20px;

        }

        .login-prompt-icon {

            font-size: 64px;

            margin-bottom: 20px;

        }

        .login-btn {

            background: #0066cc;

            color: #fff;

            border: none;

            padding: 12px 24px;

            border-radius: 6px;

            font-size: 14px;

            text-decoration: none;

            display: inline-block;

        }

        .user-info {

            background: #1a1a1a;

            border: 1px solid #333;

            border-radius: 6px;

            padding: 12px 16px;

            margin-bottom: 20px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }

        .logout-btn {

            background: #333;

            color: #ccc;

            border: none;

            padding: 6px 12px;

            border-radius: 4px;

            font-size: 12px;

            cursor: pointer;

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

        .create-btn {

            background: #0066cc;

            color: #fff;

            border: none;

            padding: 10px 20px;

            border-radius: 6px;

            font-size: 14px;

            cursor: pointer;

        }

        .key-item {

            background: #1a1a1a;

            border: 1px solid #333;

            border-radius: 8px;

            padding: 16px;

            margin-bottom: 12px;

        }

        .key-value {

            background: #000;

            border: 1px solid #333;

            border-radius: 6px;

            padding: 12px;

            margin: 12px 0;

            font-family: monospace;

            font-size: 12px;

            color: #0066cc;

            word-break: break-all;

        }

        .copy-key-btn {

            background: transparent;

            color: #0066cc;

            border: 1px solid #0066cc;

            padding: 6px 12px;

            border-radius: 4px;

            font-size: 12px;

            cursor: pointer;

        }

        .delete-key-btn {

            background: transparent;

            color: #ff6b6b;

            border: 1px solid #ff6b6b;

            padding: 6px 12px;

            border-radius: 4px;

            font-size: 12px;

            cursor: pointer;

            margin-left: 8px;

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

    <div class="container">

        <h1 class="title">Subir archivo</h1>

        <p class="subtitle">Convierte tus archivos en enlaces directos (imágenes, videos, documentos, audio, APKs, etc.)</p>

        

        <div class="tabs">

            <button class="tab-button active" onclick="showTab('upload')">📁 Subir Archivos</button>

            <button class="tab-button" onclick="showTab('api')">🔑 API Keys</button>

        </div>

        

        <!-- TAB: Subir Archivos -->

        <div id="upload-tab" class="tab-content active">

            <form id="uploadForm" enctype="multipart/form-data">

                <div class="upload-zone" id="uploadZone">

                    <div class="upload-icon">📁</div>

                    <div class="upload-text">Arrastra tus archivos aquí</div>

                    <div class="upload-subtext">Cualquier tipo de archivo (imágenes, videos, documentos, APKs, etc.)</div>

                    <input type="file" class="file-input" id="fileInput" name="files[]" multiple>

                </div>

                

                <div class="selected-files" id="selectedFiles"></div>

                

                <div style="text-align: center; margin-top: 20px; display: none;" id="actionButtons">

                    <button type="button" class="upload-btn" id="startUpload">Subir archivos</button>

                    <button type="button" class="reset-btn" id="resetBtn">Reiniciar</button>

                </div>

                

                <div class="progress" id="progress">

                    <div class="progress-bar" id="progressBar"></div>

                </div>

                

                <div class="error-msg" id="errorMsg"></div>

                <div class="success-msg" id="successMsg"></div>

            </form>

            

            <div class="results-container" id="resultsContainer">

                <h3 style="color: #ccc; font-size: 16px; margin-bottom: 16px;">Enlaces generados:</h3>

                <div id="resultsArea"></div>

            </div>

        </div>

        

        <!-- TAB: API Keys -->

        <div id="api-tab" class="tab-content">

            <?php if (!$isLoggedIn): ?>

                <div class="login-prompt">

                    <div class="login-prompt-icon">🔐</div>

                    <h2 style="font-size: 20px; margin-bottom: 10px;">Inicia sesión para gestionar tus API Keys</h2>

                    <p style="font-size: 14px; color: #888; margin-bottom: 24px;">Crea una cuenta gratuita para generar hasta 3 API Keys</p>

                    <a href="login.php" class="login-btn">Iniciar Sesión / Registrarse</a>

                </div>

            <?php else: ?>

                <div class="user-info">

                    <span>👤 <?php echo htmlspecialchars($userEmail); ?></span>

                    <button class="logout-btn" onclick="logoutUser()">Cerrar Sesión</button>

                </div>

                

                <h2 style="font-size: 18px; margin-bottom: 8px;">

                    🔑 API Keys Manager 

                    <?php if (isAdmin($userEmail)): ?>

                        <span style="background: #ffd43b; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px;">👑 ADMIN</span>

                    <?php endif; ?>

                </h2>

                <p style="font-size: 14px; color: #888; margin-bottom: 20px;">Genera API Keys para usar con bots de WhatsApp</p>

                

                <div class="limit-info">

                    <?php if (isAdmin($userEmail)): ?>

                        ♾️ Límite: <?php echo count($userKeys); ?> API Keys generadas (Ilimitado para administrador)

                    <?php else: ?>

                        📊 Límite: <?php echo count($userKeys); ?>/<?php echo getApiKeyLimit($userEmail); ?> API Keys generadas

                    <?php endif; ?>

                </div>

                

                <?php if (count($userKeys) < getApiKeyLimit($userEmail)): ?>

                <div style="background: #1a1a1a; border-radius: 8px; padding: 20px; margin-bottom: 20px;">

                    <label style="display: block; font-size: 13px; color: #ccc; margin-bottom: 6px;">Nombre de la API Key:</label>

                    <input type="text" class="form-input" id="keyName" placeholder="Ej: Bot WhatsApp" maxlength="50">

                    <button class="create-btn" id="createKeyBtn" style="margin-top: 12px; width: 100%;">Generar nueva API Key</button>

                </div>

                <?php endif; ?>

                

                <div id="keysList"></div>

            <?php endif; ?>

        </div>

    </div>

    <script src="script.js"></script>

    <script>

        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

        

        function showTab(tabName) {

            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));

            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

            document.getElementById(tabName + '-tab').classList.add('active');

            event.target.classList.add('active');

            

            if (tabName === 'api' && isLoggedIn) {

                loadApiKeys();

            }

        }

        

        <?php if ($isLoggedIn): ?>

        const createKeyBtn = document.getElementById('createKeyBtn');

        const keysList = document.getElementById('keysList');

        const keyNameInput = document.getElementById('keyName');

        if (createKeyBtn) {

            createKeyBtn.addEventListener('click', async function() {

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

                        alert('✅ API Key generada exitosamente!');

                        setTimeout(() => location.reload(), 1000);

                    } else {

                        alert('❌ Error: ' + result.error);

                    }

                } catch (error) {

                    alert('❌ Error al generar API Key');

                }

                createKeyBtn.disabled = false;

                createKeyBtn.textContent = 'Generar nueva API Key';

            });

        }

        async function loadApiKeys() {

            try {

                const formData = new FormData();

                formData.append('action', 'get_keys');

                const response = await fetch('api_manager.php', {method: 'POST', body: formData});

                const result = await response.json();

                if (result.success) displayApiKeys(result.keys);

            } catch (error) {

                console.error('Error:', error);

            }

        }

        function displayApiKeys(keys) {

            if (!keys || keys.length === 0) {

                keysList.innerHTML = '<p style="color: #888; text-align: center; padding: 20px;">No tienes API Keys aún.</p>';

                return;

            }

            keysList.innerHTML = '';

            keys.forEach(key => {

                const div = document.createElement('div');

                div.className = 'key-item';

                div.innerHTML = `

                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">

                        <strong>${key.name}</strong>

                        <span style="font-size: 12px; color: #51cf66;">Activa</span>

                    </div>

                    <div class="key-value">${key.key}</div>

                    <div style="font-size: 11px; color: #666; margin-bottom: 12px;">

                        <span>Creada: ${key.created_at}</span> | <span>Usos: ${key.usage_count || 0}</span>

                    </div>

                    <button class="copy-key-btn" onclick="copyApiKey('${key.key}')">Copiar</button>

                    <button class="delete-key-btn" onclick="deleteApiKey('${key.key}')">Eliminar</button>

                `;

                keysList.appendChild(div);

            });

        }

        function copyApiKey(apiKey) {

            navigator.clipboard.writeText(apiKey).then(() => alert('✅ API Key copiada'));

        }

        async function deleteApiKey(apiKey) {

            if (!confirm('¿Eliminar esta API Key?')) return;

            

            const formData = new FormData();

            formData.append('action', 'delete_key');

            formData.append('key', apiKey);

            

            const response = await fetch('api_manager.php', {method: 'POST', body: formData});

            const result = await response.json();

            

            if (result.success) {

                alert('✅ API Key eliminada');

                setTimeout(() => location.reload(), 1000);

            }

        }

        async function logoutUser() {

            if (!confirm('¿Cerrar sesión?')) return;

            

            const formData = new FormData();

            formData.append('action', 'logout');

            await fetch('api_manager.php', {method: 'POST', body: formData});

            window.location.href = 'login.php';

        }

        if (window.location.hash === '#api' && isLoggedIn) {

            loadApiKeys();

        }

        <?php endif; ?>

    </script>

</body>

</html>