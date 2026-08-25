<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.account', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate(['username' => ['required', 'email', 'max:255', 'unique:users,username,'.$request->user()->id]]);
        $request->user()->update($validated);
        return back()->with('profile_status', 'Datos de acceso actualizados.');
    }

    public function password(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        $request->user()->update(['password' => Hash::make($validated['password'])]);
        return back()->with('password_status', 'Contraseña actualizada correctamente.');
    }

    public function photo(Request $request)
    {
        $validated = $request->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);
        $user = $request->user();
        $path = $validated['photo']->store('profile-photos', 'public');
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }
        $user->update(['profile_photo_path' => $path]);
        return back()->with('photo_status', 'Foto de perfil actualizada.');
    }

    public function photoContent(Request $request)
    {
        $path = $request->user()->profile_photo_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
