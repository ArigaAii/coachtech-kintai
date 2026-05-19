@extends('layouts.default')

@section('title', '管理者 勤怠一覧')

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
    <h1 class="page-title">{{ \Carbon\Carbon::parse($currentDate)->format('Y年n月j日') }}の勤怠</h1>

    <div class="admin-page">

        <div class="date-nav">
            <a href="{{ route('admin.attendance.index', ['date' => \Carbon\Carbon::parse($currentDate)->subDay()->format('Y-m-d')]) }}">
                ← 前日
            </a>

            <div class="date-nav__current">
                <i class="fa-regular fa-calendar-days"></i>
                {{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}
            </div>

            <a href="{{ route('admin.attendance.index', ['date' => \Carbon\Carbon::parse($currentDate)->addDay()->format('Y-m-d')]) }}">
                翌日 →
            </a>
        </div>

        <div class="request-list">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        @php
                            $breakMinutes = 0;

                            foreach ($attendance->attendanceBreaks as $break) {
                                if ($break->break_start_at && $break->break_end_at) {
                                    $breakMinutes += \Carbon\Carbon::parse($break->break_start_at)
                                        ->diffInMinutes(\Carbon\Carbon::parse($break->break_end_at));
                                }
                            }

                            $workMinutes = 0;

                            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                                $workMinutes =
                                    \Carbon\Carbon::parse($attendance->clock_in_at)
                                        ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out_at))
                                    - $breakMinutes;
                            }
                        @endphp

                        <tr>
                            <td>{{ $attendance->user->name }}</td>

                            <td>
                                {{ $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}
                            </td>

                            <td>
                                {{ $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}
                            </td>

                            <td>
                                {{ floor($breakMinutes / 60) }}:{{ str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <td>
                                {{ floor($workMinutes / 60) }}:{{ str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <td>
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection