@extends('layouts.employee')

@section('title', 'Presensi Karyawan')

@section('content')
<div class="flex flex-col gap-5 animate-fade-up">

    {{-- Widget Utama Presensi GPS (Design PRD §5.1) --}}
    <section class="glass-card p-6 flex flex-col items-center gap-4 text-center">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-hris-text-muted">
            {{ now()->translatedFormat('l, d F Y') }}
        </span>

        {{-- Display Jam Digital Live ala iOS --}}
        <div class="flex items-baseline justify-center gap-1.5 tabular-nums">
            <span class="text-5xl sm:text-6xl font-bold tracking-tight text-hris-text" id="attendance-time">
                {{ now()->format('H:i') }}
            </span>
            <div class="flex flex-col items-start leading-none">
                <span class="text-lg font-medium text-hris-text-muted" id="attendance-seconds">:{{ now()->format('s') }}</span>
                <span class="text-[9px] font-bold text-hris-primary tracking-wider">WIB</span>
            </div>
        </div>

        {{-- Detail Shift Terjadwal Menggunakan x-shift-badge --}}
        <div class="flex items-center justify-center gap-2">
            <x-shift-badge :shift="$schedule?->shift" />
            @if ($schedule?->shift)
                <span class="text-xs text-hris-text-secondary font-mono">{{ $schedule->shift->time_range }} WIB</span>
            @endif
        </div>

        {{-- Indikator Status Geolocation GPS --}}
        <div class="w-full flex flex-col gap-2 p-3 rounded-md bg-hris-surface text-xs text-left" id="geo-box">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="map-pin" class="w-4 h-4 text-hris-primary" />
                    <span class="font-semibold text-hris-text" id="geo-status-label">Mendeteksi Titik GPS...</span>
                </div>
                <button type="button" onclick="detectLocation()" class="text-[10px] text-hris-primary font-semibold hover:underline">
                    Perbarui GPS
                </button>
            </div>

            <div class="text-[11px] text-hris-text-secondary flex flex-wrap gap-x-3 gap-y-1 font-mono" id="geo-coords">
                <span>Lat: <strong id="lat-display">-</strong></span>
                <span>Lng: <strong id="lng-display">-</strong></span>
                <span>Akurasi: <strong id="acc-display">-</strong></span>
            </div>

            <div id="geo-map-link" class="hidden">
                <a href="#" target="_blank" rel="noopener" class="text-[10px] text-hris-info hover:underline inline-flex items-center gap-1">
                    <x-icon name="external" class="w-3 h-3" />
                    <span>Buka Peta Google Maps</span>
                </a>
            </div>
        </div>

        {{-- Informasi Anti-Manipulasi & Server-Side Time Locking --}}
        <div class="w-full p-2.5 rounded-md bg-hris-surface/60 text-[10px] text-hris-text-muted flex items-center gap-2 text-left">
            <x-icon name="shield" class="w-4 h-4 text-hris-primary shrink-0" />
            <span>Waktu presensi dikunci mutlak dari jam server Laravel (Anti-Manipulasi Jam HP).</span>
        </div>

        {{-- Form Aksi Clock-In / Clock-Out --}}
        @if ($action === 'clock_out')
            {{-- Tombol Clock-Out --}}
            <form method="POST" action="{{ route('employee.attendance.clock-out') }}" id="attendance-form" class="w-full">
                @csrf
                <input type="hidden" name="latitude" id="form-latitude">
                <input type="hidden" name="longitude" id="form-longitude">

                <div class="mb-3 p-3 rounded-md bg-hris-success-soft text-hris-success text-xs flex items-center justify-between">
                    <span>Masuk: <strong>{{ $openAttendance->clock_in_time->format('H:i') }} WIB</strong></span>
                    <span class="pill-success">{{ $openAttendance->status_label }}</span>
                </div>

                <button type="submit" id="btn-submit" disabled
                        class="btn-danger btn-lg w-full text-sm font-semibold transition-all">
                    Clock-Out Pulang
                </button>
            </form>
        @elseif ($action === 'clock_in')
            {{-- Tombol Clock-In --}}
            <form method="POST" action="{{ route('employee.attendance.clock-in') }}" id="attendance-form" class="w-full">
                @csrf
                <input type="hidden" name="latitude" id="form-latitude">
                <input type="hidden" name="longitude" id="form-longitude">

                <button type="submit" id="btn-submit" disabled
                        class="btn-primary btn-lg w-full text-sm font-semibold transition-all">
                    Clock-In Masuk
                </button>
            </form>
        @elseif ($action === 'done')
            <div class="w-full p-4 rounded-md bg-hris-success-soft text-hris-success flex flex-col gap-2">
                <div class="flex items-center justify-center gap-2 font-bold text-sm">
                    <x-icon name="check-circle" class="w-5 h-5" />
                    <span>Presensi Hari Ini Selesai</span>
                </div>
                <div class="text-xs text-hris-text flex justify-around pt-2 border-t border-hris-success/20">
                    <div>Masuk: <strong>{{ $recordedAttendance->clock_in_time?->format('H:i') }}</strong></div>
                    <div>Pulang: <strong>{{ $recordedAttendance->clock_out_time?->format('H:i') }}</strong></div>
                    <div>Durasi: <strong>{{ $recordedAttendance->work_duration_label }}</strong></div>
                </div>
            </div>
        @else
            {{-- Status Khusus: Belum waktu absen, shift berakhir, atau izin --}}
            <div class="w-full p-4 rounded-md bg-hris-surface text-center flex flex-col gap-2">
                <span class="text-xs font-semibold text-hris-warning">{{ $statusMessage }}</span>
            </div>
        @endif

    </section>

    {{-- Riwayat Presensi Singkat --}}
    @if ($recentAttendances->isNotEmpty())
        <section class="glass-card p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h3 class="section-title">Riwayat Terkini</h3>
                <a href="{{ route('employee.attendance.history') }}" class="text-xs text-hris-primary hover:underline">
                    Semua
                </a>
            </div>

            <div class="flex flex-col gap-2">
                @foreach ($recentAttendances as $item)
                    <div class="glass-surface p-3 flex items-center justify-between text-xs">
                        <div class="flex flex-col">
                            <span class="font-bold text-hris-text">
                                {{ $item->clock_in_time ? $item->clock_in_time->translatedFormat('d M Y') : 'Hari ini' }}
                            </span>
                            <span class="text-[10px] text-hris-text-muted">
                                {{ $item->clock_in_time?->format('H:i') }} – {{ $item->clock_out_time?->format('H:i') ?? 'Belum Pulang' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($item->status === 'hadir')
                                <span class="pill-success">Tepat Waktu</span>
                            @elseif ($item->status === 'telat')
                                <span class="pill-warning">Terlambat</span>
                            @else
                                <span class="pill-danger">Alpha</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>

@push('scripts')
<script>
    /* ==========================================================================
       1. Frontend Security (PRD §3.A Anti-Manipulasi)
       Menonaktifkan Klik Kanan (Context Menu) dan tombol Inspect Element (F12, Ctrl+Shift+I)
       ========================================================================== */
    document.addEventListener('contextmenu', function (e) {
        e.preventDefault();
        return false;
    });

    document.addEventListener('keydown', function (e) {
        // F12
        if (e.key === 'F12') {
            e.preventDefault();
            return false;
        }
        // Ctrl+Shift+I atau Ctrl+Shift+J atau Ctrl+Shift+C
        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) {
            e.preventDefault();
            return false;
        }
        // Ctrl+U (View Source)
        if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) {
            e.preventDefault();
            return false;
        }
    });

    /* ==========================================================================
       2. Jam Digital Live
       ========================================================================== */
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const timeEl = document.getElementById('attendance-time');
        const secEl = document.getElementById('attendance-seconds');
        if (timeEl) timeEl.textContent = `${h}:${m}`;
        if (secEl) secEl.textContent = `:${s}`;
    }
    setInterval(updateClock, 1000);

    /* ==========================================================================
       3. Geolocation API (HTML5 navigator.geolocation)
       ========================================================================== */
    let currentCoords = null;

    function detectLocation() {
        const label = document.getElementById('geo-status-label');
        const btnSubmit = document.getElementById('btn-submit');
        const latDisplay = document.getElementById('lat-display');
        const lngDisplay = document.getElementById('lng-display');
        const accDisplay = document.getElementById('acc-display');
        const mapLinkWrap = document.getElementById('geo-map-link');

        if (!navigator.geolocation) {
            if (label) label.textContent = 'Browser tidak mendukung GPS/Geolocation.';
            return;
        }

        if (label) label.textContent = 'Mengunci koordinat GPS...';

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const acc = Math.round(position.coords.accuracy);

                currentCoords = { lat, lng };

                // Isi form tersembunyi
                const formLat = document.getElementById('form-latitude');
                const formLng = document.getElementById('form-longitude');
                if (formLat) formLat.value = lat;
                if (formLng) formLng.value = lng;

                if (latDisplay) latDisplay.textContent = lat.toFixed(6);
                if (lngDisplay) lngDisplay.textContent = lng.toFixed(6);
                if (accDisplay) accDisplay.textContent = `±${acc}m`;

                if (label) {
                    label.textContent = `GPS Terkunci (Akurasi: ±${acc}m)`;
                    label.classList.add('text-hris-success');
                }

                if (mapLinkWrap) {
                    mapLinkWrap.classList.remove('hidden');
                    const link = mapLinkWrap.querySelector('a');
                    if (link) link.href = `https://www.google.com/maps?q=${lat},${lng}`;
                }

                // Aktifkan tombol submit
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                }
            },
            function (error) {
                let msg = 'Gagal mengakses GPS.';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        msg = 'Akses lokasi ditolak. Aktifkan GPS dan izinkan izin lokasi di browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        msg = 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        msg = 'Waktu permintaan lokasi habis. Coba lagi.';
                        break;
                }
                if (label) {
                    label.textContent = msg;
                    label.classList.add('text-hris-danger');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    }

    // Jalankan deteksi lokasi otomatis saat halaman dimuat
    document.addEventListener('DOMContentLoaded', detectLocation);
</script>
@endpush
@endsection
