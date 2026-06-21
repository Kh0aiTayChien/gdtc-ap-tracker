<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApRecord extends Model
{
    protected $fillable = [
        'team_id', 'floor', 'ap_no', 'ap_name', 'status', 'work_date',
        'location_photo', 'mac_photo', 'cable_photo',
        'issue_reason', 'issue_note', 'issue_photo',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $record): void {
            $record->floor = strtoupper(trim($record->floor));
            $record->ap_name = "{$record->floor}-AP{$record->ap_no}";
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
