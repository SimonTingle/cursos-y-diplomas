@extends('layouts.portal')

@section('title', __('PDFs'))

@section('content')
    <section class="glass p-6 sm:p-8">
        <h1 class="text-xl font-semibold text-white">{{ __('PDF catalogue') }}</h1>
        <p class="mt-1 text-sm text-slate-400">{{ __('Documents and materials to download.') }}</p>
    </section>

    @if ($isAdmin)
        <section class="glass p-6 sm:p-8">
            <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Upload PDF') }}</h2>
            <form method="POST" action="{{ route('portal.pdfs.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="glass-label">{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="glass-input" required>
                    @error('title') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="glass-label">{{ __('File') }} (PDF)</label>
                    <input type="file" name="file" accept="application/pdf" class="glass-input" required>
                    @error('file') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary">{{ __('Upload PDF') }}</button>
                </div>
            </form>
        </section>
    @endif

    <section class="glass p-6 sm:p-8">
        @forelse ($pdfs as $pdf)
            <div class="flex items-center gap-4 border-t border-white/5 py-3 first:border-t-0">
                <svg class="h-8 w-8 flex-none text-rose-300" viewBox="0 0 20 20" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7l-5-5H6Zm5 1.5L15.5 8H12a1 1 0 0 1-1-1V3.5Z"/></svg>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-white">{{ $pdf->title }}</p>
                    @if ($pdf->size)
                        <p class="text-xs text-slate-500">{{ number_format($pdf->size / 1024, 0) }} KB</p>
                    @endif
                </div>
                <a href="{{ $pdf->url }}" target="_blank" rel="noopener" class="btn-ghost">{{ __('Open') }}</a>
                @if ($isAdmin)
                    <form method="POST" action="{{ route('portal.pdfs.destroy', $pdf) }}"
                          onsubmit="return confirm('{{ __('Delete this PDF?') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-rose-300 transition hover:bg-rose-500/10">{{ __('Delete') }}</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="py-6 text-center text-sm text-slate-400">{{ __('No PDFs yet.') }}</p>
        @endforelse
    </section>
@endsection
