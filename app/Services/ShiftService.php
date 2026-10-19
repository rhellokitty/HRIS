<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftService
{
    /**
     * Auto-Generate Jadwal Mingguan (PRD §3.B).
     *
     * Membuatkan jadwal untuk semua karyawan selama 1 pekan (Senin - Minggu)
     * berdasarkan pola shift pekan sebelumnya (atau default shift jika belum ada).
     *
     * @return array{created: int, skipped: int, start_date: string, end_date: string}
     */
    public function generateWeekly(?CarbonInterface $startDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfWeek() : now()->addWeek()->startOfWeek();
        $end = $start->copy()->endOfWeek(); // Senin - Minggu (7 hari)

        $employees = User::karyawan()->get();
        $shifts = Shift::orderBy('start_time')->get();

        if ($shifts->isEmpty()) {
            throw new \RuntimeException('Tidak ada data Master Shift. Buat minimal satu shift terlebih dahulu.');
        }

        $defaultShift = $shifts->first();
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($employees, $shifts, $defaultShift, $start, &$created, &$skipped) {
            foreach ($employees as $employee) {
                for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                    $targetDate = $start->copy()->addDays($dayOffset);

                    // Skip hari libur akhir pekan jika diinginkan (Senin - Jumat kerja, Sabtu/Minggu libur jika tidak ada shift)
                    // Pola: Jika hari Sabtu/Minggu dan pekan sebelumnya tidak ada shift, biarkan libur.
                    $isWeekend = $targetDate->isWeekend();

                    // Cek apakah jadwal tanggal ini sudah ada
                    $exists = Schedule::where('user_id', $employee->id)
                        ->whereDate('date', $targetDate->toDateString())
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    // Cari pola shift pada hari yang sama di pekan sebelumnya
                    $previousWeekDate = $targetDate->copy()->subWeek();
                    $previousSchedule = Schedule::where('user_id', $employee->id)
                        ->whereDate('date', $previousWeekDate->toDateString())
                        ->first();

                    if ($previousSchedule) {
                        $shiftToAssign = $previousSchedule->shift_id;
                    } elseif (! $isWeekend) {
                        $shiftToAssign = $defaultShift->id;
                    } else {
                        // Akhir pekan tanpa pola sebelumnya -> libur (tidak dibuatkan jadwal)
                        continue;
                    }

                    Schedule::create([
                        'user_id' => $employee->id,
                        'shift_id' => $shiftToAssign,
                        'date' => $targetDate->toDateString(),
                    ]);

                    $created++;
                }
            }
        });

        return [
            'created' => $created,
            'skipped' => $skipped,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }
}
