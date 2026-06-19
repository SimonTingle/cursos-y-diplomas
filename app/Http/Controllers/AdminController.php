<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function storeUser(Request $request)
    {
        if (!$request->user()->hasPermission('create_users')) {
            abort(403, __('Unauthorized to create users'));
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => ['required', 'in:admin,instructor,student'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'title'    => ['nullable', 'string', 'max:255'],
            'bio'      => ['nullable', 'string', 'max:1000'],
            'avatar'   => ['nullable', 'image', 'max:8192'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'phone'    => $data['phone'] ?? null,
            'title'    => $data['title'] ?? null,
            'bio'      => $data['bio'] ?? null,
            'avatar'   => $data['avatar'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('profile.edit')
            ->with('status', __('User created successfully.'));
    }

    public function destroyUser(Request $request, User $targetUser)
    {
        if (!$request->user()->hasPermission('delete_users')) {
            abort(403, __('Unauthorized to delete users'));
        }

        // Prevent admins from deleting themselves.
        if ($targetUser->is($request->user())) {
            return back()->with('status', __('You cannot delete your own account here.'));
        }

        // Delete avatar if exists
        if ($targetUser->avatar && Storage::disk('public')->exists($targetUser->avatar)) {
            Storage::disk('public')->delete($targetUser->avatar);
        }

        $targetUser->delete();

        return redirect()->route('profile.edit')
            ->with('status', __('User deleted.'));
    }
}
