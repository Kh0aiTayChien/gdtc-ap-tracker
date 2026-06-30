<?php

use App\Models\ApRecord;
use App\Models\FloorConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floor_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('floor')->unique();
            $table->unsignedInteger('target_ap_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        ApRecord::query()
            ->selectRaw('floor, COUNT(*) as record_count, MAX(ap_no) as max_ap_no')
            ->groupBy('floor')
            ->get()
            ->each(function ($row): void {
                FloorConfig::query()->create([
                    'floor' => $row->floor,
                    'target_ap_count' => max((int) $row->record_count, (int) $row->max_ap_no),
                    'sort_order' => FloorConfig::sortValue($row->floor),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('floor_configs');
    }
};
