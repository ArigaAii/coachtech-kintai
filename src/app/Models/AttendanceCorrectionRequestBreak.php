<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrectionRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_request_id',
        'requested_break_start_at',
        'requested_break_end_at',
    ];

    public function attendanceCorrectionRequest(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class);
    }
}
