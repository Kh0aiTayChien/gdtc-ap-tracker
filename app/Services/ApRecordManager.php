<?php

namespace App\Services;

use App\Events\ApRecordSaved;
use App\Models\ApRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ApRecordManager
{
    private const PHOTO_NAMES = [
        'location_photo' => 'location',
        'mac_photo' => 'mac-sn',
        'cable_photo' => 'cable',
        'issue_photo' => 'issue',
    ];

    public function save(?ApRecord $record, array $validated, ?int $teamId = null): ApRecord
    {
        $record ??= new ApRecord;
        $oldPaths = [];

        DB::transaction(function () use ($record, $validated, $teamId, &$oldPaths): void {
            $record->fill(Arr::except($validated, array_keys(self::PHOTO_NAMES)));

            if (! $record->exists && $teamId !== null) {
                $record->team_id = $teamId;
            }

            if ($record->status === 'installed') {
                $record->issue_reason = null;
                $record->issue_note = null;
                $oldPaths[] = $record->issue_photo;
                $record->issue_photo = null;
            } else {
                foreach (['location_photo', 'mac_photo', 'cable_photo'] as $field) {
                    $oldPaths[] = $record->{$field};
                    $record->{$field} = null;
                }
            }

            $record->save();

            foreach (self::PHOTO_NAMES as $field => $suffix) {
                if (($validated[$field] ?? null) instanceof UploadedFile) {
                    $oldPaths[] = $record->{$field};
                    $filename = "{$record->ap_name}-{$suffix}.jpg";
                    $record->{$field} = $validated[$field]->storeAs('ap-records', $filename, 'public');
                }
            }

            $record->save();
        });

        foreach (array_filter(array_unique($oldPaths)) as $path) {
            if (! in_array($path, self::currentPaths($record), true)) {
                Storage::disk('public')->delete($path);
            }
        }

        $record->load('team');

        try {
            ApRecordSaved::dispatch($record);
        } catch (Throwable $exception) {
            Log::warning('Realtime broadcast failed; record was saved.', ['exception' => $exception->getMessage()]);
        }

        return $record;
    }

    public function delete(ApRecord $record): void
    {
        $paths = self::currentPaths($record);
        $record->delete();
        Storage::disk('public')->delete($paths);
    }

    private static function currentPaths(ApRecord $record): array
    {
        return array_values(array_filter(array_map(fn ($field) => $record->{$field}, array_keys(self::PHOTO_NAMES))));
    }
}
