@extends('layouts.default')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('header-nav')
    <a href="{{ route('attendance.index') }}">勤怠</a>
    <a href="{{ route('attendance.list') }}">勤怠一覧</a>
    <a href="{{ route('stamp_correction_request.index') }}">申請</a>

    <form action="{{ route('logout') }}" method="POST" class="header__logout-form">
        @csrf
        <button type="submit" class="header__logout-button">ログアウト</button>
    </form>
@endsection

@section('content')
    <h1 class="page-title">勤怠一覧</h1>

    <div class="admin-page">
        <div class="date-nav">
            <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}">← 前月</a>

            <div class="date-nav__current">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('Y/m') }}
            </div>

            <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月 →</a>
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
                        $attendance = $attendances->get($date->format('Y-m-d'));
                    @endphp

                    <tr>
                        <td>
                            {{ $date->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }})
                        </td>

                        <td>{{ $attendance && $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}</td>

                        <td>{{ $attendance && $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}</td>

                        <td>
                            @if ($attendance)
                                {{ sprintf('%d:%02d', floor($attendance->total_break_minutes / 60), $attendance->total_break_minutes % 60) }}
                            @endif
                        </td>

                        <td>
                            @if ($attendance)
                                {{ sprintf('%d:%02d', floor($attendance->working_minutes / 60), $attendance->working_minutes % 60) }}
                            @endif
                        </td>

                        <td>
                            @if ($attendance)
                                <a href="{{ route('attendance.show', $attendance->id) }}">詳細</a>
                            @else
                                <span class="admin-table__detail">詳細</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection