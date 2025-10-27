@extends('layouts.main')
@section('content')
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        @include('layouts.sidebar')
        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
            <!-- Header -->
            @include('layouts.nav')
            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
              

                {{ $slot }}
            </main>
            <!-- Footer -->
            @include('layouts.footer')
        </div>
    </div>
@endsection
