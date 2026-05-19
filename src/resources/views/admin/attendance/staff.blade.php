@extends('layouts.default')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('header-nav')
    <a href="{{ route('admin.attendance.index') }}">勤怠一覧</a>
    <a href="{{ route('admin.staff.index') }}">スタッフ一覧</a>
    <a href="{{ route('stamp_correction_request.index') }}">申請一覧</a>

    <form action="{{ route('logout') }}" method="POST" class="header__logout-form">
        @csrf
        <button type="submit" class="header__logout-button">ログアウト</button>
    </form>
@endsection

@section('content')
    <h1 class="page-title">{{ $user->name }}さんの勤怠</h1>

    <div class="admin-page">
        <div class="date-nav">
            <a href="{{ route('admin.attendance.staff', ['user' => $user->id, 'month' => \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m')]) }}">
                ← 前月
            </a>

            <div class="date-nav__current">
                {{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}
            </div>

            <a href="{{ route('admin.attendance.staff', ['user' => $user->id, 'month' => \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m')]) }}">
                翌月 →
            </a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dates as $date)
                    @php
                        $dateKey = $date->format('Y-m-d');
                        $attendance = $attendances->get($dateKey);

                        $breakMinutes = 0;
                        $workMinutes = 0;

                        if ($attendance) {
                            foreach ($attendance->attendanceBreaks as $break) {
                                if ($break->break_start_at && $break->break_end_at) {
                                    $breakMinutes += \Carbon\Carbon::parse($break->break_start_at)
                                        ->diffInMinutes(\Carbon\Carbon::parse($break->break_end_at));
                                }
                            }

                            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                                $workMinutes =
                                    \Carbon\Carbon::parse($attendance->clock_in_at)
                                        ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out_at))
                                    - $breakMinutes;
                            }
                        }
                    @endphp

                    <tr>
                        <td>
                            {{ $date->format('m/d') }}
                            ({{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }})
                        </td>
                        <td>{{ $attendance && $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}</td>
                        <td>{{ $attendance && $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}</td>
                        <td>{{ $attendance ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                        <td>{{ $attendance ? floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                        <td>
                            @if ($attendance)
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a>
                            @else
                                <span class="admin-table__detail">詳細</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="csv-button-wrap">
            <a href="{{ route('admin.attendance.staff.csv', ['user' => $user->id, 'month' => $currentMonth]) }}" class="csv-button">
                CSV出力
            </a>
        </div>
    </div>
@endsection