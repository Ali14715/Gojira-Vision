@extends('layouts.app')

@section('title', 'Jadwal Kerja')

@section('content')
{{-- Filter Bar --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size: 0.8rem;">Departemen</label>
                <select class="form-select form-select-sm" name="department_id">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size: 0.8rem;">Cari Karyawan</label>
                <input type="text" class="form-control form-control-sm" name="search"
                       value="{{ request('search') }}" placeholder="Nama karyawan...">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm">
                    <i class="bi bi-search"></i> Filter
                </button>
                @if(request()->hasAny(['department_id', 'search']))
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-x-lg"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Action Bar --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span id="selected-count" class="text-muted" style="font-size: 0.85rem;">0 karyawan dipilih</span>
    </div>
    <button type="button" class="btn btn-dark btn-sm" id="btn-set-schedule" disabled
            data-bs-toggle="modal" data-bs-target="#scheduleModal">
        <i class="bi bi-calendar-week"></i> Set Jadwal
    </button>
</div>

{{-- Employee Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>Jadwal Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input emp-checkbox" value="{{ $emp->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-avatar"
                                         style="width: 28px; height: 28px; font-size: 0.7rem;">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    {{ $emp->name }}
                                </div>
                            </td>
                            <td>{{ $emp->department?->name ?? '-' }}</td>
                            <td>{{ $emp->position ?? '-' }}</td>
                            <td>
                                <small class="{{ $emp->workSchedules->isEmpty() ? 'text-muted' : '' }}">
                                    {{ $emp->schedule_summary }}
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color: var(--gv-text-dim);">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Tidak ada karyawan ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $employees->links() }}
</div>

{{-- Schedule Modal --}}
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.schedules.bulkUpdate') }}" id="scheduleForm">
                @csrf
                <div id="hidden-employee-ids"></div>

                <div class="modal-header">
                    <h5 class="modal-title">Atur Jadwal Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">
                        Jadwal akan diterapkan untuk <strong id="modal-emp-count">0</strong> karyawan yang dipilih.
                    </p>

                    {{-- Quick Templates --}}
                    <div class="mb-3 d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="applyTemplate('weekday')">
                            <i class="bi bi-lightning"></i> Sen-Jum 08:00-17:00
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="applyTemplate('weekday-sat')">
                            <i class="bi bi-lightning"></i> Sen-Sab (Sab 08:00-12:00)
                        </button>
                    </div>

                    {{-- Day Rows --}}
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Hari</th>
                                    <th style="width: 100px;">Status</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                                    $defaults = [
                                        1 => ['off' => false, 'in' => '08:00', 'out' => '17:00'],
                                        2 => ['off' => false, 'in' => '08:00', 'out' => '17:00'],
                                        3 => ['off' => false, 'in' => '08:00', 'out' => '17:00'],
                                        4 => ['off' => false, 'in' => '08:00', 'out' => '17:00'],
                                        5 => ['off' => false, 'in' => '08:00', 'out' => '17:00'],
                                        6 => ['off' => true, 'in' => '', 'out' => ''],
                                        7 => ['off' => true, 'in' => '', 'out' => ''],
                                    ];
                                @endphp
                                @foreach($days as $num => $label)
                                    <tr id="day-row-{{ $num }}">
                                        <td class="align-middle fw-bold">{{ $label }}</td>
                                        <td>
                                            <input type="hidden" name="schedules[{{ $num - 1 }}][day_of_week]" value="{{ $num }}">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="schedules[{{ $num - 1 }}][is_day_off]" value="0" id="dayoff-hidden-{{ $num }}">
                                                <input class="form-check-input" type="checkbox" id="dayoff-{{ $num }}"
                                                       {{ $defaults[$num]['off'] ? 'checked' : '' }}
                                                       onchange="toggleDayOff({{ $num }})">
                                                <label class="form-check-label" for="dayoff-{{ $num }}" style="font-size: 0.8rem;" id="dayoff-label-{{ $num }}">
                                                    {{ $defaults[$num]['off'] ? 'Libur' : 'Kerja' }}
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm" id="clockin-{{ $num }}"
                                                   name="schedules[{{ $num - 1 }}][clock_in_time]"
                                                   value="{{ $defaults[$num]['in'] }}"
                                                   {{ $defaults[$num]['off'] ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm" id="clockout-{{ $num }}"
                                                   name="schedules[{{ $num - 1 }}][clock_out_time]"
                                                   value="{{ $defaults[$num]['out'] }}"
                                                   {{ $defaults[$num]['off'] ? 'disabled' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-check-lg"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select all / individual checkboxes
const selectAll = document.getElementById('select-all');
const checkboxes = () => document.querySelectorAll('.emp-checkbox');
const btnSetSchedule = document.getElementById('btn-set-schedule');
const selectedCountEl = document.getElementById('selected-count');
const modalEmpCount = document.getElementById('modal-emp-count');

function updateSelection() {
    const checked = document.querySelectorAll('.emp-checkbox:checked');
    const total = checkboxes().length;
    const count = checked.length;

    selectedCountEl.textContent = count + ' karyawan dipilih';
    btnSetSchedule.disabled = count === 0;
    selectAll.checked = total > 0 && count === total;
    selectAll.indeterminate = count > 0 && count < total;
    modalEmpCount.textContent = count;
}

selectAll.addEventListener('change', function() {
    checkboxes().forEach(cb => cb.checked = this.checked);
    updateSelection();
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('emp-checkbox')) {
        updateSelection();
    }
});

// Populate hidden employee_ids before form submit
document.getElementById('scheduleModal').addEventListener('show.bs.modal', function() {
    const container = document.getElementById('hidden-employee-ids');
    container.innerHTML = '';
    document.querySelectorAll('.emp-checkbox:checked').forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'employee_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    modalEmpCount.textContent = document.querySelectorAll('.emp-checkbox:checked').length;
});

// Toggle day off
function toggleDayOff(day) {
    const checkbox = document.getElementById('dayoff-' + day);
    const hidden = document.getElementById('dayoff-hidden-' + day);
    const clockIn = document.getElementById('clockin-' + day);
    const clockOut = document.getElementById('clockout-' + day);
    const label = document.getElementById('dayoff-label-' + day);

    if (checkbox.checked) {
        hidden.value = '1';
        clockIn.disabled = true;
        clockOut.disabled = true;
        clockIn.value = '';
        clockOut.value = '';
        label.textContent = 'Libur';
    } else {
        hidden.value = '0';
        clockIn.disabled = false;
        clockOut.disabled = false;
        clockIn.value = '08:00';
        clockOut.value = '17:00';
        label.textContent = 'Kerja';
    }
}

// Quick templates
function applyTemplate(type) {
    for (let day = 1; day <= 7; day++) {
        const checkbox = document.getElementById('dayoff-' + day);
        const hidden = document.getElementById('dayoff-hidden-' + day);
        const clockIn = document.getElementById('clockin-' + day);
        const clockOut = document.getElementById('clockout-' + day);
        const label = document.getElementById('dayoff-label-' + day);

        if (type === 'weekday') {
            if (day <= 5) {
                checkbox.checked = false;
                hidden.value = '0';
                clockIn.disabled = false;
                clockOut.disabled = false;
                clockIn.value = '08:00';
                clockOut.value = '17:00';
                label.textContent = 'Kerja';
            } else {
                checkbox.checked = true;
                hidden.value = '1';
                clockIn.disabled = true;
                clockOut.disabled = true;
                clockIn.value = '';
                clockOut.value = '';
                label.textContent = 'Libur';
            }
        } else if (type === 'weekday-sat') {
            if (day <= 5) {
                checkbox.checked = false;
                hidden.value = '0';
                clockIn.disabled = false;
                clockOut.disabled = false;
                clockIn.value = '08:00';
                clockOut.value = '17:00';
                label.textContent = 'Kerja';
            } else if (day === 6) {
                checkbox.checked = false;
                hidden.value = '0';
                clockIn.disabled = false;
                clockOut.disabled = false;
                clockIn.value = '08:00';
                clockOut.value = '12:00';
                label.textContent = 'Kerja';
            } else {
                checkbox.checked = true;
                hidden.value = '1';
                clockIn.disabled = true;
                clockOut.disabled = true;
                clockIn.value = '';
                clockOut.value = '';
                label.textContent = 'Libur';
            }
        }
    }
}

// Form validation
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    for (let day = 1; day <= 7; day++) {
        const hidden = document.getElementById('dayoff-hidden-' + day);
        const clockIn = document.getElementById('clockin-' + day);
        const clockOut = document.getElementById('clockout-' + day);

        if (hidden.value === '0') {
            if (!clockIn.value || !clockOut.value) {
                e.preventDefault();
                alert('Harap isi jam masuk dan pulang untuk semua hari kerja.');
                return;
            }
        }
    }
});
</script>
@endpush
