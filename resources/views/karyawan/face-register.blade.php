@extends('layouts.app')

@section('title', 'Daftar Wajah')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        @if($descriptorCount > 0)
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Wajah Anda sudah terdaftar ({{ $descriptorCount }} data). Jika ingin mendaftar ulang, proses di bawah akan mengganti data lama.
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div id="status-message" class="alert alert-info">
                    <i class="bi bi-hourglass-split"></i> Memuat model face detection...
                </div>

                <div class="webcam-container mb-3">
                    <video id="video" autoplay muted playsinline></video>
                    <canvas id="overlay"></canvas>
                </div>

                <div id="capture-section" style="display: none;">
                    <p class="text-muted">Ambil <strong>3 foto</strong> wajah dari sudut berbeda untuk hasil terbaik.</p>

                    <div id="captured-faces" class="d-flex justify-content-center gap-2 mb-3 flex-wrap"></div>

                    <div class="d-flex justify-content-center gap-2">
                        <button id="btn-capture" class="btn btn-dark" onclick="captureFace()">
                            <i class="bi bi-camera"></i> Ambil Foto (<span id="capture-count">0</span>/3)
                        </button>
                        <button id="btn-save" class="btn btn-success" onclick="saveDescriptors()" style="display: none;">
                            <i class="bi bi-check-lg"></i> Simpan Wajah
                        </button>
                        <button id="btn-reset" class="btn btn-outline-secondary" onclick="resetCaptures()" style="display: none;">
                            <i class="bi bi-arrow-clockwise"></i> Ulang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const video = document.getElementById('video');
const overlay = document.getElementById('overlay');
const statusMessage = document.getElementById('status-message');
const captureSection = document.getElementById('capture-section');

let descriptors = [];
let isModelLoaded = false;

async function loadModels() {
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/';
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        isModelLoaded = true;
        statusMessage.className = 'alert alert-success';
        statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> Model berhasil dimuat. Silakan posisikan wajah di depan kamera.';
        captureSection.style.display = 'block';
        startDetection();
    } catch (error) {
        statusMessage.className = 'alert alert-danger';
        statusMessage.innerHTML = '<i class="bi bi-x-circle"></i> Gagal memuat model: ' + error.message;
    }
}

async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' }
        });
        video.srcObject = stream;
    } catch (error) {
        statusMessage.className = 'alert alert-danger';
        statusMessage.innerHTML = '<i class="bi bi-camera-video-off"></i> Tidak bisa mengakses kamera. Pastikan izin kamera diberikan.';
    }
}

async function startDetection() {
    const canvas = overlay;
    const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
    faceapi.matchDimensions(canvas, displaySize);

    setInterval(async () => {
        if (!isModelLoaded) return;
        const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks();
        const resized = faceapi.resizeResults(detections, displaySize);
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
        faceapi.draw.drawDetections(canvas, resized);
        faceapi.draw.drawFaceLandmarks(canvas, resized);
    }, 200);
}

async function captureFace() {
    if (descriptors.length >= 3) return;

    statusMessage.className = 'alert alert-info';
    statusMessage.innerHTML = '<i class="bi bi-hourglass-split"></i> Mendeteksi wajah...';

    const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        statusMessage.className = 'alert alert-warning';
        statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Wajah tidak terdeteksi. Pastikan wajah terlihat jelas.';
        return;
    }

    descriptors.push(Array.from(detection.descriptor));

    // Capture thumbnail
    const thumbCanvas = document.createElement('canvas');
    thumbCanvas.width = 100;
    thumbCanvas.height = 100;
    const ctx = thumbCanvas.getContext('2d');
    const box = detection.detection.box;
    ctx.drawImage(video, box.x, box.y, box.width, box.height, 0, 0, 100, 100);

    const img = document.createElement('img');
    img.src = thumbCanvas.toDataURL();
    img.className = 'rounded border';
    img.style.width = '80px';
    img.style.height = '80px';
    img.style.objectFit = 'cover';
    document.getElementById('captured-faces').appendChild(img);

    document.getElementById('capture-count').textContent = descriptors.length;

    statusMessage.className = 'alert alert-success';
    statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> Foto ' + descriptors.length + '/3 berhasil diambil!';

    if (descriptors.length >= 3) {
        document.getElementById('btn-capture').disabled = true;
        document.getElementById('btn-save').style.display = 'inline-block';
        document.getElementById('btn-reset').style.display = 'inline-block';
        statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> 3 foto berhasil. Klik "Simpan Wajah" untuk menyimpan.';
    }
}

async function saveDescriptors() {
    statusMessage.className = 'alert alert-info';
    statusMessage.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan data wajah...';

    try {
        const response = await fetch('{{ route("karyawan.face.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ descriptors: descriptors }),
        });

        const data = await response.json();

        if (response.ok) {
            statusMessage.className = 'alert alert-success';
            statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> ' + data.message + ' Anda bisa langsung melakukan absensi.';
            document.getElementById('btn-save').style.display = 'none';
        } else {
            statusMessage.className = 'alert alert-danger';
            statusMessage.innerHTML = '<i class="bi bi-x-circle"></i> Gagal: ' + data.message;
        }
    } catch (error) {
        statusMessage.className = 'alert alert-danger';
        statusMessage.innerHTML = '<i class="bi bi-x-circle"></i> Terjadi kesalahan: ' + error.message;
    }
}

function resetCaptures() {
    descriptors = [];
    document.getElementById('captured-faces').innerHTML = '';
    document.getElementById('capture-count').textContent = '0';
    document.getElementById('btn-capture').disabled = false;
    document.getElementById('btn-save').style.display = 'none';
    document.getElementById('btn-reset').style.display = 'none';
    statusMessage.className = 'alert alert-info';
    statusMessage.innerHTML = '<i class="bi bi-info-circle"></i> Data direset. Silakan ambil foto ulang.';
}

startCamera().then(loadModels);
</script>
@endpush
