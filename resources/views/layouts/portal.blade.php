<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Portal')) · ACES Point</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans">
@php
    $navItems = [
        ['route' => 'portal',          'label' => __('Home'),            'icon' => 'M11.3 3.3a1 1 0 0 1 1.4 0l6 6A1 1 0 0 1 18 11h-1v5a1 1 0 0 1-1 1h-3v-4h-2v4H8a1 1 0 0 1-1-1v-5H6a1 1 0 0 1-.7-1.7l6-6Z'],
        ['route' => 'portal.courses',  'label' => __('Courses'),         'icon' => 'M3 5a2 2 0 0 1 2-2h6v14H5a2 2 0 0 1-2-2V5Zm10-2h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-2V3Z'],
        ['route' => 'portal.videos',   'label' => __('Video library'),   'icon' => 'M4 5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H4Zm12 2.5 3.2-1.9a.6.6 0 0 1 .9.5v7.8a.6.6 0 0 1-.9.5L16 12.5v-5Z'],
        ['route' => 'portal.pdfs',     'label' => __('PDFs'),            'icon' => 'M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7l-5-5H6Zm5 1.5L15.5 8H12a1 1 0 0 1-1-1V3.5Z'],
        ['route' => 'portal.gallery',  'label' => __('Image gallery'),   'icon' => 'M4 4a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H4Zm0 10 3.5-4.5 2.5 3 3-4L16 14H4Zm3-6.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Z'],
    ];
@endphp
<div class="flex min-h-screen flex-col">
    @include('partials.portal-header', ['isAdmin' => $isAdmin ?? false, 'showCalendarLink' => true])

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 px-5 pb-10 sm:px-8 lg:flex-row">
        {{-- Left nav --}}
        <aside class="lg:w-60 lg:flex-none">
            <nav class="glass flex gap-2 overflow-x-auto p-2 lg:flex-col lg:gap-1 lg:p-3">
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-none items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition
                              {{ $active ? 'bg-gradient-to-r from-indigo-500/30 to-cyan-400/20 text-white ring-1 ring-white/10' : 'text-slate-300 hover:bg-white/10' }}">
                        <svg class="h-5 w-5 flex-none {{ $active ? 'text-cyan-300' : 'text-slate-400' }}" viewBox="0 0 20 20" fill="currentColor"><path d="{{ $item['icon'] }}"/></svg>
                        <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- Content --}}
        <main class="min-w-0 flex-1 space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
