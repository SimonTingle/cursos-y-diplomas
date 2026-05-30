<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => ['required', 'in:admin,instructor'],
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        return redirect()->route('profile.edit')
            ->with('status', __('User created successfully.'));
    }

    public function destroyUser(Request $request, User $targetUser)
    {
        // Prevent admins from deleting themselves.
        if ($targetUser->is($request->user())) {
            return back()->with('status', __('You cannot delete your own account here.'));
        }

        $targetUser->delete();

        return redirect()->route('profile.edit')
            ->with('status', __('User deleted.'));
    }
}
