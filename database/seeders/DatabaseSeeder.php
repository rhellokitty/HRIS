<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        // 1. Akun Pengguna Utama (Admin, Operator, Karyawan)
        $admin = User::firstOrCreate(
            ['email' => 'admin@hris.local'],
            [
                'name' => 'Admin Utama',
                'nip' => 'ADM-001',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('password'),
            ]
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator@hris.local'],
            [
                'name' => 'Operator Monitoring',
                'nip' => 'OPR-001',
                'role' => User::ROLE_OPERATOR,
                'password' => Hash::make('password'),
            ]
        );

        $karyawan1 = User::firstOrCreate(
            ['email' => 'daffa@hris.local'],
            [
                'name' => 'Muhammad Raja Daffa',
                'nip' => '20260819',
                'role' => User::ROLE_KARYAWAN,
                'password' => Hash::make('password'),
            ]
        );

        $karyawan2 = User::firstOrCreate(
            ['email' => 'budi@hris.local'],
            [
                'name' => 'Budi Santoso',
                'nip' => '20260820',
                'role' => User::ROLE_KARYAWAN,
                'password' => Hash::make('password'),
            ]
        );

        $karyawan3 = User::firstOrCreate(
            ['email' => 'siti@hris.local'],
            [
                'name' => 'Siti Rahma',
                'nip' => '20260821',
                'role' => User::ROLE_KARYAWAN,
                'password' => Hash::make('password'),
            ]
        );

        $allKaryawan = [$karyawan1, $karyawan2, $karyawan3];

        // 2. Master Shift (PRD §3.B)
        $shiftPagi = Shift::firstOrCreate(
            ['name' => 'Shift Pagi'],
            ['start_time' => '08:00', 'end_time' => '16:00']
        );

        $shiftSiang = Shift::firstOrCreate(
            ['name' => 'Shift Siang'],
            ['start_time' => '13:00', 'end_time' => '21:00']
        );

        $shiftMalam = Shift::firstOrCreate(
            ['name' => 'Shift Malam'],
            ['start_time' => '21:00', 'end_time' => '05:00']
        );

        $allShifts = [$shiftPagi, $shiftSiang, $shiftMalam];

        // 3. Plotting Jadwal Mingguan (Pekan ini)
        $startOfWeek = now()->startOfWeek();

        foreach ($allKaryawan as $idx => $emp) {
            for ($d = 0; $d < 5; $d++) { // Senin - Jumat
                $date = $startOfWeek->copy()->addDays($d)->toDateString();
                $shiftToAssign = $allShifts[($idx + $d) % count($allShifts)];

                Schedule::firstOrCreate(
                    [
                        'user_id' => $emp->id,
                        'date' => $date,
                    ],
                    [
                        'shift_id' => $shiftToAssign->id,
                    ]
                );
            }
        }

        // 4. Rekapan Presensi Contoh (Hari ini)
        $today = now()->toDateString();
        $todayScheduleKaryawan2 = Schedule::where('user_id', $karyawan2->id)->whereDate('date', $today)->first();
        if ($todayScheduleKaryawan2) {
            Attendance::firstOrCreate(
                ['schedule_id' => $todayScheduleKaryawan2->id],
                [
                    'user_id' => $karyawan2->id,
                    'clock_in_time' => Carbon::parse($today.' 07:55:12'),
                    'clock_out_time' => Carbon::parse($today.' 16:05:30'),
                    'lat_in' => '-6.2087634',
                    'lng_in' => '106.8455990',
                    'lat_out' => '-6.2087640',
                    'lng_out' => '106.8456010',
                    'status' => Attendance::STATUS_HADIR,
                ]
            );
        }

        $todayScheduleKaryawan3 = Schedule::where('user_id', $karyawan3->id)->whereDate('date', $today)->first();
        if ($todayScheduleKaryawan3) {
            Attendance::firstOrCreate(
                ['schedule_id' => $todayScheduleKaryawan3->id],
                [
                    'user_id' => $karyawan3->id,
                    'clock_in_time' => Carbon::parse($today.' 08:35:20'),
                    'clock_out_time' => null, // Masih kerja
                    'lat_in' => '-6.2091000',
                    'lng_in' => '106.8460000',
                    'status' => Attendance::STATUS_TELAT,
                ]
            );
        }

        // 5. Contoh Pengajuan Cuti / Izin
        LeaveRequest::firstOrCreate(
            [
                'user_id' => $karyawan1->id,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(4)->toDateString(),
            ],
            [
                'type' => LeaveRequest::TYPE_CUTI,
                'reason' => 'Keperluan keluarga di luar kota.',
                'status' => LeaveRequest::STATUS_PENDING,
            ]
        );

        LeaveRequest::firstOrCreate(
            [
                'user_id' => $karyawan2->id,
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->subDays(5)->toDateString(),
            ],
            [
                'type' => LeaveRequest::TYPE_SAKIT,
                'reason' => 'Demam dan istirahat dokter.',
                'status' => LeaveRequest::STATUS_APPROVED,
            ]
        );
    }
}
