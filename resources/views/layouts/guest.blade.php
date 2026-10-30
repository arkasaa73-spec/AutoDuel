<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
</head>
<body class="min-h-screen">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ url('/') }}" aria-label="AutoDuel - на главную">
            <x-brand class="text-4xl" />
        </a>

        <div class="mt-8 w-full max-w-md border-2 border-ink bg-white p-6 shadow-hard sm:p-8">
            {{ $slot }}
        </div>

        <div class="road-line mt-10 w-40"></div>
    </div>
</body>
</html>
