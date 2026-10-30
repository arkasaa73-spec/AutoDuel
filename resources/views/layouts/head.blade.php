<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#F3EFE6">

<title>{{ config('app.name', 'AutoDuel') }}</title>

{{-- Иконка вкладки прямо в коде, чтобы не было запроса /favicon.ico --}}
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' fill='%2316130F'/%3E%3Cpath d='M6 24 16 7l10 17h-5l-5-9-5 9z' fill='%23FFC21A'/%3E%3C/svg%3E">

{{-- Oswald - заголовки и цифры, Golos Text - основной текст (оба с кириллицей) --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=oswald:500,600,700|golos-text:400,500,600,700&display=swap">

@vite(['resources/css/app.css', 'resources/js/app.js'])
