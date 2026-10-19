<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel `attendances`: rekam absensi (waktu server & koordinat) (PRD §5.4).
     * clock_in_time / clock_out_time SELALU diisi dari timestamp server (now()).
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->dateTime('clock_in_time')->nullable();
            $table->dateTime('clock_out_time')->nullable();
            $table->string('lat_in', 32)->nullable();
            $table->string('lng_in', 32)->nullable();
            $table->string('lat_out', 32)->nullable();
            $table->string('lng_out', 32)->nullable();
            $table->enum('status', ['hadir', 'telat', 'alpha'])->default('alpha');
            $table->timestamps();

            $table->index(['user_id', 'clock_in_time']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
