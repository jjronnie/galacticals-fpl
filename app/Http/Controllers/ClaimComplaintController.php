<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResolveClaimComplaintRequest;
use App\Http\Requests\StoreClaimComplaintRequest;
use App\Mail\ClaimComplaintSubmittedMail;
use App\Models\ClaimsComplaint;
use App\Models\Manager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ClaimComplaintController extends Controller
{
    public function store(StoreClaimComplaintRequest $request, Manager $manager): RedirectResponse
    {
        $user = $request->user();

        $dailyComplaints = ClaimsComplaint::query()
            ->where('reporter_user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        if ($dailyComplaints >= 3) {
            return back()->withErrors([
                'complaint' => 'Daily complaint limit reached. Try again tomorrow.',
            ]);
        }

        $subject = trim($request->validated('subject'));
        $message = trim($request->validated('message'));

        $duplicateComplaint = ClaimsComplaint::query()
            ->where('manager_id', $manager->id)
            ->where('reporter_user_id', $user->id)
            ->where('subject', $subject)
            ->where('message', $message)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($duplicateComplaint) {
            return back()->withErrors([
                'complaint' => 'You already submitted this complaint recently.',
            ]);
        }

        $complaint = DB::transaction(function () use ($manager, $user, $subject, $message): ClaimsComplaint {
            return ClaimsComplaint::create([
                'manager_id' => $manager->id,
                'reporter_user_id' => $user->id,
                'subject' => $subject,
                'message' => $message,
                'status' => 'open',
            ]);
        });

        $complaint->load(['reporter', 'manager']);

        Mail::to('ronaldjjuuko7@gmail.com')->queue(new ClaimComplaintSubmittedMail($complaint));

        return back()->with('status', 'Complaint submitted. The admin team will review it.');
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $complaints = ClaimsComplaint::query()
            ->with(['reporter:id,name,email', 'manager:id,player_name,team_name,entry_id', 'resolvedBy:id,name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 WHEN status = 'in_progress' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.complaints.index', [
            'complaints' => $complaints,
            'status' => $status,
        ]);
    }

    public function resolve(ResolveClaimComplaintRequest $request, ClaimsComplaint $complaint): RedirectResponse
    {
        $status = $request->validated('status');

        $complaint->update([
            'status' => $status,
            'resolved_by' => $request->user()->id,
            'resolved_at' => $status === 'resolved' ? now() : null,
        ]);

        return back()->with('status', 'Complaint updated successfully.');
    }

    public function destroy(ClaimsComplaint $complaint): RedirectResponse
    {
        $complaint->delete();

        return back()->with('status', 'Complaint deleted successfully.');
    }
}
