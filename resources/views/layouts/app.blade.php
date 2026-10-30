<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
</head>
<body class="flex min-h-screen flex-col">
    @include('layouts.navigation')

    @isset($header)
        <div class="border-b border-line bg-paper-deep">
            <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
                {{ $header }}
            </div>
        </div>
    @endisset

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t-2 border-ink">
        <div class="road-line"></div>
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-ink-muted sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p><x-brand class="text-base text-ink" /> <span class="ml-1">учебный проект</span></p>
            <p>Цены и мощность приблизительные, базовая комплектация.</p>
        </div>
    </footer>
</body>
</html>
