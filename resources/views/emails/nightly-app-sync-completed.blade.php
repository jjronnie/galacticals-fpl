@php
    $hasFailures = ($summary['errors'] ?? []) !== [] || ($summary['league_failures'] ?? []) !== [];
@endphp

<x-mail::message>
# Nightly App Sync Report

Status: **{{ $hasFailures ? 'Completed with issues' : 'Completed successfully' }}**

<x-mail::panel>
Started: {{ $summary['started_at'] ?? '-' }} ({{ $summary['timezone'] ?? 'UTC' }})
Completed: {{ $summary['completed_at'] ?? '-' }} ({{ $summary['timezone'] ?? 'UTC' }})
Duration: {{ $summary['duration_seconds'] ?? 0 }} seconds
</x-mail::panel>

## Summary

- FPL teams/players synced: {{ ($summary['fpl_synced'] ?? false) ? 'Yes' : 'No' }}
- Manager profile entries: {{ $summary['profile_entries_total'] ?? 0 }}
- Manager profiles synced: {{ ($summary['profile_synced'] ?? false) ? 'Yes' : 'No' }}
- Leagues synced: {{ $summary['leagues_synced'] ?? 0 }} / {{ $summary['leagues_total'] ?? 0 }}

@if (! empty($summary['errors']))
## Global Errors

@foreach ($summary['errors'] as $error)
- {{ $error }}
@endforeach
@endif

@if (! empty($summary['league_failures']))
## League Failures

@foreach ($summary['league_failures'] as $failure)
- {{ $failure['name'] ?? 'Unknown league' }}: {{ $failure['error'] ?? 'Unknown error' }}
@endforeach
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
