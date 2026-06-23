@extends('layouts.default')

@section('title', '勤怠詳細')

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
    <h1 class="page-title">勤怠詳細</h1>

    <div class="attendance-detail">
        @if ($errors->any())
            <div class="attendance-detail__errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stamp_correction_request.store', $attendance->id) }}" method="POST">
            @csrf

            <table class="attendance-detail__table">
                <tr>
                    <th>名前</th>
                    <td>
                        @php
                            $nameParts = preg_split('/[.  ]+/u', trim($attendance->user->name));
                        @endphp

                        <div class="detail-name">
                            <span>{{ $nameParts[0] ?? '' }}</span>
                            <span>{{ $nameParts[1] ?? '' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <div class="detail-date">
                            <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                            <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="detail-time">
                            <input
                                type="text"
                                name="requested_clock_in_at"
                                value="{{ old('requested_clock_in_at', $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '') }}"
                                @if ($pendingRequestExists) disabled @endif
                            >

                            <span>〜</span>

                            <input
                                type="text"
                                name="requested_clock_out_at"
                                value="{{ old('requested_clock_out_at', $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '') }}"
                                @if ($pendingRequestExists) disabled @endif
                            >
                        </div>
                    </td>
                </tr>

                @php
                    $breaks = $attendance->attendanceBreaks;
                @endphp

                @forelse ($breaks as $index => $break)
                    <tr>
                        <th>休憩{{ $index == 0 ? '' : $index + 1 }}</th>
                        <td>
                            <div class="detail-time">
                                <input
                                    type="text"
                                    name="breaks[{{ $index }}][break_start_at]"
                                    value="{{ old("breaks.$index.break_start_at", $break->break_start_at ? \Carbon\Carbon::parse($break->break_start_at)->format('H:i') : '') }}"
                                    @if ($pendingRequestExists) disabled @endif
                                >
                        
                                <span>〜</span>

                                <input
                                    type="text"
                                    name="breaks[{{ $index }}][break_end_at]"
                                    value="{{ old("breaks.$index.break_end_at", $break->break_end_at ? \Carbon\Carbon::parse($break->break_end_at)->format('H:i') : '') }}"
                                    @if ($pendingRequestExists) disabled @endif
                                >
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <th>休憩</th>
                        <td>
                            <div class="detail-time">
                                <input
                                    type="text"
                                    name="breaks[0][break_start_at]"
                                    value="{{ old('breaks.0.break_start_at') }}"
                                    @if ($pendingRequestExists) disabled @endif
                                >
                            
                                <span>〜</span>

                                <input
                                    type="text"
                                    name="breaks[0][break_end_at]"
                                    value="{{ old('breaks.0.break_end_at') }}"
                                    @if ($pendingRequestExists) disabled @endif
                                >
                            </div>
                        </td>
                    </tr>
                @endforelse

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="reason" @if ($pendingRequestExists) disabled @endif>{{old('reason', $attendance->note) }}</textarea>
                    </td>
                </tr>
            </table>

            @if ($pendingRequestExists)
                <p class="correction-message">※承認待ちのため修正できません。</p>
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