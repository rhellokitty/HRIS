<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    public const TYPE_CUTI = 'cuti';
    public const TYPE_IZIN = 'izin';
    public const TYPE_SAKIT = 'sakit';

    public const TYPES = [
        self::TYPE_CUTI => 'Cuti',
        self::TYPE_IZIN => 'Izin',
        self::TYPE_SAKIT => 'Sakit',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'type',
        'reason',
        'status',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Pengajuan yang mencakup tanggal tertentu.
     */
    public function scopeCovering(Builder $query, CarbonInterface|string $date): Builder
    {
        $day = Carbon::parse($date)->toDateString();

        return $query->whereDate('start_date', '<=', $day)->whereDate('end_date', '>=', $day);
    }

    /**
     * Pengajuan yang beririsan dengan rentang tanggal.
     */
    public function scopeOverlapping(Builder $query, CarbonInterface|string $from, CarbonInterface|string $to): Builder
    {
        return $query->whereDate('start_date', '<=', Carbon::parse($to)->toDateString())
            ->whereDate('end_date', '>=', Carbon::parse($from)->toDateString());
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    protected function durationDays(): Attribute
    {
        return Attribute::get(fn () => (int) $this->start_date->diffInDays($this->end_date) + 1);
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => self::TYPES[$this->type] ?? ucfirst((string) $this->type));
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? ucfirst((string) $this->status));
    }
}
