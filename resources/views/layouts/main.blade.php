<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <link rel="icon" href="{{ asset('assets/img/logo-light.webp?v=2') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo-light.webp?v=2') }}">



    <link rel="manifest" href="/manifest.json?v=2">

    <meta name="mobile-web-app-capable" content="yes">


    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-36Z6T6DYQ4"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-36Z6T6DYQ4');
    </script>

    <!-- CLARITY -->
    <script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "uip1r477ae");
</script>




    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">





    @vite('resources/css/app.css')







    {{-- @if(auth()->guest() || (auth()->check() && !auth()->user()->isAdmin()))
    <!--adsense script auto ads-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
        crossorigin="anonymous"></script>
    @endif --}}




    {!! SEO::generate() !!}



</head>


<body class="font-sans  text-white bg-primary  m-0 p-0 flex flex-col min-h-screen ">

    <!-- Preloader-->
    @if (!request()->routeIs(['login', 'register', 'dashboard']))
    @include('layouts.preloader')

    @guest
    <x-cta-reg />
    @endguest


    @endif





    @yield('content')


    @include('components.alerts')


    @vite('resources/js/app.js')
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <x-consent-banner />

</body>

</html>