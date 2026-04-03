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
            {{-- Employee ID Badge --}}
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="neo-tag green" style="font-size: 0.85rem; padding: 6px 14px;">
                    <i class="bi bi-person-badge me-1"></i> ID: {{ auth()->user()->employee_id }}
                </div>
                <span style="color: var(--gv-text-muted); font-size: 0.85rem;">{{ auth()->user()->name }}</span>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-camera-video-fill me-2" style="color: var(--gv-green);"></i>Absensi Wajah</h6>
                    {{-- Mode Toggle --}}
                    @if(!$todayAttendance || ($todayAttendance->clock_in && !$todayAttendance->clock_out))
                    <div class="attendance-mode-toggle" id="modeToggle">
                        <button type="button" class="mode-btn {{ !$todayAttendance ? 'active' : '' }}" data-mode="clock-in" onclick="setMode('clock-in')">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </button>
                        <button type="button" class="mode-btn clock-out {{ $todayAttendance && $todayAttendance->clock_in && !$todayAttendance->clock_out ? 'active' : '' }}" data-mode="clock-out" onclick="setMode('clock-out')">
                            <i class="bi bi-box-arrow-right me-1"></i> Pulang
                        </button>
                    </div>
                    @endif
                </div>
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
                            <div class="card-body py-2">
                                <h5 id="recognized-name" style="color: var(--gv-green);"></h5>
                                <p id="recognized-distance" class="text-muted mb-0" style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;"></p>
                            </div>
                        </div>
                    </div>

                    @if($todayAttendance && $todayAttendance->clock_out)
                        <div class="alert alert-success w-100">
                            <i class="bi bi-check-circle"></i> Absensi hari ini sudah lengkap.
                            <br>Masuk: <strong>{{ $todayAttendance->clock_in }}</strong> | Pulang: <strong>{{ $todayAttendance->clock_out }}</strong>
                        </div>
                    @elseif($todayAttendance && $todayAttendance->clock_in && !$todayAttendance->clock_out)
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle"></i> Sudah absen masuk pukul <strong>{{ $todayAttendance->clock_in }}</strong>
                        </div>
                        <button id="btn-attendance" class="btn btn-danger btn-lg" onclick="processAttendance()">
                            <i class="bi bi-box-arrow-right me-1"></i> Absen Pulang
                        </button>
                    @else
                        <button id="btn-attendance" class="btn btn-success btn-lg" onclick="processAttendance()">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Absen Masuk
                        </button>
                    @endif
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

let myMatcher = null;
let isModelLoaded = false;
let faceVerified = false;
let attendanceMode = '{{ ($todayAttendance && $todayAttendance->clock_in && !$todayAttendance->clock_out) ? "clock-out" : "clock-in" }}';

function setMode(mode) {
    attendanceMode = mode;
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.mode === mode) btn.classList.add('active');
    });

    const btnAttendance = document.getElementById('btn-attendance');
    if (btnAttendance) {
        if (mode === 'clock-in') {
            btnAttendance.className = 'btn btn-success btn-lg';
            btnAttendance.innerHTML = '<i class="bi bi-box-arrow-in-right me-1"></i> Absen Masuk';
        } else {
            btnAttendance.className = 'btn btn-danger btn-lg';
            btnAttendance.innerHTML = '<i class="bi bi-box-arrow-right me-1"></i> Absen Pulang';
        }
    }
}

async function loadModels() {
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/';
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);

        // Fetch only MY descriptors via the API
        const response = await fetch('/api/face-descriptors');
        const data = await response.json();

        // Filter only my own descriptors
        const myData = data.filter(d => d.user_id === {{ auth()->id() }});

        if (myData.length === 0) {
            statusMessage.className = 'alert alert-warning';
            statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Data wajah Anda tidak ditemukan.';
            return;
        }

        const myDescriptors = myData.map(d => new Float32Array(d.descriptor));
        const labeled = new faceapi.LabeledFaceDescriptors(
            '{{ auth()->id() }}|{{ auth()->user()->name }}',
            myDescriptors
        );

        myMatcher = new faceapi.FaceMatcher([labeled], 0.5);
        isModelLoaded = true;

        statusMessage.className = 'alert alert-success';
        statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> Siap! Posisikan wajah Anda di depan kamera untuk verifikasi.';

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
        if (!isModelLoaded || !myMatcher) return;

        const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (detection) {
            const resized = faceapi.resizeResults(detection, displaySize);
            const match = myMatcher.findBestMatch(detection.descriptor);
            const box = resized.detection.box;
            const confidence = (100 - match.distance * 100).toFixed(0);

            if (match.label !== 'unknown') {
                faceVerified = true;

                ctx.strokeStyle = '#00dc82';
                ctx.lineWidth = 3;
                ctx.strokeRect(box.x, box.y, box.width, box.height);
                ctx.fillStyle = '#00dc82';
                ctx.font = 'bold 16px "Space Grotesk", Arial';
                ctx.fillText('{{ auth()->user()->name }} (' + confidence + '%)', box.x, box.y - 10);

                document.getElementById('recognition-result').style.display = 'block';
                document.getElementById('recognized-name').textContent = '{{ auth()->user()->name }}';
                document.getElementById('recognized-distance').textContent = 'Verifikasi: ' + confidence + '% cocok';
            } else {
                faceVerified = false;
                ctx.strokeStyle = '#ef4444';
                ctx.lineWidth = 3;
                ctx.strokeRect(box.x, box.y, box.width, box.height);
                ctx.fillStyle = '#ef4444';
                ctx.font = 'bold 16px "Space Grotesk", Arial';
                ctx.fillText('Wajah tidak cocok (' + confidence + '%)', box.x, box.y - 10);

                document.getElementById('recognition-result').style.display = 'none';
            }
        } else {
            faceVerified = false;
            document.getElementById('recognition-result').style.display = 'none';
        }
    }, 500);
}

async function processAttendance() {
    if (!faceVerified) {
        statusMessage.className = 'alert alert-warning';
        statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Wajah belum terverifikasi. Pastikan wajah Anda terlihat jelas dan cocok.';
        return;
    }

    const url = attendanceMode === 'clock-in'
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
            body: JSON.stringify({ user_id: {{ auth()->id() }} }),
        });

        const data = await response.json();

        if (response.ok) {
            statusMessage.className = 'alert alert-success';
            statusMessage.innerHTML = '<i class="bi bi-check-circle"></i> ' + data.message + ' (' + data.time + ')';
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
