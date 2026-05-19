@extends('layouts.default')

@section('title', '申請一覧（管理者）')

@section('content')
    <h1>申請一覧（管理者）</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>名前</th>
            <th>日付</th>
            <th>理由</th>
            <th>状態</th>
            <th>詳細</th>
        </tr>

        @foreach ($requests as $request)
            <tr>
                <td>{{ $request->id }}</td>
                <td>{{ $request->attendance->user->name }}</td>
                <td>{{ $request->attendance->work_date }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ $request->status }}</td>
                <td>
                    <a href="{{ route('admin.stamp_correction_request.show', $request->id) }}">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
@endsection