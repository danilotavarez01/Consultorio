class CameraManager {
    constructor() {
        this.stream = null;
        this.video = null;
        this.canvas = null;
        this.photoData = null;
    }

    async init(videoElement) {
        this.video = videoElement;

        // Verificar si estamos en un contexto seguro
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            console.warn('La cámara requiere HTTPS para funcionar correctamente en producción');
        }

        const constraints = {
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            },
            audio: false
        };

        try {
            // Asegurarse de que el navegador soporte getUserMedia
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Tu navegador no soporta el acceso a la cámara');
            }

            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.video.srcObject = this.stream;
            
            // Asegurarse de que el video tenga los atributos necesarios
            this.video.setAttribute('autoplay', '');
            this.video.setAttribute('playsinline', ''); // Importante para iOS
            this.video.setAttribute('muted', '');
            
            await this.video.play();
            return true;
        } catch (err) {
            console.error('Error accessing camera:', err);
            this.handleError(err);
            return false;
        }
    }

    handleError(err) {
        let message = 'Error de cámara: ';
        switch (err.name) {
            case 'NotAllowedError':
                message += 'No se otorgaron permisos para la cámara';
                break;
            case 'NotFoundError':
                message += 'No se encontró ninguna cámara';
                break;
            case 'NotReadableError':
                message += 'La cámara está en uso por otra aplicación';
                break;
            case 'OverconstrainedError':
                message += 'No se encontró una cámara compatible';
                break;
            default:
                message += err.message || 'Error desconocido';
        }
        alert(message);
    }

    capture() {
        if (!this.stream || !this.video) return null;
        
        const canvas = document.createElement('canvas');
        canvas.width = this.video.videoWidth;
        canvas.height = this.video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(this.video, 0, 0);
        
        return canvas.toDataURL('image/png');
    }

    stop() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.video) {
            this.video.srcObject = null;
        }
    }

    isActive() {
        return !!this.stream;
    }
}

// Initializing camera functionality when document is ready
$(document).ready(function() {
    const cameraManager = new CameraManager();
    let video = document.createElement('video');
    video.setAttribute('autoplay', '');
    video.setAttribute('playsinline', '');

    // Enable camera button when option is selected
    $('input[name="fotoSource"]').change(function() {
        if (this.value === 'camera') {
            $('#inputFoto').prop('disabled', true);
            $('#btnStartCamera').prop('disabled', false);
        } else {
            $('#inputFoto').prop('disabled', false);
            $('#btnStartCamera').prop('disabled', true);
            cameraManager.stop();
        }
    });

    // Start camera
    $('#btnStartCamera').click(async function() {
        document.getElementById('camera').innerHTML = '';
        document.getElementById('camera').appendChild(video);
        $('#camera').show();

        const success = await cameraManager.init(video);
        if (success) {
            $('#btnCapturePhoto').prop('disabled', false);
            $(this).prop('disabled', true);
        } else {
            $('#fotoCamera').prop('checked', false);
            $('#fotoUpload').prop('checked', true);
            $('input[name="fotoSource"]').trigger('change');
        }
    });

    // Capture photo
    $('#btnCapturePhoto').click(function() {
        if (cameraManager.isActive()) {
            const imgData = cameraManager.capture();
            if (imgData) {
                $('#fotoPreview').attr('src', imgData).show();
                $('#fotoBase64').val(imgData);
                cameraManager.stop();
                $('#camera').hide();
                $('#btnCapturePhoto').prop('disabled', true);
                $('#btnStartCamera').prop('disabled', false);
            }
        }
    });

    // Clean up when modal is closed
    $('#nuevoPacienteModal').on('hidden.bs.modal', function() {
        cameraManager.stop();
        $('#camera').hide();
        $('#fotoPreview').hide();
        $('#fotoBase64').val('');
        $('#inputFoto').val('');
        $('#btnCapturePhoto').prop('disabled', true);
        $('#btnStartCamera').prop('disabled', false);
    });

    // Preview uploaded image
    $('#inputFoto').change(function() {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#fotoPreview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
