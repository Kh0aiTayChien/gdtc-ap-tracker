<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ap_records', function (Blueprint $table): void {
            $table->date('work_date')->nullable()->after('status')->index();
        });

        DB::table('ap_records')
            ->whereNull('work_date')
            ->update(['work_date' => DB::raw('DATE(created_at)')]);
    }

    public function down(): void
    {
        Schema::table('ap_records', function (Blueprint $table): void {
            $table->dropIndex(['work_date']);
            $table->dropColumn('work_date');
        });
    }
};
