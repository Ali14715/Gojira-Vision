@extends('layouts.app')

@section('title', 'Absensi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        @if(!auth()->user()->face_registered)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Anda belum mendaftarkan wajah. <a href="{{ route('karyawan.face.register') }}" class="alert-link">Daftar wajah terlebih dahulu.</a>
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div id="status-message" class="alert alert-info">
                        <i class="bi bi-hourglass-split"></i> Memuat model face recognition...
                    </div>

                    <div class="webcam-container mb-3">
                        <video id="video" autoplay muted playsinline></video>
                        <canvas id="overlay"></canvas>
                    </div>

                    <div id="recognition-result" class="mb-3" style="display: none;">
                        <div class="card border-success">
                            <div class="card-body">
                                <h5 id="recognized-name" class="text-success"></h5>
                                <p id="recognized-distance" class="text-muted mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        @if($todayAttendance && $todayAttendance->clock_in && !$todayAttendance->clock_out)
                            <div class="alert alert-success w-100">
                                <i class="bi bi-check-circle"></i> Sudah absen masuk pukul <strong>{{ $todayAttendance->clock_in }}</strong>
                            </div>
                            <button id="btn-clock-out" class="btn btn-danger btn-lg" onclick="processAttendance('clock-out')">
                                <i class="bi bi-box-arrow-right"></i> Absen Pulang
                            </button>
                        @elseif($todayAttendance && $todayAttendance->clock_out)
                            <div class="alert alert-success w-100">
                                <i class="bi bi-check-circle"></i> Absensi hari ini sudah lengkap.
                                <br>Masuk: <strong>{{ $todayAttendance->clock_in }}</strong> | Pulang: <strong>{{ $todayAttendance->clock_out }}</strong>
                            </div>
                        @else
                            <button id="btn-clock-in" class="btn btn-success btn-lg" onclick="processAttendance('clock-in')">
                                <i class="bi bi-box-arrow-in-right"></i> Absen Masuk
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@if(auth()->user()->face_registered)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const video = document.getElementById('video');
const overlay = document.getElementById('overlay');
const statusMessage = document.getElementById('status-message');

let labeledDescriptors = [];
let faceMatcher = null;
let isModelLoaded = false;
let recognizedUserId = null;

async function loadModels() {
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/';
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);

        // Load face descriptors dari database
        const response = await fetch('/api/face-descriptors');
        const data = await response.json();

        if (data.length === 0) {
            statusMessage.className = 'alert alert-warning';
            statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Tidak ada data wajah di database.';
            return;
        }

        // Group descriptors by user
        const grouped = {};
        data.forEach(item => {
            if (!grouped[item.user_id]) {
                grouped[item.user_id] = { name: item.name, user_id: item.user_id, descriptors: [] };
            }
            grouped[item.user_id].descriptors.push(new Float32Array(item.descriptor));
        });

        labeledDescriptors = Object.values(grouped).map(user => {
            return new faceapi.LabeledFaceDescriptors(
                user.user_id + '|' + user.name,
                user.descriptors
            );
        });

        faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);
        isModelLoaded = true;

        statusMessage.className = 'alert alert-success';
        statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> Siap! Posisikan wajah Anda di depan kamera.';

        startRecognition();
    } catch (error) {
        statusMessage.className = 'alert alert-danger';
        statusMessage.innerHTML = '<i class="bi bi-x-circle"></i> Gagal memuat: ' + error.message;
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
        statusMessage.innerHTML = '<i class="bi bi-camera-video-off"></i> Tidak bisa mengakses kamera.';
    }
}

async function startRecognition() {
    const canvas = overlay;
    const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
    faceapi.matchDimensions(canvas, displaySize);

    setInterval(async () => {
        if (!isModelLoaded || !faceMatcher) return;

        const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (detection) {
            const resized = faceapi.resizeResults(detection, displaySize);
            const match = faceMatcher.findBestMatch(detection.descriptor);

            const box = resized.detection.box;
            const label = match.toString();

            if (match.label !== 'unknown') {
                const parts = match.label.split('|');
                recognizedUserId = parseInt(parts[0]);
                const name = parts[1];

                // Draw green box
                ctx.strokeStyle = '#28a745';
                ctx.lineWidth = 3;
                ctx.strokeRect(box.x, box.y, box.width, box.height);
                ctx.fillStyle = '#28a745';
                ctx.font = '16px Arial';
                ctx.fillText(name + ' (' + (100 - match.distance * 100).toFixed(0) + '%)', box.x, box.y - 10);

                document.getElementById('recognition-result').style.display = 'block';
                document.getElementById('recognized-name').textContent = name;
                document.getElementById('recognized-distance').textContent =
                    'Kemiripan: ' + (100 - match.distance * 100).toFixed(1) + '%';
            } else {
                recognizedUserId = null;
                ctx.strokeStyle = '#dc3545';
                ctx.lineWidth = 3;
                ctx.strokeRect(box.x, box.y, box.width, box.height);
                ctx.fillStyle = '#dc3545';
                ctx.font = '16px Arial';
                ctx.fillText('Tidak dikenali', box.x, box.y - 10);

                document.getElementById('recognition-result').style.display = 'none';
            }
        } else {
            recognizedUserId = null;
            document.getElementById('recognition-result').style.display = 'none';
        }
    }, 500);
}

async function processAttendance(type) {
    if (!recognizedUserId) {
        statusMessage.className = 'alert alert-warning';
        statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Wajah belum terdeteksi. Pastikan wajah Anda terlihat jelas di kamera.';
        return;
    }

    // Pastikan yang terdeteksi adalah user yang sedang login
    if (recognizedUserId !== {{ auth()->id() }}) {
        statusMessage.className = 'alert alert-danger';
        statusMessage.innerHTML = '<i class="bi bi-x-circle"></i> Wajah yang terdeteksi bukan milik Anda!';
        return;
    }

    const url = type === 'clock-in'
        ? '{{ route("karyawan.attendance.clockIn") }}'
        : '{{ route("karyawan.attendance.clockOut") }}';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ user_id: recognizedUserId }),
        });

        const data = await response.json();

        if (response.ok) {
            statusMessage.className = 'alert alert-success';
            statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> ' + data.message + ' (' + data.time + ')';

            // Reload halaman setelah 2 detik
            setTimeout(() => location.reload(), 2000);
        } else {
            statusMessage.className = 'alert alert-warning';
            statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle"></i> ' + data.message;
        }
    } catch (error) {
        statusMessage.className = 'alert alert-danger';
        statusMessage.innerHTML = '<i class="bi bi-x-circle"></i> Terjadi kesalahan: ' + error.message;
    }
}

startCamera().then(loadModels);
</script>
@endpush
@endif
