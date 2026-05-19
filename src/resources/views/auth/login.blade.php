@extends('layouts.default')

@section('title', 'ログイン')

@section('body-class', 'auth-page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
    <div class="auth">
        <h1 class="auth__title">ログイン</h1>

        @if ($errors->any())
            <div class="auth__errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST" class="auth__form">
            @csrf

            <div class="auth__group">
                <label class="auth__label">メールアドレス</label>
                <input type="text" name="email" class="auth__input" value="{{ old('email') }}">
            </div>

            <div class="auth__group">
                <label class="auth__label">パスワード</label>
                <input type="password" name="password" class="auth__input">
            </div>

            <button type="submit" class="auth__button">ログインする</button>
        </form>

        <div class="auth__link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </div>
@endsection