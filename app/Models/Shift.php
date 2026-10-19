<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Nama shift bersih: otomatis menghapus kode hash/hex acak teknis jika ada
     * (contoh: "Shift Khusus 6ad5d742b707c" menjadi "Shift Khusus").
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => trim(preg_replace('/\s+[0-9a-f]{6,}$/i', '', (string) $value)) ?: (string) $value,
            set: fn ($value) => trim(preg_replace('/\s+[0-9a-f]{6,}$/i', '', (string) $value)) ?: (string) $value,
        );
    }

    /**
     * Shift lintas hari (mis. Malam 22:00 - 06:00).
     */
    public function isOvernight(): bool
    {
        return $this->normalizeTime($this->end_time) <= $this->normalizeTime($this->start_time);
    }

    /**
     * Waktu mulai shift pada tanggal tertentu (zona waktu aplikasi).
     */
    public function startsAt(CarbonInterface|string $date): Carbon
    {
        $day = Carbon::parse($date)->toDateString();

        return Carbon::parse($day.' '.$this->normalizeTime($this->start_time));
    }

    /**
     * Waktu selesai shift pada tanggal tertentu (otomatis +1 hari untuk shift malam).
     */
    public function endsAt(CarbonInterface|string $date): Carbon
    {
        $day = Carbon::parse($date)->toDateString();
        $end = Carbon::parse($day.' '.$this->normalizeTime($this->end_time));

        return $this->isOvernight() ? $end->addDay() : $end;
    }

    public function durationMinutes(): int
    {
        $start = $this->startsAt('2000-01-01');

        return (int) $start->diffInMinutes($this->endsAt('2000-01-01'));
    }

    protected function startLabel(): Attribute
    {
        return Attribute::get(fn () => substr($this->normalizeTime($this->start_time), 0, 5));
    }

    protected function endLabel(): Attribute
    {
        return Attribute::get(fn () => substr($this->normalizeTime($this->end_time), 0, 5));
    }

    protected function timeRange(): Attribute
    {
        return Attribute::get(fn () => $this->start_label.' – '.$this->end_label);
    }

    protected function durationLabel(): Attribute
    {
        return Attribute::get(function () {
            $minutes = $this->durationMinutes();
            $hours = intdiv($minutes, 60);
            $rest = $minutes % 60;

            return $rest ? "{$hours}j {$rest}m" : "{$hours} jam";
        });
    }

    private function normalizeTime(?string $time): string
    {
        $time = (string) $time;

        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
