<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage; 

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // 1) Données validées 
        $validated = $request->validated();

        if(array_key_exists('avatar', $validated)) unset($validated['avatar']);

        $user->fill($validated);

        // Si email a changé => on annule la vérification
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // 4) Si l'utilisateur a envoyé une image
        if ($request->hasFile('avatar')) {

            // supprimer l'ancienne image
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // upload dans storage/app/public/avatars
            $path = $request->file('avatar')->store('avatars', 'public');

            // on stocke le chemin dans la DB
            $user->avatar_path = $path;
        }
       
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
