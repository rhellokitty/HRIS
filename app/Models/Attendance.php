<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_HADIR = 'hadir';
    public const STATUS_TELAT = 'telat';
    public const STATUS_ALPHA = 'alpha';

    public const STATUSES = [
        self::STATUS_HADIR,
        self::STATUS_TELAT,
        self::STATUS_ALPHA,
    ];

    public const STATUS_LABELS = [
        self::STATUS_HADIR => 'Tepat Waktu',
        self::STATUS_TELAT => 'Terlambat',
        self::STATUS_ALPHA => 'Alpha',
    ];

    /**
     * clock_in_time & clock_out_time sengaja TIDAK dapat diisi dari input
     * pengguna di controller — selalu diset dari now() oleh AttendanceService.
     */
    protected $fillable = [
        'user_id',
        'schedule_id',
        'clock_in_time',
        'clock_out_time',
        'lat_in',
        'lng_in',
        'lat_out',
        'lng_out',
        'status',
    ];

    protected $attributes = [
        'status' => self::STATUS_ALPHA,
    ];

    protected function casts(): array
    {
        return [
            'clock_in_time' => 'datetime',
            'clock_out_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function scopeClockedInOn(Builder $query, $date): Builder
    {
        return $query->whereDate('clock_in_time', $date);
    }

    public function scopePresent(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_HADIR, self::STATUS_TELAT])
            ->whereNotNull('clock_in_time');
    }

    public function isOpen(): bool
    {
        return $this->clock_in_time !== null && $this->clock_out_time === null;
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn() => self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status));
    }

    protected function workDurationLabel(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->clock_in_time || !$this->clock_out_time) {
                return null;
            }

            $minutes = (int) $this->clock_in_time->diffInMinutes($this->clock_out_time);

            return sprintf('%dj %02dm', intdiv($minutes, 60), $minutes % 60);
        });
    }

    protected function mapUrlIn(): Attribute
    {
        return Attribute::get(fn() => self::mapUrl($this->lat_in, $this->lng_in));
    }

    protected function mapUrlOut(): Attribute
    {
        return Attribute::get(fn() => self::mapUrl($this->lat_out, $this->lng_out));
    }

    public static function mapUrl(?string $lat, ?string $lng): ?string
    {
        if (blank($lat) || blank($lng)) {
            return null;
        }

        return 'https://www.google.com/maps?q=' . urlencode($lat . ',' . $lng);
    }
}
