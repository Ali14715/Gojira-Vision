<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gojira Vision</title>
    <script>
        (function() {
            const theme = localStorage.getItem('gv-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-page">
        {{-- Marquee ticker top --}}
        <div class="marquee-track">
            <div class="marquee-content">
                <span>FACE RECOGNITION ◆ ATTENDANCE SYSTEM ◆ GOJIRA VISION ◆ SMART ABSENSI ◆ CLOCK IN ◆ CLOCK OUT ◆ KIOSK MODE ◆ </span>
                <span>FACE RECOGNITION ◆ ATTENDANCE SYSTEM ◆ GOJIRA VISION ◆ SMART ABSENSI ◆ CLOCK IN ◆ CLOCK OUT ◆ KIOSK MODE ◆ </span>
            </div>
        </div>

        {{-- Marquee ticker bottom --}}
        <div class="marquee-track bottom">
            <div class="marquee-content" style="animation-direction: reverse;">
                <span>TENSORFLOW.JS ◆ FACE-API ◆ LARAVEL 13 ◆ BOOTSTRAP 5 ◆ REAL-TIME ◆ WEBCAM ◆ ZERO BACKEND AI ◆ </span>
                <span>TENSORFLOW.JS ◆ FACE-API ◆ LARAVEL 13 ◆ BOOTSTRAP 5 ◆ REAL-TIME ◆ WEBCAM ◆ ZERO BACKEND AI ◆ </span>
            </div>
        </div>

        {{-- Theme toggle top-right --}}
        <div class="position-fixed" style="top: 20px; right: 20px; z-index: 10;">
            <div class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Ganti tema">
                <div class="toggle-thumb">
                    <i class="bi" id="themeIcon"></i>
                </div>
            </div>
        </div>

        <div class="login-card" style="width: 100%; max-width: 420px; padding: 0 20px;">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="56" height="56" style="color: var(--gv-green);">
                        <path d="M2,22 L2,17 L1,14 L2,12 L4,7 L5,12 L6,10 L8,4 L10,10 L11,8 L13,1.5 L15,9 L16.5,8 L18,9.5 L20,10.5 L22,11.5 L23,12.5 L22,14 L19,13.5 L21,15.5 L23,17 L21,18.5 L18,17 L14.5,18.5 L10,20 L6,21 Z"/>
                    </svg>
                </div>
                <h2 class="fw-bold login-title" style="letter-spacing: -1px;">Gojira Vision</h2>
                <p class="login-subtitle">Sistem Absensi Citra Wajah</p>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger py-2 mb-3">
                            @foreach($errors->all() as $error)
                                <small><i class="bi bi-x-circle me-1"></i>{{ $error }}</small><br>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Masukkan password" required>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </button>
                    </form>

                    <div class="neo-divider mt-3">atau</div>

                    <div class="text-center auth-switch-link">
                        Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('kiosk') }}" class="login-kiosk-link">
                    <i class="bi bi-display me-1"></i> Buka Mode Kiosk
                </a>
            </div>
        </div>
    </div>

    <script>
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
