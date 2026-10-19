<?php

namespace App\Services;

use App\Exceptions\AttendanceException;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logika inti Modul Absensi (PRD §3.A).
 *
 * Server-Side Time Locking: seluruh waktu clock-in / clock-out diambil dari
 * now() milik server. Tidak ada satu pun parameter waktu dari klien yang
 * diterima oleh service ini — klien hanya mengirim koordinat lokasi.
 */
class AttendanceService
{
    /**
     * Jadwal yang relevan untuk absensi saat ini:
     *  1. Shift malam kemarin yang masih berjalan (lintas hari), atau
     *  2. Jadwal hari ini.
     */
    public function relevantSchedule(User $user, ?CarbonInterface $now = null): ?Schedule
    {
        $now = Carbon::instance($now ?? now());
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        $schedules = Schedule::with('shift')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$yesterday, $today])
            ->get();

        $overnight = $schedules->first(function (Schedule $s) use ($yesterday, $now) {
            return $s->date->toDateString() === $yesterday
                && $s->shift->isOvernight()
                && $now->lessThanOrEqualTo($s->endsAt());
        });

        return $overnight ?? $schedules->first(fn (Schedule $s) => $s->date->toDateString() === $today);
    }

    /**
     * Sesi absensi yang sudah clock-in tetapi belum clock-out.
     */
    public function openAttendance(User $user, ?CarbonInterface $now = null): ?Attendance
    {
        $now = Carbon::instance($now ?? now());
        $limit = $now->copy()->subHours(config('hris.max_open_session_hours', 20));

        return Attendance::with('schedule.shift')
            ->where('user_id', $user->id)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->where('clock_in_time', '>=', $limit)
            ->latest('clock_in_time')
            ->first();
    }

    /**
     * Absensi yang sudah tercatat untuk jadwal (atau hari ini bila tanpa jadwal).
     */
    public function recordedAttendance(User $user, ?Schedule $schedule, ?CarbonInterface $now = null): ?Attendance
    {
        $now = Carbon::instance($now ?? now());

        if ($schedule) {
            return Attendance::where('schedule_id', $schedule->id)
                ->whereNotNull('clock_in_time')
                ->first();
        }

        return Attendance::where('user_id', $user->id)
            ->whereNull('schedule_id')
            ->whereNotNull('clock_in_time')
            ->whereDate('clock_in_time', $now->toDateString())
            ->latest('clock_in_time')
            ->first();
    }

    /**
     * Ringkasan status absensi untuk ditampilkan di halaman karyawan.
     *
     * @return array{schedule: ?Schedule, open: ?Attendance, recorded: ?Attendance, action: string, message: ?string, onLeave: ?LeaveRequest}
     */
    public function status(User $user, ?CarbonInterface $now = null): array
    {
        $now = Carbon::instance($now ?? now());
        $schedule = $this->relevantSchedule($user, $now);
        $open = $this->openAttendance($user, $now);
        $recorded = $open ?? $this->recordedAttendance($user, $schedule, $now);
        $onLeave = LeaveRequest::approved()->where('user_id', $user->id)->covering($now)->first();

        $action = 'clock_in';
        $message = null;

        if ($open) {
            $action = 'clock_out';
        } elseif ($recorded) {
            $action = 'done';
            $message = 'Presensi hari ini selesai.';
        } elseif ($onLeave) {
            $action = 'leave';
            $message = 'Anda sedang '.strtolower($onLeave->type_label).' hari ini.';
        } elseif ($schedule) {
            $opensAt = $schedule->startsAt()->subMinutes(config('hris.clock_in_early_minutes', 120));

            if ($now->lessThan($opensAt)) {
                $action = 'not_yet';
                $message = 'Clock-in dibuka pukul '.$opensAt->format('H:i').'.';
            } elseif ($schedule->hasEnded($now)) {
                $action = 'ended';
                $message = 'Shift hari ini telah berakhir.';
            }
        } elseif (! config('hris.allow_unscheduled_clock_in', true)) {
            $action = 'no_schedule';
            $message = 'Tidak ada jadwal hari ini.';
        }

        return compact('schedule', 'open', 'recorded', 'action', 'message', 'onLeave');
    }

    /**
     * Clock-in: waktu = now() server, koordinat dari Geolocation API klien.
     *
     * @throws AttendanceException
     */
    public function clockIn(User $user, float|string $lat, float|string $lng): Attendance
    {
        return DB::transaction(function () use ($user, $lat, $lng) {
            // Kunci baris user untuk mencegah double-submit bersamaan.
            User::whereKey($user->id)->lockForUpdate()->first();

            $now = now();

            if ($this->openAttendance($user, $now)) {
                throw new AttendanceException('Anda sudah clock-in. Silakan clock-out terlebih dahulu.');
            }

            $this->assertWithinGeofence($lat, $lng);

            $schedule = $this->relevantSchedule($user, $now);
            $status = Attendance::STATUS_HADIR;

            if ($schedule) {
                if ($this->recordedAttendance($user, $schedule, $now)) {
                    throw new AttendanceException('Presensi untuk shift ini sudah tercatat.');
                }

                $start = $schedule->startsAt();
                $opensAt = $start->copy()->subMinutes(config('hris.clock_in_early_minutes', 120));

                if ($now->lessThan($opensAt)) {
                    throw new AttendanceException('Clock-in dibuka pukul '.$opensAt->format('H:i').'.');
                }

                if ($schedule->hasEnded($now)) {
                    throw new AttendanceException('Shift sudah berakhir, clock-in tidak dapat dilakukan.');
                }

                $lateAfter = $start->copy()->addMinutes(config('hris.late_tolerance_minutes', 15));
                $status = $now->greaterThan($lateAfter) ? Attendance::STATUS_TELAT : Attendance::STATUS_HADIR;

                // Gunakan record "alpha" bila sudah dibuat sebelumnya untuk jadwal ini.
                $attendance = Attendance::firstOrNew(['schedule_id' => $schedule->id], ['user_id' => $user->id]);
            } else {
                if (! config('hris.allow_unscheduled_clock_in', true)) {
                    throw new AttendanceException('Tidak ada jadwal shift untuk hari ini.');
                }

                if ($this->recordedAttendance($user, null, $now)) {
                    throw new AttendanceException('Presensi hari ini sudah tercatat.');
                }

                $attendance = new Attendance(['user_id' => $user->id]);
            }

            $attendance->forceFill([
                'user_id' => $user->id,
                'schedule_id' => $schedule?->id,
                'clock_in_time' => $now,
                'lat_in' => $this->formatCoordinate($lat),
                'lng_in' => $this->formatCoordinate($lng),
                'status' => $status,
            ])->save();

            return $attendance->refresh();
        });
    }

    /**
     * Clock-out: waktu = now() server.
     *
     * @throws AttendanceException
     */
    public function clockOut(User $user, float|string $lat, float|string $lng): Attendance
    {
        return DB::transaction(function () use ($user, $lat, $lng) {
            User::whereKey($user->id)->lockForUpdate()->first();

            $now = now();
            $attendance = $this->openAttendance($user, $now);

            if (! $attendance) {
                throw new AttendanceException('Belum ada clock-in aktif.');
            }

            $this->assertWithinGeofence($lat, $lng);

            $attendance->forceFill([
                'clock_out_time' => $now,
                'lat_out' => $this->formatCoordinate($lat),
                'lng_out' => $this->formatCoordinate($lng),
            ])->save();

            return $attendance->refresh();
        });
    }

    /**
     * Tandai "alpha" untuk jadwal yang sudah berakhir tanpa absensi
     * (kecuali karyawan memiliki cuti/izin yang disetujui pada tanggal tsb).
     *
     * @return int jumlah record alpha yang dibuat
     */
    public function markAlpha(?CarbonInterface $now = null, int $lookbackDays = 7): int
    {
        $now = Carbon::instance($now ?? now());
        $created = 0;

        Schedule::with('shift')
            ->whereBetween('date', [$now->copy()->subDays($lookbackDays)->toDateString(), $now->toDateString()])
            ->whereDoesntHave('attendance')
            ->orderBy('id')
            ->chunkById(200, function ($schedules) use ($now, &$created) {
                foreach ($schedules as $schedule) {
                    if (! $schedule->hasEnded($now)) {
                        continue;
                    }

                    $onLeave = LeaveRequest::approved()
                        ->where('user_id', $schedule->user_id)
                        ->covering($schedule->date)
                        ->exists();

                    if ($onLeave) {
                        continue;
                    }

                    Attendance::create([
                        'user_id' => $schedule->user_id,
                        'schedule_id' => $schedule->id,
                        'status' => Attendance::STATUS_ALPHA,
                    ]);

                    $created++;
                }
            });

        return $created;
    }

    /**
     * Jarak (meter) antara dua koordinat — rumus Haversine.
     */
    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Validasi geofence opsional (config hris.office.enforce).
     *
     * @throws AttendanceException
     */
    protected function assertWithinGeofence(float|string $lat, float|string $lng): void
    {
        $office = config('hris.office');

        if (! ($office['enforce'] ?? false) || $office['lat'] === null || $office['lng'] === null) {
            return;
        }

        $distance = self::distanceMeters((float) $lat, (float) $lng, (float) $office['lat'], (float) $office['lng']);

        if ($distance > (int) $office['radius_meters']) {
            throw new AttendanceException(sprintf(
                'Lokasi Anda %s m dari %s (maks. %d m).',
                number_format($distance, 0, ',', '.'),
                $office['name'],
                $office['radius_meters'],
            ));
        }
    }

    protected function formatCoordinate(float|string $value): string
    {
        return number_format((float) $value, 7, '.', '');
    }
}
