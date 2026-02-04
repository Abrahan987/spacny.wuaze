<?php
// api_config.php - Configuración del sistema de API

// Archivo donde se guardarán las API Keys
$apiKeysFile = 'api_keys.json';

// Email del administrador (dueño)
$adminEmail = 'abrahanmoises987@gmail.com';

// Límite máximo de API Keys por usuario (NO aplica al admin)
$maxApiKeys = 2;

// Límite ilimitado para el administrador
$maxApiKeysAdmin = 999;

// Longitud de las API Keys generadas
$apiKeyLength = 32;

// Prefijo para las API Keys (opcional)
$apiKeyPrefix = 'sk_';

// Rate limiting por API Key (requests por minuto)
$rateLimitPerMinute = 100;

// Rate limiting para admin (mayor límite)
$rateLimitPerMinuteAdmin = 1000;

// Archivo para tracking de rate limiting
$rateLimitFile = 'rate_limits.json';

// Headers permitidos para CORS (para usar desde otros dominios)
$allowedOrigins = [
    'https://spacny.wuaze.com',
    'http://localhost',
    // Agregar más dominios según necesites
];

// Configuración de respuestas API
$apiResponses = [
    'success' => [
        'status' => 'success',
        'message' => 'File uploaded successfully'
    ],
    'error_no_key' => [
        'status' => 'error',
        'message' => 'API Key required',
        'code' => 401
    ],
    'error_invalid_key' => [
        'status' => 'error',
        'message' => 'Invalid API Key',
        'code' => 403
    ],
    'error_rate_limit' => [
        'status' => 'error',
        'message' => 'Rate limit exceeded',
        'code' => 429
    ],
    'error_no_file' => [
        'status' => 'error',
        'message' => 'No file provided',
        'code' => 400
    ],
    'error_file_type' => [
        'status' => 'error',
        'message' => 'File type not allowed',
        'code' => 400
    ],
    'error_file_size' => [
        'status' => 'error',
        'message' => 'File too large',
        'code' => 400
    ]
];

// Función para verificar si un usuario es administrador
function isAdmin($email) {
    global $adminEmail;
    return strtolower(trim($email)) === strtolower(trim($adminEmail));
}

// Función para obtener el límite de API Keys según el usuario
function getApiKeyLimit($email) {
    global $maxApiKeys, $maxApiKeysAdmin;
    return isAdmin($email) ? $maxApiKeysAdmin : $maxApiKeys;
}

// Función para obtener el rate limit según el usuario
function getRateLimit($email) {
    global $rateLimitPerMinute, $rateLimitPerMinuteAdmin;
    return isAdmin($email) ? $rateLimitPerMinuteAdmin : $rateLimitPerMinute;
}
?>