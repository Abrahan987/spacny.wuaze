<?php
// functions.php - Funciones auxiliares

// Función para cargar la base de datos
function loadDatabase($file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

// Función para guardar en la base de datos
function saveDatabase($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Función para obtener la extensión del archivo
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Función para generar nombre único
function generateUniqueFileName($originalName, $uploadDir) {
    $extension = getFileExtension($originalName);
    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
    
    $counter = 1;
    $fileName = $baseName . '.' . $extension;
    
    while (file_exists($uploadDir . $fileName)) {
        $fileName = $baseName . '_' . $counter . '.' . $extension;
        $counter++;
    }
    
    return $fileName;
}

// Función para inicializar directorios
function initializeApp($uploadDir, $databaseFile) {
    // Crear directorio de subidas si no existe
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Crear base de datos JSON si no existe
    if (!file_exists($databaseFile)) {
        file_put_contents($databaseFile, json_encode([], JSON_PRETTY_PRINT));
    }
}

// Función para formatear tamaño de archivo
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Función para generar URL completa
function generateFileUrl($filePath) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['REQUEST_URI']);
    return $protocol . $host . $scriptDir . '/' . $filePath;
}

// Función para detectar el tipo de archivo
function detectFileType($mimeType, $extension) {
    // Imágenes
    if (strpos($mimeType, 'image/') === 0) {
        return 'image';
    }
    
    // Videos
    if (strpos($mimeType, 'video/') === 0) {
        return 'video';
    }
    
    // Audio
    if (strpos($mimeType, 'audio/') === 0) {
        return 'audio';
    }
    
    // Documentos
    $docExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'odt', 'ods', 'odp'];
    if (in_array($extension, $docExtensions) || strpos($mimeType, 'application/pdf') !== false) {
        return 'document';
    }
    
    // Comprimidos
    $compressedExtensions = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
    if (in_array($extension, $compressedExtensions)) {
        return 'compressed';
    }
    
    // APK
    if ($extension === 'apk' || strpos($mimeType, 'android.package') !== false) {
        return 'apk';
    }
    
    // Código
    $codeExtensions = ['html', 'css', 'js', 'json', 'xml', 'php', 'py', 'java', 'cpp', 'c', 'cs', 'go', 'rb', 'sql'];
    if (in_array($extension, $codeExtensions)) {
        return 'code';
    }
    
    // Otros
    return 'file';
}

// Función para obtener emoji según tipo de archivo
function getFileEmoji($fileType) {
    $emojis = [
        'image' => '🖼️',
        'video' => '🎥',
        'audio' => '🎵',
        'document' => '📄',
        'compressed' => '📦',
        'apk' => '📱',
        'code' => '💻',
        'file' => '📁'
    ];
    
    return $emojis[$fileType] ?? '📄';
}
?>