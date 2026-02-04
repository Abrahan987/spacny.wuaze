<?php
// api_functions.php - Funciones para el sistema de API

require_once 'api_config.php';

// Función para generar una API Key única
function generateApiKey($prefix = '', $length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $key = '';
    
    for ($i = 0; $i < $length; $i++) {
        $key .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $prefix . $key;
}

// Función para cargar API Keys existentes
function loadApiKeys($file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

// Función para guardar API Keys
function saveApiKeys($file, $data) {
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Función para crear una nueva API Key
function createApiKey($apiKeysFile, $maxKeys, $userEmail, $name = 'Default') {
    $apiKeys = loadApiKeys($apiKeysFile);
    
    // Obtener el límite correcto según el usuario
    $userLimit = getApiKeyLimit($userEmail);
    
    // Contar API Keys existentes para este usuario
    $userKeys = array_filter($apiKeys, function($key) use ($userEmail) {
        return strtolower($key['user_email']) === strtolower($userEmail);
    });
    
    if (count($userKeys) >= $userLimit) {
        return [
            'success' => false,
            'error' => "Límite alcanzado. Máximo $userLimit API Keys por usuario."
        ];
    }
    
    // Generar nueva API Key
    do {
        $newKey = generateApiKey('sk_', 32);
    } while (apiKeyExists($apiKeys, $newKey));
    
    // Crear registro de la nueva API Key
    $keyData = [
        'id' => uniqid(),
        'key' => $newKey,
        'name' => $name,
        'user_email' => strtolower($userEmail),
        'is_admin' => isAdmin($userEmail),
        'created_at' => date('Y-m-d H:i:s'),
        'last_used' => null,
        'usage_count' => 0,
        'status' => 'active'
    ];
    
    $apiKeys[] = $keyData;
    
    if (saveApiKeys($apiKeysFile, $apiKeys)) {
        return [
            'success' => true,
            'api_key' => $newKey,
            'name' => $name,
            'created_at' => $keyData['created_at']
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Error al generar API Key'
    ];
}

// Función para verificar si una API Key existe
function apiKeyExists($apiKeys, $key) {
    foreach ($apiKeys as $apiKey) {
        if ($apiKey['key'] === $key) {
            return true;
        }
    }
    return false;
}

// Función para validar una API Key
function validateApiKey($apiKeysFile, $key) {
    $apiKeys = loadApiKeys($apiKeysFile);
    
    foreach ($apiKeys as &$apiKey) {
        if ($apiKey['key'] === $key && $apiKey['status'] === 'active') {
            // Actualizar último uso
            $apiKey['last_used'] = date('Y-m-d H:i:s');
            $apiKey['usage_count']++;
            saveApiKeys($apiKeysFile, $apiKeys);
            
            return [
                'valid' => true,
                'key_data' => $apiKey
            ];
        }
    }
    
    return ['valid' => false];
}

// Función para obtener API Keys de un usuario
function getUserApiKeys($apiKeysFile, $userEmail) {
    $apiKeys = loadApiKeys($apiKeysFile);
    
    return array_filter($apiKeys, function($key) use ($userEmail) {
        return isset($key['user_email']) && strtolower($key['user_email']) === strtolower($userEmail);
    });
}

// Función para eliminar una API Key
function deleteApiKey($apiKeysFile, $keyToDelete, $userEmail) {
    $apiKeys = loadApiKeys($apiKeysFile);
    
    $filteredKeys = array_filter($apiKeys, function($key) use ($keyToDelete, $userEmail) {
        return !($key['key'] === $keyToDelete && strtolower($key['user_email']) === strtolower($userEmail));
    });
    
    if (count($filteredKeys) < count($apiKeys)) {
        saveApiKeys($apiKeysFile, array_values($filteredKeys));
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'API Key no encontrada'];
}

// Función para verificar rate limiting
function checkRateLimit($rateLimitFile, $apiKey, $limitPerMinute) {
    $rateLimits = [];
    
    if (file_exists($rateLimitFile)) {
        $content = file_get_contents($rateLimitFile);
        $rateLimits = json_decode($content, true) ?: [];
    }
    
    $now = time();
    $windowStart = $now - 60; // Ventana de 1 minuto
    
    // Limpiar registros antiguos
    if (isset($rateLimits[$apiKey])) {
        $rateLimits[$apiKey] = array_filter($rateLimits[$apiKey], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
    } else {
        $rateLimits[$apiKey] = [];
    }
    
    // Verificar si se ha excedido el límite
    if (count($rateLimits[$apiKey]) >= $limitPerMinute) {
        return ['allowed' => false, 'remaining' => 0];
    }
    
    // Registrar esta petición
    $rateLimits[$apiKey][] = $now;
    
    // Guardar datos actualizados
    file_put_contents($rateLimitFile, json_encode($rateLimits));
    
    return [
        'allowed' => true,
        'remaining' => $limitPerMinute - count($rateLimits[$apiKey])
    ];
}

// Función para formatear respuesta de API
function apiResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Función para configurar headers CORS
function setCorsHeaders($allowedOrigins) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
    header('Access-Control-Max-Age: 86400');
    
    // Responder a preflight OPTIONS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
}
?>