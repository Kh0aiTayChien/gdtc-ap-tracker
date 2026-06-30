<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloorConfig extends Model
{
    protected $fillable = ['floor', 'target_ap_count', 'sort_order'];

    protected function casts(): array
    {
        return [
            'target_ap_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $config): void {
            $config->floor = strtoupper(trim($config->floor));
            $config->target_ap_count = max(0, (int) $config->target_ap_count);
            $config->sort_order ??= self::sortValue($config->floor);
        });
    }

    public static function sortValue(string $floor): int
    {
        $floor = strtoupper(trim($floor));

        return $floor === 'G' ? 0 : ((int) str_replace('T', '', $floor) * 10);
    }
}
