@php
    $submittedBy = $submission->user;
    $verificationsUrl = route('admin.verifications.index', ['status' => 'pending']);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Verification Submitted</title>
</head>
<body style="font-family: Arial, sans-serif; background: #0f172a; color: #e5e7eb; padding: 24px;">
    <div style="max-width: 640px; margin: 0 auto; background: #111827; border: 1px solid #374151; border-radius: 12px; padding: 20px;">
        <h1 style="font-size: 20px; margin: 0 0 12px; color: #f9fafb;">New Profile Verification Submitted</h1>

        <p style="font-size: 14px; line-height: 1.6; margin: 0 0 12px;">
            A user has submitted profile verification evidence and is waiting for admin review.
        </p>

        <div style="font-size: 14px; line-height: 1.7; background: #0f172a; border: 1px solid #374151; border-radius: 10px; padding: 12px;">
            <p style="margin: 0 0 6px;"><strong>User:</strong> {{ $submittedBy?->name }} ({{ $submittedBy?->email }})</p>
            <p style="margin: 0 0 6px;"><strong>Manager:</strong> {{ $submission->player_name }}</p>
            <p style="margin: 0 0 6px;"><strong>Team:</strong> {{ $submission->team_name }}</p>
            <p style="margin: 0 0 6px;"><strong>Entry ID:</strong> {{ $submission->entry_id }}</p>
            <p style="margin: 0 0 6px;"><strong>Submitted:</strong> {{ $submission->created_at?->format('M j, Y g:i A') }}</p>
            <p style="margin: 0;"><strong>Notes:</strong> {{ $submission->notes ?: 'None provided.' }}</p>
        </div>

        <div style="margin-top: 18px;">
            <a href="{{ $verificationsUrl }}" style="display: inline-block; background: #06b6d4; color: #0f172a; font-weight: 700; text-decoration: none; padding: 10px 14px; border-radius: 8px;">
                Review Verification
            </a>
        </div>
    </div>
</body>
</html>
