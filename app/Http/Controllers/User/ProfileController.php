<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('User/Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'department' => $user->department,
                'role' => $user->role,
                'profile_photo' => $user->profile_photo ? Storage::url($user->profile_photo) : null,
                'is_active' => (bool) $user->is_active,
                'notify_on_verification' => (bool) $user->notify_on_verification,
                'notify_on_new_entry' => (bool) $user->notify_on_new_entry,
                'notify_on_rejection' => (bool) $user->notify_on_rejection,
                'two_factor_enabled' => (bool) $user->two_factor_enabled,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'notify_on_verification' => 'boolean',
            'notify_on_new_entry' => 'boolean',
            'notify_on_rejection' => 'boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->password);
        $user->password_changed_at = now();
        $user->save();

        return back()->with('success', 'Password updated successfully');
    }

    public function updateProfilePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_photo' => 'required|image|max:2048',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Store new profile photo
            $path = $request->file('profile_photo')->store('profile-photos', 'public');

            // Update user
            $user->profile_photo = $path;
            $user->save();
        }

        return back()->with('success', 'Profile photo updated successfully');
    }

    public function updateTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'two_factor_enabled' => 'required|boolean',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->two_factor_enabled = $request->two_factor_enabled;
        $user->save();

        return back()->with('success', 'Two-factor authentication settings updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect']);
        }

        // Delete profile photo if exists
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Delete related records - check if relationships exist
        if (method_exists($user, 'assets')) {
            $user->assets()->delete();
        }
        if (method_exists($user, 'watchlist')) {
            $user->watchlist()->delete();
        }
        if (method_exists($user, 'alerts')) {
            $user->alerts()->delete();
        }
        if (method_exists($user, 'notifications')) {
            $user->notifications()->delete();
        }

        // Logout before deleting
        Auth::logout();

        // Delete the user (soft delete since you have deleted_at column)
        $user->delete();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Account deleted successfully');
    }
}
