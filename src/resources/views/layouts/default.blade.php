<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ asset('/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/common.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @yield('css')
</head>

<body class="@yield('body-class')">
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <a href="/">
                    <img src="{{ asset('img/logo.png') }}" alt="COACHTECHロゴ">
                </a>
            </div>

            @hasSection('header-nav')
                <nav class="header__nav">
                    @yield('header-nav')
                </nav>
            @endif
        </div>
    </header>

    @if (session('message'))
        <div class="flash-message">
            {{ session('message') }}
        </div>
    @endif

    @if (session('flashSuccess'))
        <div class="flash-message flash-message--success">
            {{ session('flashSuccess') }}
        </div>
    @endif

    @if (session('flashError'))
        <div class="flash-message flash-message--error">
            {{ session('flashError') }}
        </div>
    @endif

    <main class="main">
        <div class="content">
            @yield('content')
        </div>
    </main>
</body>

</html>