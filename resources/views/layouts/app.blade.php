@extends('layouts.main')
@section('content')
    <div class="flex min-h-screen">
        @include('layouts.sidebar')
        <div class="flex min-w-0 flex-1 flex-col">
            @include('layouts.nav')
          
            <main class="flex-1 p-4 pb-24 sm:p-6 sm:pb-24 lg:p-8 lg:pb-24">
                @include('layouts.profile-intro-banner')
                {{ $slot }}
            </main>
           
            @include('layouts.footer')
        </div>
    </div>
@endsection
