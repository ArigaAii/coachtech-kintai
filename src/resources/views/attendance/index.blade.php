@extends('layouts.default')

@section('title', '勤怠登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('header-nav')
    @if ($status === '退勤済')
        <a href="{{ route('attendance.list') }}">今月の出勤一覧</a>
        <a href="{{ route('stamp_correction_request.index') }}">申請一覧</a>
    @else
        <a href="{{ route('attendance.index') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.index') }}">申請</a>
    @endif

    <form action="{{ route('logout') }}" method="post" class="header__logout-form">
        @csrf
        <button type="submit" class="header__logout-button">ログアウト</button>
    </form>
@endsection

@section('content')
    <div class="attendance-register">
        <p class="attendance-register__status">{{ $status }}</p>
        <p class="attendance-register__date">{{ $now->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}</p>
        <p class="attendance-register__time">{{ $now->format('H:i') }}</p>

        @if ($status === '勤務外')
            <form action="{{ route('attendance.clock_in') }}" method="post">
                @csrf
                <button type="submit" class="attendance-register__button">出勤</button>
            </form>
        @elseif ($status === '出勤中')
            <div class="attendance-register__actions">
                <form action="{{ route('attendance.clock_out') }}" method="POST">
                    @csrf
                    <button type="submit" class="attendance-register__button">退勤</button>
                </form>

                <form action="{{ route('attendance.break_start') }}" method="POST">
                    @csrf
                    <button type="submit" class="attendance-register__button attendance-register__button--sub">休憩入</button>
                </form>
            </div>

        @elseif ($status === '休憩中')
            <form action="{{ route('attendance.break_end') }}" method="POST">
                @csrf
                <button type="submit" class="attendance-register__button attendance-register__button--sub">休憩戻</button>
            </form>
        @elseif ($status === '退勤済')
            <p class="attendance-register__message">お疲れ様でした。</p>
        @endif
    </div>
@endsection