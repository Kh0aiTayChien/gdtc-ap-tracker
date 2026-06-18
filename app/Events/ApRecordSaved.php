<?php

namespace App\Events;

use App\Models\ApRecord;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApRecordSaved implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public ApRecord $record) {}

    public function broadcastOn(): array
    {
        return [new Channel('ap-records')];
    }

    public function broadcastAs(): string
    {
        return 'ApRecordSaved';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->record->id,
            'team_name' => $this->record->team?->name,
            'floor' => $this->record->floor,
            'ap_no' => $this->record->ap_no,
            'ap_name' => $this->record->ap_name,
            'status' => $this->record->status,
            'issue_reason' => $this->record->issue_reason,
            'created_at' => $this->record->created_at?->toIso8601String(),
            'updated_at' => $this->record->updated_at?->toIso8601String(),
        ];
    }
}
