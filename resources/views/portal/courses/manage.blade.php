@extends('layouts.portal')

@section('title', $course->title . ' - ' . __('Manage'))

@section('content')
    <section class="glass p-6 sm:p-8">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-white">{{ $course->title }}</h1>
                <p class="mt-1 text-sm text-slate-400">{{ __('Manage course details, instructor, students, and media.') }}</p>
            </div>
            <a href="{{ route('portal.courses') }}" class="ml-auto text-sm text-indigo-300 hover:text-indigo-200">← {{ __('Back to courses') }}</a>
        </div>
    </section>

    <div x-data="{ activeTab: 'details' }" class="space-y-6">
        {{-- Tab Navigation --}}
        <div class="glass flex flex-wrap gap-2 p-2">
            <button @click="activeTab = 'details'" :class="activeTab === 'details' ? 'bg-indigo-500/30 text-white' : 'text-slate-300 hover:bg-white/10'" class="rounded-lg px-4 py-2 transition">
                {{ __('Details') }}
            </button>
            <button @click="activeTab = 'instructor'" :class="activeTab === 'instructor' ? 'bg-indigo-500/30 text-white' : 'text-slate-300 hover:bg-white/10'" class="rounded-lg px-4 py-2 transition">
                {{ __('Instructor') }}
            </button>
            <button @click="activeTab = 'students'" :class="activeTab === 'students' ? 'bg-indigo-500/30 text-white' : 'text-slate-300 hover:bg-white/10'" class="rounded-lg px-4 py-2 transition">
                {{ __('Students') }}
            </button>
            <button @click="activeTab = 'media'" :class="activeTab === 'media' ? 'bg-indigo-500/30 text-white' : 'text-slate-300 hover:bg-white/10'" class="rounded-lg px-4 py-2 transition">
                {{ __('Media') }}
            </button>
        </div>

        {{-- Details Tab --}}
        <div x-show="activeTab === 'details'" class="glass p-6 sm:p-8">
            <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Course Details') }}</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('Title') }}</label>
                    <p class="mt-1 text-white">{{ $course->title }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('Description') }}</label>
                    <p class="mt-1 text-slate-200">{{ $course->description ?? '—' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('Starts') }}</label>
                    <p class="mt-1 text-slate-200">
                        @if ($course->starts_at)
                            {{ $course->starts_at->translatedFormat('D, d M Y · H:i') }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('Capacity') }}</label>
                    <p class="mt-1 text-slate-200">
                        @if ($course->capacity)
                            {{ $course->capacity }} {{ __('spots') }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('Status') }}</label>
                    <p class="mt-1 text-slate-200">
                        @if ($course->is_active)
                            <span class="inline-flex items-center rounded-full bg-emerald-400/10 px-2 py-1 text-xs font-medium text-emerald-300">{{ __('Active') }}</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-slate-400/10 px-2 py-1 text-xs font-medium text-slate-300">{{ __('Inactive') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Instructor Tab --}}
        <div x-show="activeTab === 'instructor'" class="glass p-6 sm:p-8">
            <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Assign Instructor') }}</h2>
            <form method="POST" action="{{ route('portal.courses.assign-instructor', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="glass-label">{{ __('Instructor') }}</label>
                    <select name="instructor_id" class="glass-input">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($instructors as $instructor)
                            <option value="{{ $instructor->id }}" @selected($course->instructor_id === $instructor->id)>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary">{{ __('Update Instructor') }}</button>
            </form>
            @if ($course->instructor)
                <div class="mt-6 border-t border-white/10 pt-6">
                    <h3 class="mb-3 text-sm font-semibold text-slate-400">{{ __('Currently assigned') }}</h3>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold" style="background-color: {{ $course->instructor->color ?? '#6366f1' }}">
                            {{ substr($course->instructor->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ $course->instructor->name }}</p>
                            @if ($course->instructor->title)
                                <p class="text-xs text-slate-400">{{ $course->instructor->title }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Students Tab --}}
        <div x-show="activeTab === 'students'" class="glass p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Enroll Student') }}</h2>
                <form method="POST" action="{{ route('portal.courses.enroll-student', $course) }}" class="flex gap-2">
                    @csrf
                    <select name="user_id" class="glass-input flex-1" required>
                        <option value="">{{ __('Select student...') }}</option>
                        @foreach ($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary flex-none">{{ __('Enroll') }}</button>
                </form>
            </div>

            <div class="border-t border-white/10 pt-6">
                <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Enrolled Students') }}</h2>
                @if ($course->students->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($course->students as $student)
                            <div class="flex items-center justify-between gap-4 border-t border-white/5 py-3 first:border-t-0">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-semibold text-indigo-300">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">{{ $student->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $student->email }}</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('portal.courses.unenroll-student', [$course, $student]) }}"
                                      onsubmit="return confirm('{{ __('Unenroll this student?') }}')" class="flex-none">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg px-2.5 py-1 text-xs font-medium text-rose-300 transition hover:bg-rose-500/10">
                                        {{ __('Unenroll') }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-6 text-center text-sm text-slate-400">{{ __('No students enrolled yet.') }}</p>
                @endif
            </div>
        </div>

        {{-- Media Tab --}}
        <div x-show="activeTab === 'media'" class="space-y-6">
            {{-- Upload Gallery Images Section --}}
            <div class="glass p-6 sm:p-8">
                <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Upload Images') }}</h2>
                <form method="POST" action="{{ route('portal.course-media.store-image', $course) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="glass-label">{{ __('Title') }}</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="glass-input" placeholder="{{ __('Optional') }}">
                    </div>
                    <div>
                        <label class="glass-label">{{ __('Image') }}</label>
                        <input type="file" name="image" accept="image/*" class="glass-input" required>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary">{{ __('Upload Image') }}</button>
                    </div>
                </form>
                @error('image') <p class="mt-2 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>

            {{-- Display Gallery Images --}}
            <div class="glass p-6 sm:p-8">
                <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Course Images') }}</h2>
                @if ($course->galleryImages->isNotEmpty())
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($course->galleryImages as $image)
                            <div class="group relative aspect-[4/3] overflow-hidden rounded-xl border border-white/10">
                                <img src="{{ $image->url }}" alt="{{ $image->title }}" class="h-full w-full object-cover">
                                @if ($image->is_featured)
                                    <div class="absolute top-1.5 left-1.5 flex items-center gap-1 rounded-lg bg-amber-500/30 px-2 py-1 text-xs text-amber-300">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        {{ __('Featured') }}
                                    </div>
                                @endif
                                <div class="absolute right-1.5 top-1.5 flex gap-1 opacity-0 transition group-hover:opacity-100">
                                    <form method="POST" action="{{ route('portal.media.toggle-featured', $image) }}" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="grid h-7 w-7 place-items-center rounded-lg bg-black/50 text-amber-300 backdrop-blur transition hover:bg-amber-500/30" title="{{ __('Toggle featured') }}">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('portal.course-media.destroy-image', $image) }}" style="display:inline" onsubmit="return confirm('{{ __('Delete this image?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="grid h-7 w-7 place-items-center rounded-lg bg-black/50 text-rose-300 backdrop-blur transition hover:bg-rose-500/30">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-6 text-center text-sm text-slate-400">{{ __('No images yet.') }}</p>
                @endif
            </div>

            {{-- Upload PDF Section --}}
            <div class="glass p-6 sm:p-8">
                <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Upload PDFs') }}</h2>
                <form method="POST" action="{{ route('portal.course-media.store-pdf', $course) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="glass-label">{{ __('Title') }}</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="glass-input" required>
                    </div>
                    <div>
                        <label class="glass-label">{{ __('PDF File') }}</label>
                        <input type="file" name="file" accept=".pdf" class="glass-input" required>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary">{{ __('Upload PDF') }}</button>
                    </div>
                </form>
                @error('file') <p class="mt-2 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>

            {{-- Display PDFs --}}
            <div class="glass p-6 sm:p-8">
                <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Course PDFs') }}</h2>
                @if ($course->pdfs->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($course->pdfs as $pdf)
                            <div class="flex items-center justify-between gap-4 border-t border-white/5 py-3 first:border-t-0">
                                <div class="flex min-w-0 items-center gap-3">
                                    <svg class="h-5 w-5 flex-none text-red-400" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm0 2h12v10H4V5z"/></svg>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-white">{{ $pdf->title }}</p>
                                        <p class="text-xs text-slate-400">{{ $pdf->original_name }}</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('portal.course-media.destroy-pdf', $pdf) }}" style="display:inline" onsubmit="return confirm('{{ __('Delete this PDF?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg px-2.5 py-1 text-xs font-medium text-rose-300 transition hover:bg-rose-500/10">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-6 text-center text-sm text-slate-400">{{ __('No PDFs yet.') }}</p>
                @endif
            </div>

            {{-- Upload Video Section --}}
            <div class="glass p-6 sm:p-8">
                <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Add YouTube Video') }}</h2>
                <form method="POST" action="{{ route('portal.course-media.store-video', $course) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="glass-label">{{ __('Title') }}</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="glass-input" required>
                    </div>
                    <div>
                        <label class="glass-label">{{ __('YouTube URL') }}</label>
                        <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="glass-input" required>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary">{{ __('Add Video') }}</button>
                    </div>
                </form>
                @error('youtube_url') <p class="mt-2 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>

            {{-- Display Videos --}}
            <div class="glass p-6 sm:p-8">
                <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Course Videos') }}</h2>
                @if ($course->videos->isNotEmpty())
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach ($course->videos as $video)
                            <div class="group relative aspect-video overflow-hidden rounded-xl border border-white/10 bg-slate-900">
                                @if ($video->embedUrl)
                                    <iframe src="{{ $video->embedUrl }}" allowfullscreen class="h-full w-full"></iframe>
                                @else
                                    <div class="flex h-full items-center justify-center bg-slate-800">
                                        <p class="text-xs text-slate-400">{{ __('Invalid YouTube URL') }}</p>
                                    </div>
                                @endif
                                <div class="absolute top-2 right-2 opacity-0 transition group-hover:opacity-100">
                                    <form method="POST" action="{{ route('portal.course-media.destroy-video', $video) }}" style="display:inline" onsubmit="return confirm('{{ __('Delete this video?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="grid h-7 w-7 place-items-center rounded-lg bg-black/50 text-rose-300 backdrop-blur transition hover:bg-rose-500/30">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-6 text-center text-sm text-slate-400">{{ __('No videos yet.') }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection
