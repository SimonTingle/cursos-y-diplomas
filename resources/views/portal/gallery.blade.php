@extends('layouts.portal')

@section('title', __('Image gallery'))

@section('content')
    <section class="glass p-6 sm:p-8">
        <h1 class="text-xl font-semibold text-white">{{ __('Image gallery') }}</h1>
        <p class="mt-1 text-sm text-slate-400">{{ __('Photos managed by the team.') }}</p>
    </section>

    @if ($isAdmin)
        <section class="glass p-6 sm:p-8">
            <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Upload image') }}</h2>
            <form method="POST" action="{{ route('portal.gallery.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="glass-label">{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="glass-input" placeholder="{{ __('Optional') }}">
                </div>
                <div>
                    <label class="glass-label">{{ __('Image') }}</label>
                    <input type="file" name="image" accept="image/*" class="glass-input" required>
                    @error('image') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary">{{ __('Upload image') }}</button>
                </div>
            </form>
        </section>
    @endif

    <section class="glass p-6 sm:p-8">
        @if ($images->isNotEmpty())
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($images as $image)
                    <div class="group relative aspect-[4/3] overflow-hidden rounded-xl border border-white/10 bg-white/5">
                        <img src="{{ $image->url }}" alt="{{ $image->title ?? __('Gallery image') }}"
                             loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @if ($image->title)
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2 text-xs text-white">{{ $image->title }}</div>
                        @endif
                        @if ($isAdmin)
                            <form method="POST" action="{{ route('portal.gallery.destroy', $image) }}"
                                  onsubmit="return confirm('{{ __('Delete this image?') }}')"
                                  class="absolute right-1.5 top-1.5">
                                @csrf @method('DELETE')
                                <button type="submit" class="grid h-7 w-7 place-items-center rounded-lg bg-black/50 text-rose-300 backdrop-blur transition hover:bg-rose-500/30" aria-label="{{ __('Delete') }}">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="py-6 text-center text-sm text-slate-400">{{ __('No images yet.') }}</p>
        @endif
    </section>
@endsection
