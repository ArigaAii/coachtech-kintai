@extends('layouts.default')

@section('title', '会員登録')

@section('body-class', 'auth-page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
    <div class="auth">
        <h1 class="auth__title">会員登録</h1>

        @if ($errors->any())
            <div class="auth__errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" class="auth__form">
            @csrf

            <div class="auth__group">
                <label class="auth__label">名前</label>
                <input type="text" name="name" class="auth__input" value="{{ old('name') }}">
            </div>

            <div class="auth__group">
                <label class="auth__label">メールアドレス</label>
                <input type="text" name="email" class="auth__input" value="{{ old('email') }}">
            </div>

            <div class="auth__group">
                <label class="auth__label">パスワード</label>
                <input type="password" name="password" class="auth__input">
            </div>

            <div class="auth__group">
                <label class="auth__label">パスワード確認</label>
                <input type="password" name="password_confirmation" class="auth__input">
            </div>

            <button type="submit" class="auth__button">登録する</button>
        </form>

        <div class="auth__link">
            <a href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </div>
@endsection