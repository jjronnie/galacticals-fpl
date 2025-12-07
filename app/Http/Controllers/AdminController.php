<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\League;


use Illuminate\Validation\Rule;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $users = User::with('league')
        ->orderBy('id', 'desc')
        ->paginate(50);

    return view('admin.users.index', compact('users'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {

   $user = User::findOrFail($id);
        $league = League::where('user_id', $user->id)->first();


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'status' => 'required|string|in:active,suspended',
            'role' => 'required|string|in:user,admin',
            'profile_photo_path' => 'nullable|image|max:2048',
            'sync_status' => 'nullable|string|max:255',


        ]);

             $league->update([
                'sync_status' => $validated['sync_status'],
             
            ]);

        // Handle avatar upload
        if ($request->hasFile('profile_photo_path')) {
            // Delete old avatar if exists
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new avatar
            $validated['profile_photo_path'] = $request->file('profile_photo_path')->store('avatars/users', 'public');
        }



        $user->update($validated);

        // Handle email verification switch
        if ($request->has('email_verified')) {
            $user->email_verified_at = now();
        } else {
            $user->email_verified_at = null;
        }

        $user->save();

        



        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
public function destroy( $id)
{

    $user = User::findOrFail($id);
    // Prevent deleting admin users
    if ($user->role === 'admin') {
        return back()->with('error', 'Admin users cannot be deleted.');
    }

    $user->delete();

    return back()->with('status', 'User deleted successfully.');
}

}
