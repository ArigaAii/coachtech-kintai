<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'requested_clock_in_at' => ['required'],
            'requested_clock_out_at' => ['required', 'after:requested_clock_in_at'],
            'breaks.*.break_start_at' => ['nullable'],
            'breaks.*.break_end_at' => ['nullable'],
            'reason' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'requested_clock_in_at.required' => '出勤時間を入力してください',
            'requested_clock_out_at.required' => '退勤時間を入力してください',
            'requested_clock_out_at.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('requested_clock_in_at');
            $clockOut = $this->input('requested_clock_out_at');
            $breaks = $this->input('breaks', []);

            foreach ($breaks as $break) {
                $breakStart = $break['break_start_at'] ?? null;
                $breakEnd = $break['break_end_at'] ?? null;

                if ($breakStart && $breakEnd && $breakStart > $breakEnd) {
                    $validator->errors()->add(
                        'break_time',
                        '休憩時間が不適切な値です'
                    );
                }

                if ($breakStart && $clockIn && $breakStart < $clockIn) {
                    $validator->errors()->add(
                        'break_time',
                        '休憩時間が不適切な値です'
                    );
                }

                if ($breakEnd && $clockOut && $breakEnd > $clockOut) {
                    $validator->errors()->add(
                        'break_time',
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }
}
