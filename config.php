<?php
// config.php - Configuración general de la aplicación
$uploadDir = 'uploads/';
$databaseFile = 'database.json';
$maxFileSize = 100 * 1024 * 1024; // 100MB (aumentado desde 50MB)

// Tipos de archivos permitidos - AMPLIADO
$allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'];
$allowedVideoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'mpeg', 'mpg', '3gp'];
$allowedAudioTypes = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'wma'];
$allowedDocumentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'odt', 'ods', 'odp'];
$allowedCompressedTypes = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
$allowedCodeTypes = ['html', 'css', 'js', 'json', 'xml', 'php', 'py', 'java', 'cpp', 'c', 'cs', 'go', 'rb', 'sql'];
$allowedOtherTypes = ['apk', 'exe', 'dmg', 'iso', 'torrent'];

// MIME types permitidos
$allowedTypes = [
    // Imágenes
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml', 'image/x-icon',
    
    // Videos
    'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/x-flv', 
    'video/webm', 'video/x-matroska', 'video/mpeg', 'video/3gpp',
    
    // Audio
    'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/flac', 'audio/x-m4a', 'audio/aac', 'audio/x-ms-wma',
    
    // Documentos
    'application/pdf',
    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain', 'text/rtf', 'application/rtf',
    'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.spreadsheet', 
    'application/vnd.oasis.opendocument.presentation',
    
    // Comprimidos
    'application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/x-rar',
    'application/x-7z-compressed', 'application/x-tar', 'application/gzip', 'application/x-bzip2',
    
    // Código
    'text/html', 'text/css', 'text/javascript', 'application/javascript', 'application/json',
    'text/xml', 'application/xml', 'application/x-php', 'text/x-php',
    'text/x-python', 'text/x-java', 'text/x-c', 'text/x-csharp', 'text/x-go', 'text/x-ruby', 'application/sql',
    
    // Otros
    'application/vnd.android.package-archive', // APK
    'application/x-msdownload', 'application/x-msdos-program', // EXE
    'application/x-apple-diskimage', // DMG
    'application/x-iso9660-image', // ISO
    'application/x-bittorrent', // Torrent
    
    // Genéricos (para casos especiales)
    'application/octet-stream'
];
?>