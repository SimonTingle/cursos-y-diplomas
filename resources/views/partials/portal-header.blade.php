@php
    $isAdmin = $isAdmin ?? false;
    $showCalendarLink = $showCalendarLink ?? true;
    $showPortalLink = $showPortalLink ?? false;
@endphp

{{-- Shared top bar for the portal / calendar pages --}}
<header class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8">
    <a href="{{ route('portal') }}" class="flex items-center gap-3">
        <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 via-violet-500 to-cyan-400 text-lg font-bold text-white shadow-lg">A</div>
        <div>
            <h1 class="text-lg font-semibold leading-tight text-white">ACES Point</h1>
            <p class="text-xs text-slate-400">{{ __('Instructors Calendar') }}</p>
        </div>
    </a>

    <div class="flex items-center gap-3">
        @if ($showPortalLink)
            <a href="{{ route('portal') }}" class="btn-ghost">{{ __('Portal') }}</a>
        @endif
        @if ($showCalendarLink)
            <a href="{{ route('instructors-calendar') }}" class="btn-ghost">{{ __('Calendar') }}</a>
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
