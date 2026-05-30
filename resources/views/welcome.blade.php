<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RCP CANARIAS · {{ __('Instructors Calendar') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans">
<div class="flex min-h-screen flex-col">
    {{-- top bar: brand + language switcher --}}
    <header class="flex items-center justify-between px-5 py-4 sm:px-8">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo.png') }}" alt="RCP CANARIAS" class="h-12 w-12 rounded-lg object-contain">
            <div>
                <p class="text-lg font-semibold leading-tight text-white">RCP CANARIAS</p>
                <p class="text-xs text-slate-400">{{ __('Instructors Calendar') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm">
            @foreach (['en' => 'English', 'es' => 'Español'] as $code => $label)
                <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}"
                   class="rounded-lg px-3 py-1 transition {{ app()->getLocale() === $code ? 'bg-white/10 font-semibold text-white' : 'text-slate-400 hover:text-white' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-8 px-5 py-8 sm:px-8">
        {{-- Row 1: two equal boxes --}}
        <div class="grid gap-8 lg:grid-cols-2">
            {{-- Left: course info box --}}
            <section class="glass p-7 sm:p-9">
                <span class="inline-flex items-center rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300">
                    {{ __('Courses') }}
                </span>
                <h1 class="mt-4 text-2xl font-semibold leading-tight text-white sm:text-3xl">
                    {{ __('Train the next generation of instructors') }}
                </h1>
                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ __('landing.course_intro') }}</p>
            </section>

            {{-- Right: auth actions (same size as the course box) --}}
            <section class="glass flex flex-col p-7 sm:p-9">
                <h2 class="text-lg font-semibold text-white">{{ __('Access your account') }}</h2>
                <p class="mt-1 text-sm text-slate-400">{{ __('Sign in to access your portal.') }}</p>

                <div class="mt-6 space-y-3">
                    <a href="{{ route('login') }}" class="btn-primary w-full">{{ __('Sign in') }}</a>
                    <a href="{{ route('password.request') }}" class="btn-ghost w-full">{{ __('Change password') }}</a>
                </div>

                {{-- WhatsApp contact --}}
                <div class="mt-8 border-t border-white/10 pt-6">
                    <a href="https://wa.me/34639846448"
                       target="_blank" rel="noopener"
                       class="flex items-center gap-3 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm font-medium text-emerald-200 transition hover:bg-emerald-400/20">
                        <svg class="h-5 w-5 flex-none" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.554 4.121 1.523 5.855L.057 23.885a.5.5 0 0 0 .611.612l6.083-1.48A11.94 11.94 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 0 1-5.003-1.372l-.359-.213-3.722.906.936-3.63-.234-.373A9.818 9.818 0 0 1 2.182 12C2.182 6.57 6.57 2.182 12 2.182c5.43 0 9.818 4.388 9.818 9.818 0 5.43-4.388 9.818-9.818 9.818z"/>
                        </svg>
                        <span>{{ __('Request information') }} +34 639 846 448 {{ __('via WhatsApp') }}</span>
                    </a>
                </div>
            </section>
        </div>

        {{-- Row 2: image gallery (full width, placeholder folder) --}}
        <section class="glass p-7 sm:p-9">
            <h2 class="mb-5 text-lg font-semibold text-white">{{ __('Image gallery') }}</h2>
            @if (count($photos))
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($photos as $photo)
                        <div class="group relative aspect-[4/3] overflow-hidden rounded-xl border border-white/10 bg-white/5">
                            <img src="{{ $photo }}" alt="{{ __('Past event') }}" loading="lazy"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-6 text-center text-sm text-slate-400">{{ __('No photos yet.') }}</p>
            @endif
        </section>

        {{-- Row 3: próximo curso (full width, info only) --}}
        <section class="glass p-7 sm:p-9">
            <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Upcoming course') }}</h2>
            @if ($nextCourse)
                <h3 class="text-xl font-semibold text-white">{{ $nextCourse->title }}</h3>
                @if ($nextCourse->starts_at)
                    <p class="mt-1 text-sm text-cyan-300">{{ ucfirst($nextCourse->starts_at->translatedFormat('D, d M Y · H:i')) }}</p>
                @endif
                @if ($nextCourse->description)
                    <p class="mt-3 max-w-3xl whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $nextCourse->description }}</p>
                @endif
            @else
                <p class="py-6 text-center text-sm text-slate-400">{{ __('No upcoming courses.') }}</p>
            @endif
        </section>
    </main>

    <footer class="px-5 py-6 text-center text-xs text-slate-500 sm:px-8">
        © {{ date('Y') }} RCP CANARIAS
    </footer>
</div>
</body>
</html>
