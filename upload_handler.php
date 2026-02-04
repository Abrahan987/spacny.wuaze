<?php
// upload_handler.php - Manejo de subida de archivos
require_once 'config.php';
require_once 'functions.php';

// Solo ejecutar si hay archivos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    
    header('Content-Type: application/json');
    
    // Inicializar aplicación
    initializeApp($uploadDir, $databaseFile);
    
    $response = ['success' => true, 'files' => []];
    $files = $_FILES['files'];
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'size' => $files['size'][$i],
            'error' => $files['error'][$i]
        ];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['files'][] = [
                'original_name' => $file['name'],
                'success' => false,
                'error' => 'Error de subida: ' . $file['error']
            ];
            continue;
        }
        
        // Validar tipo
        if (!in_array($file['type'], $allowedTypes)) {
            $response['files'][] = [
                'original_name' => $file['name'],
                'success' => false,
                'error' => 'Tipo de archivo no permitido'
            ];
            continue;
        }
        
        // Validar tamaño
        if ($file['size'] > $maxFileSize) {
            $response['files'][] = [
                'original_name' => $file['name'],
                'success' => false,
                'error' => 'Archivo demasiado grande (máx 50MB)'
            ];
            continue;
        }
        
        // Generar nombre único
        $fileName = generateUniqueFileName($file['name'], $uploadDir);
        $filePath = $uploadDir . $fileName;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Generar URL
            $fileUrl = generateFileUrl($filePath);
            
            // Guardar en base de datos
            $fileData = [
                'id' => uniqid(),
                'original_name' => $file['name'],
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_url' => $fileUrl,
                'file_type' => strpos($file['type'], 'image/') === 0 ? 'image' : 'video',
                'file_size' => $file['size'],
                'upload_date' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            $database = loadDatabase($databaseFile);
            $database[] = $fileData;
            saveDatabase($databaseFile, $database);
            
            $response['files'][] = [
                'original_name' => $file['name'],
                'success' => true,
                'url' => $fileUrl
            ];
        } else {
            $response['files'][] = [
                'original_name' => $file['name'],
                'success' => false,
                'error' => 'Error al guardar archivo'
            ];
        }
    }
    
    echo json_encode($response);
    exit;
}
?>