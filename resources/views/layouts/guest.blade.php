<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <div class="mb-6 flex flex-col items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="RCP CANARIAS" class="h-36 w-36 rounded-xl object-contain shadow-2xl shadow-white/50">
                <p class="text-lg font-semibold leading-tight text-white">RCP CANARIAS</p>
            </div>

            <div class="glass w-full overflow-hidden p-6 sm:max-w-md">
                {{ $slot }}
            </div>

            <div class="mt-5 flex items-center gap-1 text-sm">
                @foreach (['en' => 'English', 'es' => 'Español'] as $code => $label)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}"
                       class="rounded-lg px-3 py-1 transition {{ app()->getLocale() === $code ? 'bg-white/10 font-semibold text-white' : 'text-slate-400 hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </body>
</html>
