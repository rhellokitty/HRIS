<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Toleransi Keterlambatan
    |--------------------------------------------------------------------------
    | Jumlah menit setelah jam mulai shift di mana clock-in masih dianggap
    | "hadir". Lewat dari batas ini status otomatis menjadi "telat".
    */
    'late_tolerance_minutes' => (int) env('HRIS_LATE_TOLERANCE_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Jendela Clock-In
    |--------------------------------------------------------------------------
    | Clock-in hanya dapat dilakukan mulai N menit sebelum shift dimulai
    | hingga jam selesai shift.
    */
    'clock_in_early_minutes' => (int) env('HRIS_CLOCK_IN_EARLY_MINUTES', 120),

    /*
    |--------------------------------------------------------------------------
    | Izinkan Absen Tanpa Jadwal
    |--------------------------------------------------------------------------
    | Jika true, karyawan yang tidak memiliki jadwal hari ini tetap dapat
    | clock-in (schedule_id = null, status = hadir).
    */
    'allow_unscheduled_clock_in' => (bool) env('HRIS_ALLOW_UNSCHEDULED', true),

    /*
    |--------------------------------------------------------------------------
    | Batas Sesi Absen Terbuka
    |--------------------------------------------------------------------------
    | Clock-in yang belum di-clock-out lebih lama dari N jam dianggap kedaluwarsa
    | (tidak bisa clock-out lagi).
    */
    'max_open_session_hours' => (int) env('HRIS_MAX_OPEN_SESSION_HOURS', 20),

    /*
    |--------------------------------------------------------------------------
    | Titik Lokasi Kantor (Geofence Opsional)
    |--------------------------------------------------------------------------
    | Koordinat dasar kantor untuk menampilkan jarak pengguna. Jika `enforce`
    | true, absen di luar radius akan ditolak oleh server.
    */
    'office' => [
        'name' => env('HRIS_OFFICE_NAME', 'Kantor Pusat'),
        'lat' => env('HRIS_OFFICE_LAT') !== null ? (float) env('HRIS_OFFICE_LAT') : null,
        'lng' => env('HRIS_OFFICE_LNG') !== null ? (float) env('HRIS_OFFICE_LNG') : null,
        'radius_meters' => (int) env('HRIS_OFFICE_RADIUS', 100),
        'enforce' => (bool) env('HRIS_GEOFENCE_ENFORCE', false),
    ],

];
