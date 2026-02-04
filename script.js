// script.js - JavaScript de la aplicación

// Variables globales
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const selectedFiles = document.getElementById('selectedFiles');
const actionButtons = document.getElementById('actionButtons');
const startUploadBtn = document.getElementById('startUpload');
const resetBtn = document.getElementById('resetBtn');
const progress = document.getElementById('progress');
const progressBar = document.getElementById('progressBar');
const errorMsg = document.getElementById('errorMsg');
const successMsg = document.getElementById('successMsg');
const resultsContainer = document.getElementById('resultsContainer');
const resultsArea = document.getElementById('resultsArea');

let filesToUpload = [];
const maxFileSize = 100 * 1024 * 1024; // 100MB

// Event listeners principales
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
});

function initializeEventListeners() {
    // Drag and drop
    uploadZone.addEventListener('dragover', handleDragOver);
    uploadZone.addEventListener('dragleave', handleDragLeave);
    uploadZone.addEventListener('drop', handleDrop);
    
    // Click to select
    uploadZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFileInputChange);
    
    // Botones
    startUploadBtn.addEventListener('click', handleStartUpload);
    resetBtn.addEventListener('click', handleReset);
}

// Funciones de Drag & Drop
function handleDragOver(e) {
    e.preventDefault();
    uploadZone.classList.add('dragover');
}

function handleDragLeave() {
    uploadZone.classList.remove('dragover');
}

function handleDrop(e) {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) {
        handleFilesSelect(files);
    }
}

function handleFileInputChange(e) {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
        handleFilesSelect(files);
    }
}

// Función principal para manejar archivos seleccionados
function handleFilesSelect(files) {
    hideMessages();
    filesToUpload = [];
    selectedFiles.innerHTML = '';

    let validFiles = [];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/avi', 'video/mov', 'video/wmv'];
    
    files.forEach(file => {
        // Validar tipo de archivo
        if (!allowedTypes.some(type => type === file.type || file.type.includes(type.split('/')[1]))) {
            showError(`Archivo "${file.name}" no válido. Solo imágenes y videos.`);
            return;
        }

        // Validar tamaño
        if (file.size > maxFileSize) {
            showError(`Archivo "${file.name}" es demasiado grande. Máximo 50MB.`);
            return;
        }

        validFiles.push(file);
    });

    if (validFiles.length === 0) return;

    filesToUpload = validFiles;
    
    // Mostrar archivos seleccionados
    validFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <div class="file-info">
                <div class="file-name">${file.name}</div>
                <div class="file-size">${formatFileSize(file.size)}</div>
            </div>
            <div class="file-status status-pending" id="status-${index}">Pendiente</div>
        `;
        selectedFiles.appendChild(fileItem);
    });

    selectedFiles.style.display = 'block';
    actionButtons.style.display = 'block';
}

// Función para iniciar subida
async function handleStartUpload() {
    if (filesToUpload.length === 0) return;
    
    startUploadBtn.disabled = true;
    startUploadBtn.textContent = 'Subiendo...';
    progress.style.display = 'block';
    hideMessages();
    resultsArea.innerHTML = '';
    resultsContainer.style.display = 'none';

    const formData = new FormData();
    filesToUpload.forEach(file => {
        formData.append('files[]', file);
    });

    // Actualizar estado visual
    filesToUpload.forEach((file, index) => {
        const statusEl = document.getElementById(`status-${index}`);
        statusEl.textContent = 'Subiendo...';
        statusEl.className = 'file-status status-uploading';
    });

    try {
        const response = await fetch('upload_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            processUploadResults(result.files);
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
    } catch (error) {
        console.error('Error:', error);
        handleUploadError();
    }
}

// Procesar resultados de subida
function processUploadResults(files) {
    let successCount = 0;
    
    files.forEach((fileResult, index) => {
        const statusEl = document.getElementById(`status-${index}`);
        
        if (fileResult.success) {
            statusEl.textContent = 'Completado';
            statusEl.className = 'file-status status-success';
            createResultItem(fileResult);
            successCount++;
        } else {
            statusEl.textContent = 'Error';
            statusEl.className = 'file-status status-error';
            statusEl.title = fileResult.error;
        }
    });

    progressBar.style.width = '100%';
    
    setTimeout(() => {
        progress.style.display = 'none';
        startUploadBtn.disabled = false;
        startUploadBtn.textContent = 'Subir archivos';
        
        if (successCount > 0) {
            resultsContainer.style.display = 'block';
            successMsg.textContent = `${successCount} archivo(s) subido(s) correctamente`;
            successMsg.style.display = 'block';
        }
        
        if (successCount < files.length) {
            showError(`${files.length - successCount} archivo(s) fallaron. Revisa los estados.`);
        }
    }, 500);
}

// Manejar error de subida
function handleUploadError() {
    showError('Error al subir archivos. Inténtalo de nuevo.');
    
    filesToUpload.forEach((file, index) => {
        const statusEl = document.getElementById(`status-${index}`);
        statusEl.textContent = 'Error';
        statusEl.className = 'file-status status-error';
    });
    
    startUploadBtn.disabled = false;
    startUploadBtn.textContent = 'Subir archivos';
    progress.style.display = 'none';
}

// Crear elemento de resultado
function createResultItem(fileResult) {
    const resultItem = document.createElement('div');
    resultItem.className = 'result-item';
    resultItem.innerHTML = `
        <div class="result-filename">${fileResult.original_name}</div>
        
        <div class="link-options">
            <div class="link-option selected" data-type="direct" data-url="${fileResult.url}">
                <div class="option-title">Enlace directo</div>
                <div class="option-preview">${fileResult.url}</div>
            </div>
            
            <div class="link-option" data-type="html" data-url="&lt;img src=&quot;${fileResult.url}&quot; alt=&quot;${fileResult.original_name}&quot;&gt;">
                <div class="option-title">HTML completa enlazada</div>
                <div class="option-preview">&lt;img src="${fileResult.url}" alt="${fileResult.original_name}"&gt;</div>
            </div>
            
            <div class="link-option" data-type="html-mini" data-url="&lt;img src=&quot;${fileResult.url}&quot;&gt;">
                <div class="option-title">HTML miniatura enlazada</div>
                <div class="option-preview">&lt;img src="${fileResult.url}"&gt;</div>
            </div>
            
            <div class="link-option" data-type="bbcode" data-url="[img]${fileResult.url}[/img]">
                <div class="option-title">BBCode completa enlazada</div>
                <div class="option-preview">[img]${fileResult.url}[/img]</div>
            </div>
            
            <div class="link-option" data-type="bbcode-mini" data-url="[img]${fileResult.url}[/img]">
                <div class="option-title">BBCode miniatura enlazada</div>
                <div class="option-preview">[img]${fileResult.url}[/img]</div>
            </div>
        </div>
        
        <div class="result-url">${fileResult.url}</div>
        <button class="copy-btn" onclick="copyResultUrl(this)">Copiar enlace</button>
    `;
    resultsArea.appendChild(resultItem);
    
    // Agregar eventos a las opciones
    const options = resultItem.querySelectorAll('.link-option');
    const urlDisplay = resultItem.querySelector('.result-url');
    
    options.forEach(option => {
        option.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            const url = option.dataset.url;
            if (option.dataset.type === 'html' || option.dataset.type === 'html-mini') {
                urlDisplay.innerHTML = url.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
            } else {
                urlDisplay.textContent = url;
            }
        });
    });
}

// Función de reset
function handleReset() {
    filesToUpload = [];
    selectedFiles.innerHTML = '';
    selectedFiles.style.display = 'none';
    actionButtons.style.display = 'none';
    progress.style.display = 'none';
    resultsContainer.style.display = 'none';
    resultsArea.innerHTML = '';
    fileInput.value = '';
    startUploadBtn.disabled = false;
    startUploadBtn.textContent = 'Subir archivos';
    progressBar.style.width = '0%';
    hideMessages();
}

// Funciones de utilidad
function showError(message) {
    errorMsg.textContent = message;
    errorMsg.style.display = 'block';
}

function hideMessages() {
    errorMsg.style.display = 'none';
    successMsg.style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Función global para copiar URL (llamada desde HTML)
function copyResultUrl(button) {
    const resultItem = button.closest('.result-item');
    const selectedOption = resultItem.querySelector('.link-option.selected');
    const url = selectedOption.dataset.url;
    
    let textToCopy = url;
    if (selectedOption.dataset.type === 'html' || selectedOption.dataset.type === 'html-mini') {
        textToCopy = url.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
    }
    
    navigator.clipboard.writeText(textToCopy).then(() => {
        const originalText = button.textContent;
        button.textContent = 'Copiado!';
        button.style.background = '#51cf66';
        button.style.borderColor = '#51cf66';
        
        setTimeout(() => {
            button.textContent = originalText;
            button.style.background = 'transparent';
            button.style.borderColor = '#0066cc';
        }, 2000);
    });
}