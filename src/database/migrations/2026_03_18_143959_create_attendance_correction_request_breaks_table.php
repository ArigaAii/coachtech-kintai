<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('attendance_correction_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_correction_request_id');
            $table->foreign('attendance_correction_request_id', 'acrb_request_id_fk')
                ->references('id')
                ->on('attendance_correction_requests')
                ->cascadeOnDelete();
            $table->dateTime('requested_break_start_at');
            $table->dateTime('requested_break_end_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_correction_request_breaks');
    }
};
