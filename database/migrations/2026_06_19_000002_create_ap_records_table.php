<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ap_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete()->index();
            $table->string('floor')->index();
            $table->unsignedInteger('ap_no');
            $table->string('ap_name');
            $table->string('status')->index();
            $table->string('location_photo')->nullable();
            $table->string('mac_photo')->nullable();
            $table->string('cable_photo')->nullable();
            $table->string('issue_reason')->nullable();
            $table->text('issue_note')->nullable();
            $table->string('issue_photo')->nullable();
            $table->timestamps();
            $table->unique(['floor', 'ap_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_records');
    }
};
