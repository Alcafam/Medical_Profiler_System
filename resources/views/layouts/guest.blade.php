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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="flex flex-col items-center px-6 text-center">
                <a href="/" class="inline-block">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Holy Ghost Baptist Church"
                        class="rounded-full object-cover ring-2 ring-amber-400/40"
                        width="100"
                        height="100"
                        style="width: 100px; height: 100px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35), 0 2px 6px rgba(0, 0, 0, 0.2);"
                    >
                </a>

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-800 uppercase">
                    Holy Ghost Baptist Church
                </h1>

                @isset($header)
                    <div class="mt-2 max-w-md text-italic">
                        <em>{{ $header }}</em>
                    </div>
                @endisset
            </div>

            <div class="w-full sm:max-w-md mt-8 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
