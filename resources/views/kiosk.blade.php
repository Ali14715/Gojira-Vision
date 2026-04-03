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

        /* ====== WINDOWS-STYLE WINDOW ====== */
        .win-window {
            position: absolute;
            z-index: 20;
            min-width: 280px;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            border: 2px solid var(--gv-border);
            border-radius: 8px 8px 4px 4px;
            background: rgba(10, 10, 18, 0.92);
            backdrop-filter: blur(16px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.04);
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .win-window.active { box-shadow: 0 8px 32px rgba(0,220,130,0.12), 0 0 0 1px rgba(0,220,130,0.15); border-color: var(--gv-green); }
        .win-window.minimized .win-body { display: none; }
        .win-window.minimized .win-resize { display: none; }
        .win-window.minimized { min-height: unset; height: auto !important; }

        [data-theme="light"] .win-window {
            background: rgba(250, 247, 242, 0.95);
            box-shadow: 6px 6px 0px #1a1a1a, 0 0 0 1px rgba(0,0,0,0.08);
            border-color: #1a1a1a;
        }
        [data-theme="light"] .win-window.active { box-shadow: 6px 6px 0px #1a1a1a, 0 0 0 2px var(--gv-green); }

        /* Title Bar */
        .win-titlebar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 4px 0 12px;
            height: 36px;
            background: rgba(20, 20, 30, 0.95);
            cursor: grab;
            user-select: none;
            flex-shrink: 0;
        }
        .win-titlebar:active { cursor: grabbing; }
        [data-theme="light"] .win-titlebar { background: #1a1a1a; }

        .win-titlebar-left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .win-titlebar-icon { font-size: 0.85rem; color: var(--gv-green); flex-shrink: 0; }
        .win-titlebar-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.8);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Window Buttons */
        .win-btns { display: flex; align-items: center; gap: 0; flex-shrink: 0; height: 100%; }
        .win-btn {
            width: 46px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .win-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .win-btn.close:hover { background: #e81123; color: #fff; }

        /* Body */
        .win-body {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            text-align: center;
        }

        /* Resize handle */
        .win-resize {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 16px;
            height: 16px;
            cursor: nwse-resize;
        }
        .win-resize::after {
            content: '';
            position: absolute;
            bottom: 3px;
            right: 3px;
            width: 8px;
            height: 8px;
            border-right: 2px solid rgba(255,255,255,0.2);
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }
        [data-theme="light"] .win-resize::after {
            border-color: rgba(0,0,0,0.2);
        }

        /* ====== ID Input ====== */
        .kiosk-id-input {
            background: var(--gv-bg-input);
            border: 3px solid var(--gv-border);
            color: var(--gv-text-heading);
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.2rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: 6px;
            padding: 14px;
            border-radius: 8px;
            width: 100%;
        }
        .kiosk-id-input:focus {
            outline: none;
            border-color: var(--gv-green);
            box-shadow: 4px 4px 0px var(--gv-green);
        }
        .kiosk-id-input::placeholder {
            font-size: 1.2rem;
            letter-spacing: 4px;
            color: var(--gv-text-dim);
        }

        /* Verify badge specific */
        .win-window.verify-window.active { border-color: var(--gv-green); }
        .win-window.verify-window.error { border-color: var(--gv-danger); }

        [data-theme="light"] .win-window #verify-name { color: var(--gv-green) !important; }
        [data-theme="light"] .win-window #verify-position { color: var(--gv-text-muted) !important; }
        [data-theme="light"] .win-window #verify-eid { color: var(--gv-text-dim) !important; }

        /* Taskbar (for minimized windows) */
        .win-taskbar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: rgba(10, 10, 18, 0.85);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--gv-border);
            display: flex;
            align-items: center;
            padding: 0 12px;
            z-index: 25;
            gap: 4px;
        }
        [data-theme="light"] .win-taskbar {
            background: rgba(26,26,26,0.92);
            border-top-color: rgba(255,255,255,0.1);
        }
        .win-taskbar-item {
            padding: 4px 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.7);
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.15s;
            display: none;
        }
        .win-taskbar-item.visible { display: flex; align-items: center; gap: 6px; }
        .win-taskbar-item:hover { background: rgba(0,220,130,0.15); color: var(--gv-green); border-color: var(--gv-green); }
        .win-taskbar-item .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gv-green); }
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
            <div class="d-flex align-items-center gap-3">
                {{-- Mode Toggle --}}
                <div class="attendance-mode-toggle show-cursor" id="kioskModeToggle" style="cursor: pointer;">
                    <button type="button" class="mode-btn active" data-mode="clock-in" onclick="setKioskMode('clock-in')">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </button>
                    <button type="button" class="mode-btn clock-out" data-mode="clock-out" onclick="setKioskMode('clock-out')">
                        <i class="bi bi-box-arrow-right me-1"></i> Pulang
                    </button>
                </div>

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

            {{-- Step 1: ID Input Window --}}
            <div class="win-window show-cursor active" id="id-panel" style="top: 80px; right: 20px; width: 360px;">
                <div class="win-titlebar" data-drag="id-panel">
                    <div class="win-titlebar-left">
                        <i class="bi bi-person-badge win-titlebar-icon"></i>
                        <span class="win-titlebar-text">Employee ID — Gojira Vision</span>
                    </div>
                    <div class="win-btns">
                        <button class="win-btn show-cursor" onclick="winMinimize('id-panel')" title="Minimize"><i class="bi bi-dash-lg"></i></button>
                        <button class="win-btn show-cursor" onclick="winMaximize('id-panel')" title="Maximize"><i class="bi bi-square" id="max-icon-id-panel"></i></button>
                        <button class="win-btn close show-cursor" onclick="winClose('id-panel')" title="Close"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
                <div class="win-body">
                    <div class="mb-2">
                        <i class="bi bi-person-badge" style="font-size: 2.2rem; color: var(--gv-green);"></i>
                    </div>
                    <h5 style="color: var(--gv-text-heading); font-weight: 700; margin-bottom: 2px;">Masukkan ID</h5>
                    <p style="color: var(--gv-text-dim); font-size: 0.78rem; font-family: 'JetBrains Mono', monospace;" class="mb-3">Ketik nomor ID lalu ENTER</p>
                    <input type="text" class="kiosk-id-input show-cursor" id="employee-id-input" placeholder="0001" maxlength="10" inputmode="numeric" autofocus>
                    <div id="id-error" class="mt-2" style="color: var(--gv-danger); font-weight: 600; font-size: 0.85rem; display: none;"></div>
                    <button class="btn btn-primary w-100 mt-3 show-cursor" onclick="submitEmployeeId()" style="font-size: 0.9rem; padding: 10px;">
                        <i class="bi bi-arrow-right-circle me-1"></i> Verifikasi
                    </button>
                </div>
                <div class="win-resize" data-resize="id-panel"></div>
            </div>

            {{-- Step 2: Verification Window --}}
            <div class="win-window verify-window show-cursor active" id="verify-badge" style="top: 80px; right: 20px; width: 360px; display: none;">
                <div class="win-titlebar" data-drag="verify-badge">
                    <div class="win-titlebar-left">
                        <i class="bi bi-shield-check win-titlebar-icon"></i>
                        <span class="win-titlebar-text" id="verify-titlebar-text">Verifikasi Wajah</span>
                    </div>
                    <div class="win-btns">
                        <button class="win-btn show-cursor" onclick="winMinimize('verify-badge')" title="Minimize"><i class="bi bi-dash-lg"></i></button>
                        <button class="win-btn show-cursor" onclick="winMaximize('verify-badge')" title="Maximize"><i class="bi bi-square" id="max-icon-verify-badge"></i></button>
                        <button class="win-btn close show-cursor" onclick="cancelVerification()" title="Close"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
                <div class="win-body">
                    <div id="verify-name" style="font-size: 1.5rem; font-weight: 700; color: var(--gv-green);">Budi Karyawan</div>
                    <div id="verify-position" style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Staff IT</div>
                    <div id="verify-eid" style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: rgba(255,255,255,0.4); margin-top: 4px;">0001</div>
                    <div class="neo-divider" style="--gv-border: rgba(255,255,255,0.15);">verifikasi wajah</div>
                    <div id="verify-status" style="font-size: 1rem; color: var(--gv-warning);">
                        <i class="bi bi-hourglass-split"></i> Posisikan wajah di depan kamera...
                    </div>
                    <button class="btn btn-secondary btn-sm mt-3 show-cursor" onclick="cancelVerification()">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </button>
                </div>
                <div class="win-resize" data-resize="verify-badge"></div>
            </div>

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
                    <i class="bi bi-keyboard"></i> Masukkan ID Karyawan
                </div>
            </div>

            {{-- Activity log (left side) --}}
            <div class="kiosk-log" id="activity-log"></div>

            {{-- Taskbar for minimized windows --}}
            <div class="win-taskbar show-cursor" id="win-taskbar">
                <div class="win-taskbar-item" id="taskbar-id-panel" onclick="winRestore('id-panel')">
                    <span class="dot"></span> Employee ID
                </div>
                <div class="win-taskbar-item" id="taskbar-verify-badge" onclick="winRestore('verify-badge')">
                    <span class="dot"></span> Verifikasi
                </div>
            </div>
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
    const idPanel = document.getElementById('id-panel');
    const verifyBadge = document.getElementById('verify-badge');
    const idInput = document.getElementById('employee-id-input');
    const idError = document.getElementById('id-error');

    let isProcessing = false;
    let isModelLoaded = false;
    let kioskMode = 'clock-in';
    let verificationInterval = null;

    // Current target employee (after ID lookup)
    let targetUser = null; // { user_id, employee_id, name, position, descriptors: [Float32Array] }

    function setKioskMode(mode) {
        kioskMode = mode;
        document.querySelectorAll('#kioskModeToggle .mode-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.mode === mode) btn.classList.add('active');
        });
    }

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

            isModelLoaded = true;
            setStatus('ready', 'Siap — Masukkan ID Karyawan');
        } catch (error) {
            setStatus('error', 'Gagal: ' + error.message);
        }
    }

    // ====== ID INPUT - Enter key ======
    idInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitEmployeeId();
        }
    });

    // ====== STEP 1: Submit Employee ID → Lookup ======
    async function submitEmployeeId() {
        const raw = idInput.value.trim().replace(/\D/g, ''); // only digits
        if (!raw) {
            showIdError('Masukkan nomor ID terlebih dahulu.');
            return;
        }
        const employeeId = raw.padStart(4, '0'); // pad to 4 digits
        idInput.value = employeeId;

        idError.style.display = 'none';

        try {
            const response = await fetch('{{ route("kiosk.lookup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ employee_id: employeeId }),
            });

            const data = await response.json();

            if (!response.ok) {
                showIdError(data.message);
                return;
            }

            // Got user data with descriptors — move to Step 2
            targetUser = {
                user_id: data.user_id,
                employee_id: data.employee_id,
                name: data.name,
                position: data.position || '',
                descriptors: data.descriptors.map(d => new Float32Array(d)),
            };

            showVerificationPanel();

        } catch (error) {
            showIdError('Gagal menghubungi server: ' + error.message);
        }
    }

    function showIdError(msg) {
        idError.textContent = msg;
        idError.style.display = 'block';
    }

    // ====== STEP 2: Verify Face ======
    function showVerificationPanel() {
        idPanel.style.display = 'none';
        const tb = document.getElementById('taskbar-id-panel');
        if (tb) tb.classList.remove('visible');

        verifyBadge.style.display = 'flex';
        verifyBadge.classList.remove('error');
        const state = winStates['verify-badge'];
        if (state) { state.minimized = false; state.maximized = false; }
        verifyBadge.classList.remove('minimized');
        bringToFront('verify-badge');

        document.getElementById('verify-name').textContent = targetUser.name;
        document.getElementById('verify-position').textContent = targetUser.position || '-';
        document.getElementById('verify-eid').textContent = targetUser.employee_id;
        document.getElementById('verify-titlebar-text').textContent = 'Verifikasi — ' + targetUser.name;
        document.getElementById('verify-status').innerHTML = '<i class="bi bi-hourglass-split"></i> Posisikan wajah di depan kamera...';
        document.getElementById('verify-status').style.color = 'var(--gv-warning)';

        detectStatus.className = 'badge bg-warning';
        detectStatus.innerHTML = '<i class="bi bi-person-bounding-box"></i> Verifikasi: ' + targetUser.name;

        startFaceVerification();
    }

    function cancelVerification() {
        if (verificationInterval) {
            clearInterval(verificationInterval);
            verificationInterval = null;
        }
        targetUser = null;
        verifyBadge.style.display = 'none';
        const tbv = document.getElementById('taskbar-verify-badge');
        if (tbv) tbv.classList.remove('visible');

        idPanel.style.display = 'flex';
        const state = winStates['id-panel'];
        if (state) { state.minimized = false; }
        idPanel.classList.remove('minimized');
        const tbi = document.getElementById('taskbar-id-panel');
        if (tbi) tbi.classList.remove('visible');
        bringToFront('id-panel');

        idInput.value = '';
        idInput.focus();

        // Clear canvas
        const ctx = overlay.getContext('2d');
        ctx.clearRect(0, 0, overlay.width, overlay.height);

        detectStatus.className = 'badge bg-info';
        detectStatus.innerHTML = '<i class="bi bi-keyboard"></i> Masukkan ID Karyawan';
        setStatus('ready', 'Siap — Masukkan ID Karyawan');
    }

    function startFaceVerification() {
        if (!isModelLoaded || !targetUser) return;

        const canvas = overlay;
        const displaySize = { width: video.videoWidth, height: video.videoHeight };
        faceapi.matchDimensions(canvas, displaySize);

        // Build a matcher from ONLY this employee's descriptors
        const labeled = new faceapi.LabeledFaceDescriptors(
            targetUser.user_id + '|' + targetUser.name,
            targetUser.descriptors
        );
        const matcher = new faceapi.FaceMatcher([labeled], 0.5);

        let matchCount = 0;
        const REQUIRED_MATCHES = 3; // Need 3 consecutive matches

        verificationInterval = setInterval(async () => {
            if (isProcessing) return;

            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                const resized = faceapi.resizeResults(detection, displaySize);
                const match = matcher.findBestMatch(detection.descriptor);
                const box = resized.detection.box;
                const confidence = (100 - match.distance * 100).toFixed(0);

                if (match.label !== 'unknown') {
                    // Match found
                    matchCount++;

                    ctx.strokeStyle = '#00dc82';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);

                    ctx.font = 'bold 20px "Space Grotesk", Inter, Arial';
                    ctx.fillStyle = '#00dc82';
                    const labelText = targetUser.name + ' (' + confidence + '%)';
                    const textWidth = ctx.measureText(labelText).width;
                    ctx.fillRect(box.x, box.y - 30, textWidth + 16, 28);
                    ctx.fillStyle = '#000';
                    ctx.fillText(labelText, box.x + 8, box.y - 8);

                    document.getElementById('verify-status').innerHTML =
                        '<i class="bi bi-check-circle"></i> Wajah cocok! (' + confidence + '%) — Verifikasi ' + matchCount + '/' + REQUIRED_MATCHES;
                    document.getElementById('verify-status').style.color = 'var(--gv-green)';

                    if (matchCount >= REQUIRED_MATCHES) {
                        clearInterval(verificationInterval);
                        verificationInterval = null;
                        processKioskAttendance();
                    }
                } else {
                    // Detected but not matching
                    matchCount = 0;
                    ctx.strokeStyle = '#ef4444';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);

                    ctx.font = 'bold 18px "Space Grotesk", Inter, Arial';
                    ctx.fillStyle = '#ef4444';
                    ctx.fillRect(box.x, box.y - 28, 200, 26);
                    ctx.fillStyle = '#fff';
                    ctx.fillText('Wajah tidak cocok', box.x + 8, box.y - 8);

                    document.getElementById('verify-status').innerHTML =
                        '<i class="bi bi-x-circle"></i> Wajah tidak cocok dengan ' + targetUser.name;
                    document.getElementById('verify-status').style.color = 'var(--gv-danger)';
                }
            } else {
                matchCount = 0;
                document.getElementById('verify-status').innerHTML = '<i class="bi bi-hourglass-split"></i> Posisikan wajah di depan kamera...';
                document.getElementById('verify-status').style.color = 'var(--gv-warning)';
            }
        }, 500);
    }

    // ====== CLOCK IN/OUT ======
    async function processKioskAttendance() {
        if (isProcessing || !targetUser) return;
        isProcessing = true;

        document.getElementById('verify-status').innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses absensi...';
        document.getElementById('verify-status').style.color = 'var(--gv-warning)';

        try {
            const response = await fetch('{{ route("kiosk.clock") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ employee_id: targetUser.employee_id, mode: kioskMode }),
            });

            const data = await response.json();

            if (response.ok) {
                // Success
                document.getElementById('verify-status').innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + data.message;
                document.getElementById('verify-status').style.color = 'var(--gv-green)';

                detectStatus.className = 'badge bg-success';
                detectStatus.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + targetUser.name + ' — ' + (data.type === 'clock_in' ? 'Masuk' : 'Pulang');

                showResult(targetUser.name, targetUser.position);
                addLogEntry(targetUser.name, targetUser.position, data.type, data.time, data.status || 'hadir');

                // Go back to ID input after 3 seconds
                setTimeout(() => {
                    cancelVerification();
                }, 3000);
            } else {
                document.getElementById('verify-status').innerHTML = '<i class="bi bi-exclamation-triangle"></i> ' + data.message;
                document.getElementById('verify-status').style.color = 'var(--gv-warning)';

                setTimeout(() => {
                    cancelVerification();
                }, 3000);
            }
        } catch (error) {
            document.getElementById('verify-status').innerHTML = '<i class="bi bi-x-circle"></i> Error: ' + error.message;
            document.getElementById('verify-status').style.color = 'var(--gv-danger)';
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

    // ====== WINDOWS MANAGEMENT ======
    const winStates = {};

    function initWin(id) {
        const el = document.getElementById(id);
        if (!el || winStates[id]) return;
        winStates[id] = {
            minimized: false,
            maximized: false,
            prevRect: null, // { top, left, width, height } before maximize
        };
    }
    initWin('id-panel');
    initWin('verify-badge');

    function winMinimize(id) {
        const el = document.getElementById(id);
        const state = winStates[id];
        if (!el || !state) return;
        state.minimized = true;
        el.classList.add('minimized');
        // Show in taskbar
        const tb = document.getElementById('taskbar-' + id);
        if (tb) tb.classList.add('visible');
    }

    function winRestore(id) {
        const el = document.getElementById(id);
        const state = winStates[id];
        if (!el || !state) return;
        state.minimized = false;
        el.classList.remove('minimized');
        el.style.display = 'flex';
        // Hide from taskbar
        const tb = document.getElementById('taskbar-' + id);
        if (tb) tb.classList.remove('visible');
        bringToFront(id);
    }

    function winMaximize(id) {
        const el = document.getElementById(id);
        const state = winStates[id];
        if (!el || !state) return;
        const icon = document.getElementById('max-icon-' + id);

        if (state.maximized) {
            // Restore from maximize
            if (state.prevRect) {
                el.style.top = state.prevRect.top;
                el.style.left = state.prevRect.left;
                el.style.right = state.prevRect.right;
                el.style.width = state.prevRect.width;
                el.style.height = state.prevRect.height;
            }
            state.maximized = false;
            if (icon) icon.className = 'bi bi-square';
        } else {
            // Save current rect
            state.prevRect = {
                top: el.style.top,
                left: el.style.left,
                right: el.style.right,
                width: el.style.width,
                height: el.style.height,
            };
            el.style.top = '0';
            el.style.left = '0';
            el.style.right = 'auto';
            el.style.width = '100%';
            el.style.height = 'calc(100% - 40px)'; // leave room for taskbar
            state.maximized = true;
            if (icon) icon.className = 'bi bi-copy';
        }
    }

    function winClose(id) {
        const el = document.getElementById(id);
        if (!el) return;
        // For ID panel, hide and re-show (reset state)
        if (id === 'id-panel') {
            el.style.display = 'none';
            // Show again after 500ms with reset
            setTimeout(() => {
                el.style.display = 'flex';
                const state = winStates[id];
                if (state) {
                    state.minimized = false;
                    state.maximized = false;
                }
                el.classList.remove('minimized');
                const tb = document.getElementById('taskbar-' + id);
                if (tb) tb.classList.remove('visible');
                idInput.value = '';
                idInput.focus();
            }, 500);
        }
    }

    function bringToFront(id) {
        document.querySelectorAll('.win-window').forEach(w => {
            w.classList.remove('active');
            w.style.zIndex = '20';
        });
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('active');
            el.style.zIndex = '21';
        }
    }

    // ====== DRAG ======
    let dragState = null;

    document.addEventListener('mousedown', (e) => {
        const titlebar = e.target.closest('[data-drag]');
        if (!titlebar) return;
        // Don't drag if clicking buttons
        if (e.target.closest('.win-btn')) return;

        const id = titlebar.dataset.drag;
        const el = document.getElementById(id);
        const state = winStates[id];
        if (!el || !state || state.maximized) return;

        bringToFront(id);

        const rect = el.getBoundingClientRect();
        dragState = {
            id,
            el,
            startX: e.clientX,
            startY: e.clientY,
            origLeft: rect.left,
            origTop: rect.top,
        };

        e.preventDefault();
    });

    document.addEventListener('mousemove', (e) => {
        if (dragState) {
            const dx = e.clientX - dragState.startX;
            const dy = e.clientY - dragState.startY;
            dragState.el.style.left = (dragState.origLeft + dx) + 'px';
            dragState.el.style.top = (dragState.origTop + dy) + 'px';
            dragState.el.style.right = 'auto';
        }
        if (resizeState) {
            const dx = e.clientX - resizeState.startX;
            const dy = e.clientY - resizeState.startY;
            const newW = Math.max(280, resizeState.origW + dx);
            const newH = Math.max(120, resizeState.origH + dy);
            resizeState.el.style.width = newW + 'px';
            resizeState.el.style.height = newH + 'px';
        }
    });

    document.addEventListener('mouseup', () => {
        dragState = null;
        resizeState = null;
    });

    // ====== RESIZE ======
    let resizeState = null;

    document.addEventListener('mousedown', (e) => {
        const handle = e.target.closest('[data-resize]');
        if (!handle) return;

        const id = handle.dataset.resize;
        const el = document.getElementById(id);
        const state = winStates[id];
        if (!el || !state || state.maximized) return;

        bringToFront(id);

        resizeState = {
            id,
            el,
            startX: e.clientX,
            startY: e.clientY,
            origW: el.offsetWidth,
            origH: el.offsetHeight,
        };

        e.preventDefault();
    });

    // Double-click titlebar to maximize
    document.addEventListener('dblclick', (e) => {
        const titlebar = e.target.closest('[data-drag]');
        if (!titlebar || e.target.closest('.win-btn')) return;
        winMaximize(titlebar.dataset.drag);
    });

    // Click window to bring to front
    document.addEventListener('mousedown', (e) => {
        const win = e.target.closest('.win-window');
        if (win && win.id) bringToFront(win.id);
    });
    </script>
</body>
</html>
