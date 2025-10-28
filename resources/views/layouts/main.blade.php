<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    


    <title>
        {{ ucfirst(Str::before(Route::currentRouteName() ?? 'Admin', '.')) }} | FPL
    </title>
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">





    @vite('resources/css/app.css')


    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/logo.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo.webp') }}">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#001529">

    <meta name="mobile-web-app-capable" content="yes">

    
    <!--adsense script auto ads-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
        crossorigin="anonymous"></script>

        





</head>


<body class="font-sans  bg-[#F2F3F6]  m-0 p-0 flex flex-col min-h-screen ">

    <!-- Preloader-->
    @if (!request()->routeIs(['login', 'register']))
    @include('layouts.preloader')
    @endif





    @yield('content')


    @include('components.alerts')


    @vite('resources/js/app.js')
    <script src="{{ asset('assets/js/main.js') }}"></script>

<x-consent-banner/>

</body>

</html>