@extends('layouts.portal')

@section('title', __('Video library'))

@section('content')
    <section class="glass p-6 sm:p-8">
        <h1 class="text-xl font-semibold text-white">{{ __('Video library') }}</h1>
        <p class="mt-1 text-sm text-slate-400">{{ __('Training videos and recordings.') }}</p>
    </section>

    @if ($isAdmin)
        <section class="glass p-6 sm:p-8">
            <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Add video') }}</h2>
            <form method="POST" action="{{ route('portal.videos.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="glass-label">{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="glass-input" required>
                    @error('title') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="glass-label">{{ __('YouTube link') }}</label>
                    <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="glass-input" placeholder="https://www.youtube.com/watch?v=…" required>
                    @error('youtube_url') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary">{{ __('Add video') }}</button>
                </div>
            </form>
        </section>
    @endif

    <section class="glass p-6 sm:p-8">
        @if ($videos->isNotEmpty())
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @foreach ($videos as $video)
                    <div>
                        <div class="aspect-video overflow-hidden rounded-xl border border-white/10 bg-black">
                            @if ($video->embed_url)
                                <iframe class="h-full w-full" src="{{ $video->embed_url }}"
                                        title="{{ $video->title }}" loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="truncate font-medium text-white">{{ $video->title }}</p>
                            @if ($isAdmin)
                                <form method="POST" action="{{ route('portal.videos.destroy', $video) }}"
                                      onsubmit="return confirm('{{ __('Delete this video?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex-none rounded-lg px-2 py-1 text-xs font-medium text-rose-300 transition hover:bg-rose-500/10">{{ __('Delete') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="py-6 text-center text-sm text-slate-400">{{ __('No videos yet.') }}</p>
        @endif
    </section>
@endsection
