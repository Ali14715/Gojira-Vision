<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kiosk Absensi - Gojira Vision</title>
    <script>
        (function() {
            const theme = localStorage.getItem('gv-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; overflow: hidden; cursor: none; }
        body.show-cursor { cursor: default; }
    </style>
</head>
<body class="kiosk-body">
    <div class="kiosk-container">
        {{-- Header --}}
        <div class="kiosk-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24" class="me-2" style="color: var(--gv-green);">
                    <path d="M2,22 L2,17 L1,14 L2,12 L4,7 L5,12 L6,10 L8,4 L10,10 L11,8 L13,1.5 L15,9 L16.5,8 L18,9.5 L20,10.5 L22,11.5 L23,12.5 L22,14 L19,13.5 L21,15.5 L23,17 L21,18.5 L18,17 L14.5,18.5 L10,20 L6,21 Z"/>
                </svg>
                <span class="fw-bold kiosk-brand-text">GOJIRA VISION</span>
                <span class="ms-3 kiosk-subtitle-text">Kiosk Absensi</span>
            </div>
            <div class="d-flex align-items-center gap-4">
                <div id="status-indicator" class="d-flex align-items-center">
                    <span class="rounded-circle me-2" style="width: 8px; height: 8px; background: var(--gv-warning); display: inline-block;"></span>
                    <span id="status-text" class="kiosk-status-text">Memuat...</span>
                </div>
                {{-- Theme Toggle --}}
                <div class="theme-toggle show-cursor" id="themeToggle" onclick="toggleTheme()" title="Ganti tema" style="cursor: pointer;">
                    <div class="toggle-thumb">
                        <i class="bi" id="themeIcon"></i>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="show-cursor kiosk-exit-link">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </div>
        </div>

        {{-- Main Video Area --}}
        <div class="kiosk-video-wrapper">
            <video id="video" autoplay muted playsinline></video>
            <canvas id="overlay"></canvas>

            {{-- Clock overlay (bottom-left) --}}
            <div class="kiosk-overlay">
                <div class="d-flex justify-content-between align-items-end">
                    <div>
                        <div class="kiosk-clock" id="clock">--:--:--</div>
                        <div class="kiosk-date" id="date-display">--</div>
                    </div>
                    <div id="kiosk-result" class="kiosk-result" style="display: none;">
                    </div>
                </div>
            </div>

            {{-- Status badge (top-right) --}}
            <div class="kiosk-status">
                <div id="detect-status" class="badge bg-info" style="font-size: 0.85rem; padding: 8px 14px;">
                    <i class="bi bi-hourglass-split"></i> Menunggu...
                </div>
            </div>

            {{-- Activity log (right side) --}}
            <div class="kiosk-log" id="activity-log"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const statusText = document.getElementById('status-text');
    const detectStatus = document.getElementById('detect-status');
    const activityLog = document.getElementById('activity-log');
    const kioskResult = document.getElementById('kiosk-result');

    let faceMatcher = null;
    let isProcessing = false;
    let cooldownUsers = {}; // prevent spam: user_id -> timestamp

    // ====== CLOCK ======
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });

        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('date-display').textContent = now.toLocaleDateString('id-ID', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ====== CAMERA ======
    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1280, height: 720, facingMode: 'user' }
            });
            video.srcObject = stream;
            await new Promise(resolve => video.onloadedmetadata = resolve);
        } catch (error) {
            setStatus('error', 'Kamera tidak tersedia');
        }
    }

    // ====== LOAD MODELS ======
    async function loadModels() {
        setStatus('loading', 'Memuat model AI...');
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/';

        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);

            // Load descriptors
            const response = await fetch('{{ route("kiosk.descriptors") }}');
            const data = await response.json();

            if (data.length === 0) {
                setStatus('warning', 'Belum ada data wajah terdaftar');
                return;
            }

            const grouped = {};
            data.forEach(item => {
                if (!grouped[item.user_id]) {
                    grouped[item.user_id] = {
                        name: item.name,
                        position: item.position,
                        user_id: item.user_id,
                        descriptors: []
                    };
                }
                grouped[item.user_id].descriptors.push(new Float32Array(item.descriptor));
            });

            const labeledDescriptors = Object.values(grouped).map(user => {
                return new faceapi.LabeledFaceDescriptors(
                    user.user_id + '|' + user.name + '|' + (user.position || ''),
                    user.descriptors
                );
            });

            faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);

            setStatus('ready', 'Siap — Tunjukkan wajah Anda');
            startRecognition();
        } catch (error) {
            setStatus('error', 'Gagal: ' + error.message);
        }
    }

    // ====== RECOGNITION LOOP ======
    function startRecognition() {
        const canvas = overlay;
        const displaySize = { width: video.videoWidth, height: video.videoHeight };
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
            if (!faceMatcher || isProcessing) return;

            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                const resized = faceapi.resizeResults(detection, displaySize);
                const match = faceMatcher.findBestMatch(detection.descriptor);
                const box = resized.detection.box;

                if (match.label !== 'unknown') {
                    const parts = match.label.split('|');
                    const userId = parseInt(parts[0]);
                    const name = parts[1];
                    const position = parts[2] || '';
                    const confidence = (100 - match.distance * 100).toFixed(0);

                    // Draw green box
                    ctx.strokeStyle = '#00dc82';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);

                    // Label
                    ctx.font = 'bold 20px Inter, Arial';
                    ctx.fillStyle = '#00dc82';
                    const labelText = name + ' (' + confidence + '%)';
                    const textWidth = ctx.measureText(labelText).width;
                    ctx.fillRect(box.x, box.y - 30, textWidth + 16, 28);
                    ctx.fillStyle = '#000';
                    ctx.fillText(labelText, box.x + 8, box.y - 8);

                    // Show result
                    showResult(name, position);

                    // Auto clock (with cooldown)
                    detectStatus.className = 'badge bg-success';
                    detectStatus.innerHTML = '<i class="bi bi-person-check-fill"></i> ' + name;

                    if (!cooldownUsers[userId] || Date.now() - cooldownUsers[userId] > 30000) {
                        processKioskAttendance(userId, name, position);
                        cooldownUsers[userId] = Date.now();
                    }
                } else {
                    // Red box for unknown
                    ctx.strokeStyle = '#ef4444';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);

                    ctx.font = 'bold 18px Inter, Arial';
                    ctx.fillStyle = '#ef4444';
                    ctx.fillRect(box.x, box.y - 28, 150, 26);
                    ctx.fillStyle = '#fff';
                    ctx.fillText('Tidak Dikenali', box.x + 8, box.y - 8);

                    detectStatus.className = 'badge bg-danger';
                    detectStatus.innerHTML = '<i class="bi bi-person-x-fill"></i> Tidak Dikenali';
                    hideResult();
                }
            } else {
                detectStatus.className = 'badge bg-info';
                detectStatus.innerHTML = '<i class="bi bi-search"></i> Mendeteksi wajah...';
                hideResult();
            }
        }, 500);
    }

    // ====== ATTENDANCE ======
    async function processKioskAttendance(userId, name, position) {
        if (isProcessing) return;
        isProcessing = true;

        try {
            const response = await fetch('{{ route("kiosk.clock") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ user_id: userId }),
            });

            const data = await response.json();

            if (response.ok) {
                addLogEntry(name, position, data.type, data.time, data.status || 'hadir');
            } else if (data.type === 'done') {
                // Already completed, don't log again
            }
        } catch (error) {
            console.error('Attendance error:', error);
        }

        isProcessing = false;
    }

    // ====== UI HELPERS ======
    function setStatus(type, text) {
        const indicator = document.querySelector('#status-indicator .rounded-circle');
        const colors = { loading: 'var(--gv-warning)', ready: 'var(--gv-green)', error: 'var(--gv-danger)', warning: 'var(--gv-warning)' };
        indicator.style.background = colors[type] || 'var(--gv-text-dim)';
        statusText.textContent = text;
    }

    function showResult(name, position) {
        kioskResult.style.display = 'block';
        kioskResult.innerHTML = `
            <div class="name">${name}</div>
            <div class="position">${position || ''}</div>
        `;
    }

    function hideResult() {
        kioskResult.style.display = 'none';
    }

    function addLogEntry(name, position, type, time, status) {
        const typeLabels = {
            clock_in: '<i class="bi bi-box-arrow-in-right"></i> Masuk',
            clock_out: '<i class="bi bi-box-arrow-right"></i> Pulang',
        };
        const statusClass = status === 'terlambat' ? 'late' : 'success';

        const entry = document.createElement('div');
        entry.className = 'kiosk-log-item ' + statusClass + ' kiosk-result-success';
        entry.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="kiosk-log-name">${name}</div>
                    <div class="kiosk-log-position">${position || '-'}</div>
                </div>
                <div class="text-end">
                    <div class="kiosk-log-type">${typeLabels[type] || type}</div>
                    <div class="kiosk-log-time">${time}</div>
                </div>
            </div>
            ${status === 'terlambat' ? '<div class="mt-1"><span class="badge bg-warning" style="font-size: 0.7rem;">Terlambat</span></div>' : ''}
        `;

        activityLog.prepend(entry);

        // Max 10 entries visible
        while (activityLog.children.length > 10) {
            activityLog.removeChild(activityLog.lastChild);
        }
    }

    // ====== INIT ======
    startCamera().then(loadModels);

    // Show cursor on mouse move
    let cursorTimeout;
    document.addEventListener('mousemove', () => {
        document.body.classList.add('show-cursor');
        clearTimeout(cursorTimeout);
        cursorTimeout = setTimeout(() => document.body.classList.remove('show-cursor'), 3000);
    });

    // ====== THEME TOGGLE ======
    function toggleTheme() {
        const html = document.documentElement;
        const current = html.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('gv-theme', next);
        updateThemeIcon(next);
    }

    function updateThemeIcon(theme) {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.className = 'bi ' + (theme === 'dark' ? 'bi-moon-fill' : 'bi-sun-fill');
        }
    }

    updateThemeIcon(document.documentElement.getAttribute('data-theme') || 'dark');
    </script>
</body>
</html>
