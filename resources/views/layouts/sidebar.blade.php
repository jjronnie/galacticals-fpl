@auth

@if(auth()->user()->isAdmin())
@include('layouts.partials.sidebar.admin')
@endauth

@else
@include('layouts.partials.sidebar.users')
@endif