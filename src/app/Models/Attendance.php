<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'status',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceBreaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function attendanceCorrectionRequests(): HasMany
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    public function getTotalBreaksMinutesAttribute()
    {
        $breakMinutes = 0;

        foreach ($this->attendanceBreaks as $break) {

            if ($break->break_start_at && $break->break_end_at) {

                $breakMinutes += \Carbon\Carbon::parse($break->break_start_at)
                    ->diffInMinutes(
                        \Carbon\Carbon::parse($break->break_end_at)
                    );
            }
        }

        return $breakMinutes;
    }

    public function getWorkingMinutesAttribute()
    {
        if (!$this->clock_in_at || !$this->clock_out_at) {
            return 0;
        }

        $workMinutes = \Carbon\Carbon::parse($this->clock_in_at)
            ->diffInMinutes(
                \Carbon\Carbon::parse($this->clock_out_at)
            );

        return $workMinutes - $this->total_break_minutes;
    }

    public function formatMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
