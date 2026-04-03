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
                    <svg viewBox="0 0 24 24" fill="currentColor" width="26" height="26" class="me-2" style="color: var(--gv-green);">
                        <path d="M2,22 L2,17 L1,14 L2,12 L4,7 L5,12 L6,10 L8,4 L10,10 L11,8 L13,1.5 L15,9 L16.5,8 L18,9.5 L20,10.5 L22,11.5 L23,12.5 L22,14 L19,13.5 L21,15.5 L23,17 L21,18.5 L18,17 L14.5,18.5 L10,20 L6,21 Z"/>
                    </svg>
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
                        <a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}"
                           href="{{ route('admin.departments.index') }}">
                            <i class="bi bi-building"></i> Departemen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.attendances.*') ? 'active' : '' }}"
                           href="{{ route('admin.attendances.index') }}">
                            <i class="bi bi-calendar-check-fill"></i> Laporan Absensi
                        </a>
                    </li>
                    <li class="nav-section mt-3">Pengaturan</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}"
                           href="{{ route('admin.schedules.index') }}">
                            <i class="bi bi-calendar-week"></i> Jadwal Kerja
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
                    <span class="text-muted" style="font-size: 0.7rem; font-family: 'JetBrains Mono', monospace; letter-spacing: 1px; text-transform: uppercase;">Tema</span>
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
