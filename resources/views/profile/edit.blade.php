@extends('layouts.portal')

@section('title', __('Profile'))

@section('content')

    {{-- Profile information --}}
    <section class="glass p-6 sm:p-8">
        <h2 class="mb-1 text-lg font-semibold text-white">{{ __('Profile Information') }}</h2>
        <p class="mb-5 text-sm text-slate-400">{{ __("Update your account's profile information and email address.") }}</p>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('patch')
            <div>
                <label class="glass-label">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" class="glass-input" value="{{ old('name', $user->name) }}" required autofocus>
                <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('name')" />
            </div>
            <div>
                <label class="glass-label">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" class="glass-input" value="{{ old('email', $user->email) }}" required>
                <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('email')" />
            </div>
            <div class="flex items-center gap-4 pt-1">
                <button type="submit" class="btn-primary">{{ __('Save') }}</button>
                @if (session('status') === 'profile-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                       class="text-sm text-emerald-300">{{ __('Saved.') }}</p>
                @endif
            </div>
        </form>
    </section>

    {{-- Change password --}}
    <section class="glass p-6 sm:p-8">
        <h2 class="mb-1 text-lg font-semibold text-white">{{ __('Update Password') }}</h2>
        <p class="mb-5 text-sm text-slate-400">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>

        <form method="post" action="{{ route('password.update') }}" class="space-y-4">
            @csrf @method('put')
            <div>
                <label class="glass-label">{{ __('Current Password') }}</label>
                <input id="current_password" name="current_password" type="password" class="glass-input" autocomplete="current-password">
                <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->updatePassword->get('current_password')" />
            </div>
            <div>
                <label class="glass-label">{{ __('New Password') }}</label>
                <input id="password" name="password" type="password" class="glass-input" autocomplete="new-password">
                <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->updatePassword->get('password')" />
            </div>
            <div>
                <label class="glass-label">{{ __('Confirm Password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="glass-input" autocomplete="new-password">
                <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->updatePassword->get('password_confirmation')" />
            </div>
            <div class="flex items-center gap-4 pt-1">
                <button type="submit" class="btn-primary">{{ __('Save') }}</button>
                @if (session('status') === 'password-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                       class="text-sm text-emerald-300">{{ __('Saved.') }}</p>
                @endif
            </div>
        </form>
    </section>

    @if ($isAdmin)
        {{-- ── Admin: Create user ──────────────────────────────────── --}}
        <section class="glass p-6 sm:p-8">
            <div class="mb-5 flex items-center gap-3">
                <span class="rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300">Admin</span>
                <h2 class="text-lg font-semibold text-white">{{ __('Create user') }}</h2>
            </div>

            <form method="post" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="glass-label">{{ __('Name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="glass-input" required>
                    <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('name')" />
                </div>
                <div>
                    <label class="glass-label">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="glass-input" required>
                    <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('email')" />
                </div>
                <div>
                    <label class="glass-label">{{ __('Password') }}</label>
                    <input type="password" name="password" class="glass-input" required autocomplete="new-password">
                    <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('password')" />
                </div>
                <div>
                    <label class="glass-label">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" class="glass-input" required autocomplete="new-password">
                </div>
                <div>
                    <label class="glass-label">{{ __('Role') }}</label>
                    <select name="role" class="glass-input" required>
                        <option value="instructor" @selected(old('role') === 'instructor')>{{ __('Instructor') }}</option>
                        <option value="admin" @selected(old('role') === 'admin')>{{ __('Admin') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full">{{ __('Create user') }}</button>
                </div>
            </form>
        </section>

        {{-- ── Admin: User list ────────────────────────────────────── --}}
        <section class="glass p-6 sm:p-8">
            <h2 class="mb-5 text-lg font-semibold text-white">{{ __('All users') }}</h2>
            @forelse ($users as $u)
                <div class="flex items-center justify-between gap-4 border-t border-white/5 py-3 first:border-t-0">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-white">{{ $u->name }}</p>
                        <p class="text-xs text-slate-400">{{ $u->email }}</p>
                    </div>
                    <div class="flex flex-none items-center gap-3">
                        <span class="rounded-full border px-2.5 py-0.5 text-[11px] font-semibold
                            {{ $u->isAdmin() ? 'border-cyan-400/30 text-cyan-300' : 'border-white/10 text-slate-400' }}">
                            {{ $u->isAdmin() ? __('Admin') : __('Instructor') }}
                        </span>
                        @unless ($u->is(auth()->user()))
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                  onsubmit="return confirm('{{ __('Delete this user?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="rounded-lg px-2.5 py-1 text-xs font-medium text-rose-300 transition hover:bg-rose-500/10">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <p class="py-4 text-center text-sm text-slate-400">{{ __('No users yet.') }}</p>
            @endforelse
        </section>
    @endif

    {{-- Delete own account --}}
    <section class="glass p-6 sm:p-8">
        <h2 class="mb-1 text-lg font-semibold text-white">{{ __('Delete Account') }}</h2>
        <p class="mb-5 text-sm text-slate-400">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>

        <div x-data="{ open: false }">
            <button type="button" @click="open = true"
                    class="rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-2 text-sm font-medium text-rose-300 transition hover:bg-rose-400/20">
                {{ __('Delete Account') }}
            </button>

            <div x-show="open" x-transition class="mt-4 rounded-xl border border-white/10 bg-white/5 p-5" style="display:none">
                <p class="mb-4 text-sm text-slate-300">{{ __('Are you sure you want to delete your account? Enter your password to confirm.') }}</p>
                <form method="post" action="{{ route('profile.destroy') }}" class="space-y-3">
                    @csrf @method('delete')
                    <div>
                        <label class="glass-label">{{ __('Password') }}</label>
                        <input name="password" type="password" class="glass-input" placeholder="{{ __('Password') }}" required>
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1 text-xs text-rose-400" />
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-600">
                            {{ __('Delete Account') }}
                        </button>
                        <button type="button" @click="open = false" class="btn-ghost">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection
