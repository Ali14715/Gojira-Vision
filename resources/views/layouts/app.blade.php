<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gojira Vision') - Sistem Absensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Apply theme before page renders to prevent flash
        (function() {
            const theme = localStorage.getItem('gv-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        {{-- Sidebar --}}
        <nav class="sidebar d-flex flex-column" style="width: 260px; min-width: 260px;">
            <div class="brand">
                <a class="text-decoration-none d-flex align-items-center" href="#">
                    <i class="bi bi-eye-fill fs-4 me-2" style="color: var(--gv-green);"></i>
                    <h5 class="mb-0">Gojira Vision</h5>
                </a>
            </div>

            <ul class="nav flex-column mt-2">
                @if(auth()->user()->isAdmin())
                    <li class="nav-section">Menu Utama</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                           href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}"
                           href="{{ route('admin.employees.index') }}">
                            <i class="bi bi-people-fill"></i> Karyawan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.attendances.*') ? 'active' : '' }}"
                           href="{{ route('admin.attendances.index') }}">
                            <i class="bi bi-calendar-check-fill"></i> Laporan Absensi
                        </a>
                    </li>
                    <li class="nav-section mt-3">Kiosk</li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('kiosk') }}" target="_blank">
                            <i class="bi bi-display"></i> Buka Kiosk Mode
                        </a>
                    </li>
                @else
                    <li class="nav-section">Menu Utama</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}"
                           href="{{ route('karyawan.dashboard') }}">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.attendance') ? 'active' : '' }}"
                           href="{{ route('karyawan.attendance') }}">
                            <i class="bi bi-camera-video-fill"></i> Absensi
                        </a>
                    </li>
                    <li class="nav-section mt-3">Pengaturan</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.face.register') ? 'active' : '' }}"
                           href="{{ route('karyawan.face.register') }}">
                            <i class="bi bi-person-bounding-box"></i> Daftar Wajah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.history') ? 'active' : '' }}"
                           href="{{ route('karyawan.history') }}">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </a>
                    </li>
                @endif
            </ul>

            <div class="mt-auto p-3 sidebar-footer">
                {{-- Theme Toggle --}}
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted" style="font-size: 0.75rem;">Tema</span>
                    <div class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Ganti tema">
                        <div class="toggle-thumb">
                            <i class="bi" id="themeIcon"></i>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-role">{{ auth()->user()->role }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        {{-- Main Content --}}
        <div class="flex-grow-1 main-content">
            <div class="p-4">
                <div class="content-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@yield('title', 'Dashboard')</h4>
                    <span class="content-header-date">
                        <i class="bi bi-calendar3 me-1"></i> {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-x-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
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

    // Init icon on load
    updateThemeIcon(document.documentElement.getAttribute('data-theme') || 'dark');
    </script>
    @stack('scripts')
</body>
</html>
