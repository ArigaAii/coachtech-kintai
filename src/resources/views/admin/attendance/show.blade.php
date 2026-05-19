@extends('layouts.default')

@section('title', '勤怠詳細')

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
    <h1 class="page-title">勤怠詳細</h1>

    <div class="attendance-detail">
        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
            @csrf
            <table class="attendance-detail__table">
                <tr>
                    <th>名前</th>
                    <td>{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                        {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="time"
                            name="clock_in_at"
                            value="{{ $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}">

                        〜

                        <input type="time"
                            name="clock_out_at"
                            value="{{ $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}">
                    </td>
                </tr>

                @forelse ($attendance->attendanceBreaks as $index => $break)
                    <tr>
                        <th>休憩</th>
                        <td>
                            <input
                                type="time"
                                name="break_start_at[]"
                                value="{{ isset($attendance->attendanceBreaks[0]) && $attendance->attendanceBreaks[0]->break_start_at ? \Carbon\Carbon::parse($attendance->attendanceBreaks[0]->break_start_at)->format('H:i') : '' }}"
                            >

                            〜

                            <input
                                type="time"
                                name="break_end_at[]"
                                value="{{ isset($attendance->attendanceBreaks[0]) && $attendance->attendanceBreaks[0]->break_end_at ? \Carbon\Carbon::parse($attendance->attendanceBreaks[0]->break_end_at)->format('H:i') : '' }}"
                            >
                        </td>
                    </tr>

                    <tr>
                        <th>休憩2</th>
                        <td>
                            <input
                                type="time"
                                name="break_start_at[]"
                                value="{{ isset($attendance->attendanceBreaks[1]) && $attendance->attendanceBreaks[1]->break_start_at ? \Carbon\Carbon::parse($attendance->attendanceBreaks[1]->break_start_at)->format('H:i') : '' }}"
                            >

                            〜

                            <input
                                type="time"
                                name="break_end_at[]"
                                value="{{ isset($attendance->attendanceBreaks[1]) && $attendance->attendanceBreaks[1]->break_end_at ? \Carbon\Carbon::parse($attendance->attendanceBreaks[1]->break_end_at)->format('H:i') : '' }}"
                            >
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <th>備考</th>

                    <td>
                        <textarea name="note">{{ $attendance->note }}</textarea>
                    </td>
                </tr>
            </table>

            @if ($request->status === 'pending')
                <p class="correction-message">
                    承認待ちのため修正はできません。
                </p>

                <div class="detail-button">
                    <button type="button" class="detail-button__submit" disabled>
                        修正
                    </button>
                </div>
            @else
                <div class="detail-button">
                    <button type="submit" class="detail-button__submit">
                        修正
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection