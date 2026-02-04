<?php
// api_upload.php - Endpoint para subir archivos usando API Key

require_once 'config.php';
require_once 'functions.php';
require_once 'api_config.php';
require_once 'api_functions.php';

// Configurar CORS - IMPORTANTE: Debe ir antes que cualquier otra cosa
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-API-Key");
header("Access-Control-Max-Age: 86400");

// Manejar peticiones preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir método POST para subidas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode($apiResponses['error_no_file']);
    http_response_code(405);
    exit;
}

// Inicializar aplicación
initializeApp($uploadDir, $databaseFile);

// Obtener API Key del header o parámetro
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_POST['api_key'] ?? $_GET['api_key'] ?? null;

if (!$apiKey) {
    header('Content-Type: application/json');
    echo json_encode($apiResponses['error_no_key']);
    http_response_code(401);
    exit;
}

// Validar API Key
$validation = validateApiKey($apiKeysFile, $apiKey);
if (!$validation['valid']) {
    header('Content-Type: application/json');
    echo json_encode($apiResponses['error_invalid_key']);
    http_response_code(403);
    exit;
}

// Obtener email del usuario de la API Key
$userEmail = $validation['key_data']['user_email'] ?? 'unknown';

// Verificar rate limiting con el límite correcto según el usuario
$userRateLimit = getRateLimit($userEmail);
$rateLimitCheck = checkRateLimit($rateLimitFile, $apiKey, $userRateLimit);

if (!$rateLimitCheck['allowed']) {
    header('Content-Type: application/json');
    echo json_encode($apiResponses['error_rate_limit']);
    http_response_code(429);
    exit;
}

// Verificar que se envió un archivo
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    echo json_encode($apiResponses['error_no_file']);
    http_response_code(400);
    exit;
}

$file = $_FILES['file'];

// Validar tipo de archivo
if (!in_array($file['type'], $allowedTypes)) {
    header('Content-Type: application/json');
    echo json_encode($apiResponses['error_file_type']);
    http_response_code(400);
    exit;
}

// Validar tamaño
if ($file['size'] > $maxFileSize) {
    header('Content-Type: application/json');
    echo json_encode($apiResponses['error_file_size']);
    http_response_code(400);
    exit;
}

// Generar nombre único
$fileName = generateUniqueFileName($file['name'], $uploadDir);
$filePath = $uploadDir . $fileName;

// Mover archivo
if (move_uploaded_file($file['tmp_name'], $filePath)) {
    // Generar URL
    $fileUrl = generateFileUrl($filePath);
    
    // Detectar tipo de archivo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileType = detectFileType($file['type'], $extension);
    
    // Guardar en base de datos
    $fileData = [
        'id' => uniqid(),
        'original_name' => $file['name'],
        'file_name' => $fileName,
        'file_path' => $filePath,
        'file_url' => $fileUrl,
        'file_type' => $fileType,
        'mime_type' => $file['type'],
        'extension' => $extension,
        'file_size' => $file['size'],
        'upload_date' => date('Y-m-d H:i:s'),
        'uploaded_via' => 'api',
        'api_key_used' => substr($apiKey, 0, 8) . '...',
        'user_email' => $userEmail,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $database = loadDatabase($databaseFile);
    $database[] = $fileData;
    saveDatabase($databaseFile, $database);
    
    // Respuesta exitosa
    $response = [
        'status' => 'success',
        'message' => 'File uploaded successfully',
        'data' => [
            'url' => $fileUrl,
            'direct_url' => $fileUrl,
            'filename' => $fileName,
            'original_name' => $file['name'],
            'size' => formatBytes($file['size']),
            'size_bytes' => $file['size'],
            'type' => $fileType,
            'mime_type' => $file['type'],
            'extension' => $extension,
            'emoji' => getFileEmoji($fileType),
            'upload_date' => $fileData['upload_date'],
            'formats' => [
                'direct' => $fileUrl,
                'html' => '<a href="' . $fileUrl . '">' . $file['name'] . '</a>',
                'bbcode' => '[url=' . $fileUrl . ']' . $file['name'] . '[/url]',
                'markdown' => '[' . $file['name'] . '](' . $fileUrl . ')'
            ]
        ],
        'rate_limit' => [
            'remaining' => $rateLimitCheck['remaining'] - 1,
            'limit' => $userRateLimit,
            'reset_in' => 60
        ]
    ];
    
    // Headers de rate limiting
    header("X-RateLimit-Limit: $userRateLimit");
    header("X-RateLimit-Remaining: " . ($rateLimitCheck['remaining'] - 1));
    header("X-RateLimit-Reset: " . (time() + 60));
    header('Content-Type: application/json');
    
    echo json_encode($response);
    http_response_code(200);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save file',
        'code' => 500
    ]);
    http_response_code(500);
}
?>