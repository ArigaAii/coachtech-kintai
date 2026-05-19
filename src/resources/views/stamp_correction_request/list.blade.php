@extends('layouts.default')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('header-nav')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('admin.attendance.index') }}">勤怠一覧</a>
        <a href="{{ route('admin.staff.index') }}">スタッフ一覧</a>
        <a href="{{ route('stamp_correction_request.index') }}">申請一覧</a>
    @else
        <a href="{{ route('attendance.index') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.index') }}">申請</a>
    @endif

    <form action="{{ route('logout') }}" method="POST" class="header__logout-form">
        @csrf
        <button type="submit" class="header__logout-button">ログアウト</button>
    </form>
@endsection

@section('content')
    <h1 class="page-title">申請一覧</h1>

    <div class="admin-page">
        <div class="request-tabs">
            <a href="{{ route('stamp_correction_request.index', ['status' => 'pending']) }}"
                class="request-tabs__item {{ $status === 'pending' ? 'request-tabs__item--active' : '' }}">承認待ち
            </a>

            <a href="{{ route('stamp_correction_request.index', ['status' => 'approved']) }}"
                class="request-tabs__item {{ $status === 'approved' ? 'request-tabs__item--active' : '' }}">承認済み
            </a>
        </div>

        <div class="request-list">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($correctionRequests as $correctionRequest)
                        <tr>
                            <td>
                                @if ($correctionRequest->status === 'pending')
                                    承認待ち
                                @elseif ($correctionRequest->status === 'approved')
                                    承認済み
                                @else
                                    却下
                                @endif
                            </td>
                            <td>{{ $correctionRequest->attendance->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($correctionRequest->attendance->work_date)->format('Y/m/d') }}</td>
                            <td>{{ $correctionRequest->reason }}</td>
                            <td>{{ \Carbon\Carbon::parse($correctionRequest->created_at)->format('Y/m/d') }}</td>
                            <td>
                                @if (auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.stamp_correction_request.show', $correctionRequest->id) }}">詳細</a>
                                @else
                                    <a href="{{ route('attendance.show', $correctionRequest->attendance_id) }}">詳細</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">申請データがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection