<?php

namespace App\Services;

use App\Models\ApRecord;
use App\Models\FloorConfig;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FloorProgressService
{
    public function summary(?Team $team = null): array
    {
        $records = ApRecord::query()
            ->with('team')
            ->when($team, fn (Builder $q) => $q->where('team_id', $team->id))
            ->orderBy('floor')
            ->orderBy('ap_no')
            ->get();

        $configs = FloorConfig::query()->orderBy('sort_order')->orderBy('floor')->get()->keyBy('floor');
        $recordGroups = $records->groupBy('floor');
        $floorNames = $configs->keys()->merge($recordGroups->keys())->unique()
            ->sortBy(fn (string $floor) => $configs[$floor]->sort_order ?? FloorConfig::sortValue($floor))
            ->values();

        $floors = $floorNames->map(function (string $floor) use ($configs, $recordGroups) {
            $items = ($recordGroups[$floor] ?? collect())->sortBy('ap_no')->values();
            $configuredTotal = (int) ($configs[$floor]->target_ap_count ?? 0);
            $recordedTotal = $items->count();
            $targetTotal = max($configuredTotal, $recordedTotal);
            $installed = $items->where('status', 'installed')->count();
            $blocked = $items->where('status', 'blocked')->count();
            $remaining = max(0, $targetTotal - $installed - $blocked);

            return (object) [
                'floor' => $floor,
                'total' => $targetTotal,
                'configured_total' => $configuredTotal,
                'recorded_total' => $recordedTotal,
                'installed' => $installed,
                'blocked' => $blocked,
                'remaining' => $remaining,
                'percent' => $targetTotal > 0 ? round(($installed / $targetTotal) * 100, 1) : 0,
                'records' => $items,
            ];
        });

        return compact('floors', 'records', 'team');
    }

    public function floorOptions(): Collection
    {
        $configured = FloorConfig::query()->orderBy('sort_order')->orderBy('floor')->pluck('floor');
        $recorded = ApRecord::query()->distinct()->pluck('floor');

        return $configured->merge($recorded)->unique()
            ->sortBy(fn (string $floor) => FloorConfig::sortValue($floor))
            ->values();
    }
}
