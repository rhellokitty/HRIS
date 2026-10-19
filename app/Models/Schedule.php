<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class);
    }

    public function scopeForDate(Builder $query, CarbonInterface|string $date): Builder
    {
        return $query->whereDate('date', Carbon::parse($date)->toDateString());
    }

    public function scopeBetweenDates(Builder $query, CarbonInterface|string $from, CarbonInterface|string $to): Builder
    {
        return $query->whereBetween('date', [
            Carbon::parse($from)->toDateString(),
            Carbon::parse($to)->toDateString(),
        ]);
    }

    public function startsAt(): Carbon
    {
        return $this->shift->startsAt($this->date);
    }

    public function endsAt(): Carbon
    {
        return $this->shift->endsAt($this->date);
    }

    /**
     * Apakah shift ini sedang berjalan pada waktu tertentu (default: sekarang).
     */
    public function isRunning(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $at->between($this->startsAt(), $this->endsAt());
    }

    public function hasEnded(?CarbonInterface $at = null): bool
    {
        return ($at ?? now())->greaterThan($this->endsAt());
    }
}
