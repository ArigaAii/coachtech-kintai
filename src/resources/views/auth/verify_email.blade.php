@extends('layouts.default')

@section('title', 'メール認証')

@section('body-class', 'auth-page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth">

    <div class="verify-email">

        <p class="verify-email__message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="http://localhost:8025" class="verify-email__button" target="_blank">
            認証はこちらから
        </a>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify-email__resend">
                認証メールを再送する
            </button>
        </form>

    </div>
</div>
@endsection