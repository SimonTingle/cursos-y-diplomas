@extends('layouts.portal')

@section('title', __('Portal'))

@section('content')
    {{-- Info box (placeholder copy — real text added later) --}}
    <section class="glass p-6 sm:p-8">
        <h2 class="mb-2 text-xl font-semibold text-white">{{ __('Welcome to RCP CANARIAS') }}</h2>
        <p class="max-w-3xl text-sm leading-relaxed text-slate-300">
            {{ __('portal.intro_placeholder') }}
        </p>
    </section>

    {{-- Events this month --}}
    <section class="glass p-6 sm:p-8">
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 class="text-lg font-semibold text-white">{{ __('This month') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('portal', ['month' => $prevMonth]) }}" class="btn-ghost !px-3" aria-label="{{ __('Previous month') }}">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M12.7 15.3a1 1 0 0 1-1.4 0l-5-5a1 1 0 0 1 0-1.4l5-5a1 1 0 1 1 1.4 1.4L8.42 9.6l4.28 4.3a1 1 0 0 1 0 1.4Z"/></svg>
                </a>
                <span class="min-w-[9rem] text-center text-sm font-medium text-slate-200">{{ $monthLabel }}</span>
                <a href="{{ route('portal', ['month' => $nextMonth]) }}" class="btn-ghost !px-3" aria-label="{{ __('Next month') }}">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7.3 4.7a1 1 0 0 1 1.4 0l5 5a1 1 0 0 1 0 1.4l-5 5a1 1 0 0 1-1.4-1.4l4.28-4.3-4.28-4.3a1 1 0 0 1 0-1.4Z"/></svg>
                </a>
            </div>
        </div>

        @forelse ($events as $event)
            @php $accent = $event->color ?: optional($event->instructor)->color ?: '#6366f1'; @endphp
            <div class="flex items-center gap-4 border-t border-white/5 py-3 first:border-t-0">
                {{-- date chip --}}
                <div class="flex h-12 w-12 flex-none flex-col items-center justify-center rounded-xl border border-white/10 bg-white/5">
                    <span class="text-[10px] uppercase tracking-wider text-slate-400">{{ ucfirst($event->start_at->translatedFormat('M')) }}</span>
                    <span class="text-lg font-semibold leading-none text-white">{{ $event->start_at->format('d') }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 flex-none rounded-full" style="background: {{ $accent }}"></span>
                        <p class="truncate font-medium text-white">{{ $event->title }}</p>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400">
                        {{ $event->all_day ? __('All day') : $event->start_at->format('H:i') }}
                        @if ($event->instructor) · {{ $event->instructor->name }} @endif
                        @if ($event->location) · {{ $event->location }} @endif
                    </p>
                </div>
                <span class="hidden flex-none rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] font-medium text-slate-300 sm:inline-block">
                    {{ __(ucfirst($event->status)) }}
                </span>
            </div>
        @empty
            <p class="py-6 text-center text-sm text-slate-400">{{ __('No sessions scheduled this month.') }}</p>
        @endforelse

        <div class="mt-5 text-center">
            <a href="{{ route('instructors-calendar') }}" class="btn-primary">{{ __('Open full calendar') }}</a>
        </div>
    </section>

    {{-- Past event photos (placeholder folder gallery) --}}
    <section class="glass p-6 sm:p-8">
        <h2 class="mb-5 text-lg font-semibold text-white">{{ __('Past events') }}</h2>
        @if (count($photos))
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($photos as $photo)
                    <div class="group relative aspect-[4/3] overflow-hidden rounded-xl border border-white/10 bg-white/5">
                        <img src="{{ $photo }}" alt="{{ __('Past event') }}"
                             loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                @endforeach
            </div>
        @else
            <p class="py-6 text-center text-sm text-slate-400">{{ __('No photos yet.') }}</p>
        @endif
    </section>
@endsection
