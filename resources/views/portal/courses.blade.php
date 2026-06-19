@extends('layouts.portal')

@section('title', __('Courses'))

@section('content')
    <section class="glass p-6 sm:p-8">
        <h1 class="text-xl font-semibold text-white">{{ __('Courses') }}</h1>
        <p class="mt-1 text-sm text-slate-400">{{ __('Browse available courses and enroll.') }}</p>
    </section>

    @if ($isAdmin)
        <section class="glass p-6 sm:p-8">
            <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Add course') }}</h2>
            <form method="POST" action="{{ route('portal.courses.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="glass-label">{{ __('Title') }}</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="glass-input" required>
                        @error('title') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="glass-label">{{ __('Starts') }}</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="glass-input">
                    </div>
                    <div>
                        <label class="glass-label">{{ __('Capacity') }}</label>
                        <input type="number" min="1" name="capacity" value="{{ old('capacity') }}" class="glass-input" placeholder="{{ __('Optional') }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="glass-label">{{ __('Description') }}</label>
                        <textarea name="description" rows="3" class="glass-input">{{ old('description') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn-primary">{{ __('Add course') }}</button>
            </form>
        </section>
    @endif

    <section class="glass p-6 sm:p-8">
        @forelse ($courses as $course)
            <div class="flex flex-col gap-3 border-t border-white/5 py-5 first:border-t-0 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-white">{{ $course->title }}</h3>
                        @unless ($course->is_active)
                            <span class="rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[11px] text-slate-400">{{ __('Inactive') }}</span>
                        @endunless
                    </div>
                    @if ($course->starts_at)
                        <p class="mt-0.5 text-xs text-slate-400">{{ ucfirst($course->starts_at->translatedFormat('D, d M Y · H:i')) }}</p>
                    @endif
                    @if ($course->description)
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">{{ $course->description }}</p>
                    @endif
                    <p class="mt-2 text-xs text-slate-500">
                        {{ trans_choice('{0} No one enrolled yet|{1} :count person enrolled|[2,*] :count people enrolled', $course->students_count, ['count' => $course->students_count]) }}
                        @if ($course->capacity) · {{ __('Capacity') }}: {{ $course->capacity }} @endif
                    </p>
                </div>

                <div class="flex flex-none items-center gap-2">
                    @if (in_array($course->id, $enrolledIds))
                        <span class="inline-flex items-center gap-1 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-3 py-2 text-sm font-medium text-emerald-300">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L4.3 10.7a1 1 0 1 1 1.4-1.4l2.8 2.79 6.8-6.79a1 1 0 0 1 1.4 0Z"/></svg>
                            {{ __('Enrolled') }}
                        </span>
                        <form method="POST" action="{{ route('portal.courses.unenroll', $course) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-ghost">{{ __('Cancel enrollment') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('portal.courses.enroll', $course) }}">
                            @csrf
                            <button type="submit" class="btn-primary">{{ __('Enroll') }}</button>
                        </form>
                    @endif

                    @if ($isAdmin)
                        <a href="{{ route('portal.courses.manage', $course) }}" class="rounded-xl px-3 py-2 text-sm font-medium text-indigo-300 transition hover:bg-indigo-500/10">{{ __('Manage') }}</a>
                        <form method="POST" action="{{ route('portal.courses.destroy', $course) }}"
                              onsubmit="return confirm('{{ __('Delete this course?') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-xl px-3 py-2 text-sm font-medium text-rose-300 transition hover:bg-rose-500/10">{{ __('Delete') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="py-6 text-center text-sm text-slate-400">{{ __('No courses yet.') }}</p>
        @endforelse
    </section>
@endsection
