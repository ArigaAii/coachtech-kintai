@extends('layouts.default')

@section('title', '修正申請詳細')

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
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $request->attendance->user->name }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年n月j日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ \Carbon\Carbon::parse($request->requested_clock_in_at)->format('H:i') }}
                    〜
                    {{ \Carbon\Carbon::parse($request->requested_clock_out_at)->format('H:i') }}
                </td>
            </tr>

            <tr>
                <th>休憩</th>
                <td>
                    @if (isset($request->attendanceCorrectionRequestBreaks[0]))
                        {{ \Carbon\Carbon::parse($request->attendanceCorrectionRequestBreaks[0]->requested_break_start_at)->format('H:i') }}
                        〜
                        {{ \Carbon\Carbon::parse($request->attendanceCorrectionRequestBreaks[0]->requested_break_end_at)->format('H:i') }}
                    @endif
                </td>
            </tr>

            <tr>
                <th>休憩2</th>
                <td>
                    @if (isset($request->attendanceCorrectionRequestBreaks[1]))
                        {{ \Carbon\Carbon::parse($request->attendanceCorrectionRequestBreaks[1]->requested_break_start_at)->format('H:i') }}
                        〜
                        {{ \Carbon\Carbon::parse($request->attendanceCorrectionRequestBreaks[1]->requested_break_end_at)->format('H:i') }}
                    @endif
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>{{ $request->reason }}</td>
            </tr>

        </table>

        @if ($request->status === 'pending')
            <form action="{{ route('admin.stamp_correction_request.approve', $request->id) }}" method="POST" class="approve-form">
                @csrf
                <button type="submit" class="approve-button">承認</button>
            </form>
        @else
            <div class="approve-form">
                <button type="button" class="approve-button approve-button--disabled" disabled>承認済み</button>
            </div>
        @endif
    </div>
@endsection