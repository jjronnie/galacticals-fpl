@php
    // Get the authenticated user's business instance
    // This assumes the 'business' relationship is set up on the User model
    $business = auth()->user()->business;
@endphp

@if ($business)
    {{-- Call the component and pass the required 'business' prop --}}
    <x-subscription-status-banner :business="$business" />
@endif