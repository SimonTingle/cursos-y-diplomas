<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Instructors Calendar') }} · ACES Point</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans">
@php
    $i18n = [
        'editSession' => __('Edit session'),
        'newSession' => __('New session'),
        'create' => __('Create'),
        'update' => __('Update'),
        'saving' => __('Saving…'),
        'delete' => __('Delete'),
        'deleting' => __('Deleting…'),
        'confirmDelete' => __('Delete this session?'),
        'saveError' => __('Could not save the session.'),
        'deleteError' => __('Could not delete the session.'),
        'moveError' => __('Could not move the session.'),
    ];
@endphp
<div
    class="flex h-screen flex-col"
    x-data="calendarApp({
        eventsUrl: '{{ url('/events') }}',
        isAdmin: {{ $isAdmin ? 'true' : 'false' }},
        locale: @js(str_replace('_', '-', app()->getLocale())),
        i18n: @js($i18n),
    })"
>
    {{-- Top bar --}}
    <header class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 via-violet-500 to-cyan-400 text-lg font-bold text-white shadow-lg">A</div>
            <div>
                <h1 class="text-lg font-semibold leading-tight text-white">{{ __('Instructors Calendar') }}</h1>
                <p class="text-xs text-slate-400">ACES Point · {{ __('scheduling') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden sm:block">
                <select x-model="instructorFilter" @change="refilter()" class="glass-input min-w-[12rem]">
                    <option value="">{{ __('All instructors') }}</option>
                    @foreach ($instructors as $instructor)
                        <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($isAdmin)
                <button type="button" class="btn-primary" @click="openCreate(new Date(), null, false)">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1Z"/></svg>
                    {{ __('New session') }}
                </button>
            @endif

            <span class="hidden rounded-full border px-3 py-1 text-xs font-semibold sm:inline-block
                {{ $isAdmin ? 'border-cyan-400/40 bg-cyan-400/10 text-cyan-300' : 'border-white/15 bg-white/5 text-slate-300' }}">
                {{ $isAdmin ? __('Admin') : __('Instructor') }}
            </span>

            {{-- account menu --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="btn-ghost">
                    {{ Auth::user()->name ?? __('Account') }}
                </button>
                <div x-show="open" @click.outside="open = false" x-transition
                     class="glass absolute right-0 z-20 mt-2 w-48 overflow-hidden p-1 text-sm" style="display:none">
                    {{-- language switcher --}}
                    <div class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Language') }}</div>
                    @foreach (['en' => 'English', 'es' => 'Español'] as $code => $label)
                        <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}"
                           class="flex items-center justify-between rounded-lg px-3 py-2 text-slate-200 hover:bg-white/10">
                            {{ $label }}
                            @if (app()->getLocale() === $code)
                                <svg class="h-4 w-4 text-cyan-300" viewBox="0 0 20 20" fill="currentColor"><path d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0l-3.5-3.5a1 1 0 1 1 1.4-1.4l2.8 2.79 6.8-6.79a1 1 0 0 1 1.4 0Z"/></svg>
                            @endif
                        </a>
                    @endforeach
                    <div class="my-1 border-t border-white/10"></div>
                    <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2 text-slate-200 hover:bg-white/10">{{ __('Profile') }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-slate-200 hover:bg-white/10">{{ __('Log out') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- mobile filter --}}
    <div class="px-5 pb-2 sm:hidden">
        <select x-model="instructorFilter" @change="refilter()" class="glass-input w-full">
            <option value="">{{ __('All instructors') }}</option>
            @foreach ($instructors as $instructor)
                <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Calendar --}}
    <main class="flex-1 px-5 pb-5 sm:px-8 sm:pb-8">
        <div class="glass h-full p-3 sm:p-5">
            <div x-ref="calendar" class="h-full"></div>
        </div>
    </main>

    {{-- Create / edit modal --}}
    <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="glass relative z-10 w-full max-w-lg p-6" x-transition>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white" x-text="form.id ? i18n.editSession : i18n.newSession"></h2>
                <button @click="modalOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                </button>
            </div>

            <form @submit.prevent="save()" class="space-y-4">
                <div>
                    <label class="glass-label">{{ __('Title') }}</label>
                    <input type="text" x-model="form.title" class="glass-input" placeholder="{{ __('e.g. Advanced Tactical Course') }}" required>
                    <p x-show="errors.title" x-text="errors.title?.[0]" class="mt-1 text-xs text-rose-400"></p>
                </div>

                <div>
                    <label class="glass-label">{{ __('Instructor') }}</label>
                    <select x-model="form.instructor_id" class="glass-input">
                        <option value="">{{ __('— Unassigned —') }}</option>
                        @foreach ($instructors as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="glass-label">{{ __('Starts') }}</label>
                        <input type="datetime-local" x-model="form.start_at" class="glass-input" required>
                        <p x-show="errors.start_at" x-text="errors.start_at?.[0]" class="mt-1 text-xs text-rose-400"></p>
                    </div>
                    <div>
                        <label class="glass-label">{{ __('Ends') }}</label>
                        <input type="datetime-local" x-model="form.end_at" class="glass-input">
                        <p x-show="errors.end_at" x-text="errors.end_at?.[0]" class="mt-1 text-xs text-rose-400"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="glass-label">{{ __('Location') }}</label>
                        <input type="text" x-model="form.location" class="glass-input" placeholder="{{ __('Room / venue') }}">
                    </div>
                    <div>
                        <label class="glass-label">{{ __('Status') }}</label>
                        <select x-model="form.status" class="glass-input">
                            <option value="scheduled">{{ __('Scheduled') }}</option>
                            <option value="completed">{{ __('Completed') }}</option>
                            <option value="cancelled">{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" x-model="form.all_day" class="rounded border-white/20 bg-white/5 text-indigo-500 focus:ring-indigo-500/40">
                        {{ __('All day') }}
                    </label>
                    <div class="flex items-center gap-2">
                        <label class="glass-label !mb-0">{{ __('Color') }}</label>
                        <input type="color" x-model="form.color" class="h-9 w-12 cursor-pointer rounded-lg border border-white/10 bg-transparent">
                    </div>
                </div>

                <div>
                    <label class="glass-label">{{ __('Notes') }}</label>
                    <textarea x-model="form.description" rows="2" class="glass-input" placeholder="{{ __('Optional details') }}"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" x-show="form.id" @click="remove()" :disabled="deleting"
                            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-rose-300 transition hover:bg-rose-500/10">
                        <span x-text="deleting ? i18n.deleting : i18n.delete"></span>
                    </button>
                    <div class="ml-auto flex gap-2">
                        <button type="button" class="btn-ghost" @click="modalOpen = false">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-primary" :disabled="saving">
                            <span x-text="saving ? i18n.saving : (form.id ? i18n.update : i18n.create)"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
