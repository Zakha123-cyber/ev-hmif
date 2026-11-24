<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Pengaturan pemilihan (satu baris). Jadwal diatur admin lewat panel.
 */
class ElectionSetting extends Model
{
    public const STATUS_NOT_SET = 'not_set';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = ['vote_start', 'vote_end'];

    protected $casts = [
        'vote_start' => 'datetime',
        'vote_end' => 'datetime',
    ];

    public static function current(): self
    {
        return static::query()->firstOrNew();
    }

    public function status(?Carbon $now = null): string
    {
        $now ??= Carbon::now();

        if (! $this->vote_start || ! $this->vote_end) {
            return self::STATUS_NOT_SET;
        }

        if ($now->lt($this->vote_start)) {
            return self::STATUS_UPCOMING;
        }

        if ($now->gt($this->vote_end)) {
            return self::STATUS_CLOSED;
        }

        return self::STATUS_OPEN;
    }

    public function isOpen(?Carbon $now = null): bool
    {
        return $this->status($now) === self::STATUS_OPEN;
    }
}
