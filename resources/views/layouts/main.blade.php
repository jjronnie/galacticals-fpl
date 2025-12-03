<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

</html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    {!! SEO::generate() !!}

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-36Z6T6DYQ4"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-36Z6T6DYQ4');
    </script>




    <title>
        {{ config('app.name') }}
    </title>
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">





    @vite('resources/css/app.css')


    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/logo-light.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo-light.webp') }}">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#001529">

    <meta name="mobile-web-app-capable" content="yes">


    <!--adsense script auto ads-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
        crossorigin="anonymous"></script>







</head>


<body class="font-sans  text-white bg-primary  m-0 p-0 flex flex-col min-h-screen ">

    <!-- Preloader-->
    @if (!request()->routeIs(['login', 'register', 'dashboard']))
    @include('layouts.preloader')
    @endif





    @yield('content')


    @include('components.alerts')


    @vite('resources/js/app.js')
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <x-consent-banner />

</body>

</html>