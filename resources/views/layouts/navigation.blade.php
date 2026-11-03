@php
    // Класс ссылки в меню: активная подчёркнута жёлтым
    $link = 'px-3 py-2 font-display text-sm font-medium uppercase tracking-wider border-b-4 transition';
    $active = 'border-signal text-ink';
    $idle = 'border-transparent text-ink-soft hover:border-ink hover:text-ink';
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b-2 border-ink bg-paper">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
        <a href="{{ url('/') }}" aria-label="AutoDuel - на главную">
            <x-brand class="text-2xl" />
        </a>

        {{-- Меню для широких экранов --}}
        <nav class="hidden items-center gap-2 md:flex" aria-label="Основное меню">
            <a href="{{ url('/') }}" class="{{ $link }} {{ request()->is('/') ? $active : $idle }}">Главная</a>
            <a href="{{ url('/') }}#modes" class="{{ $link }} {{ $idle }}">Играть</a>
            <a href="{{ route('leaderboard') }}" class="{{ $link }} {{ request()->routeIs('leaderboard') ? $active : $idle }}">Рейтинг</a>
            @auth
                <a href="{{ route('dashboard') }}" class="{{ $link }} {{ request()->routeIs('dashboard') ? $active : $idle }}">Кабинет</a>
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.cars.index') }}" class="{{ $link }} {{ request()->routeIs('admin.*') ? $active : $idle }}">Админка</a>
                @endif
            @endauth
        </nav>

        <div class="hidden items-center gap-3 md:flex">
            @auth
                <div class="relative" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape.window="menu = false">
                    <button type="button" @click="menu = !menu" :aria-expanded="menu"
                            class="flex items-center gap-2 border-2 border-ink bg-white px-3 py-1.5 font-display text-sm uppercase tracking-wider hover:bg-signal">
                        {{ Auth::user()->name }}
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M5.3 7.3a1 1 0 0 1 1.4 0L10 10.6l3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 0-1.4z"/></svg>
                    </button>

                    <div x-show="menu" x-cloak class="absolute right-0 mt-2 w-48 border-2 border-ink bg-white shadow-hard-sm">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm hover:bg-signal">Профиль</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full border-t border-line px-4 py-2.5 text-left text-sm hover:bg-signal">Выйти</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="{{ $link }} {{ $idle }}">Войти</a>
                <a href="{{ route('register') }}" class="btn btn-dark !px-4 !py-2 !text-sm">Регистрация</a>
            @endauth
        </div>

        {{-- Кнопка-бургер для телефона --}}
        <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Меню"
                class="border-2 border-ink p-2 hover:bg-signal md:hidden">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path x-show="!open" d="M4 7h16M4 12h16M4 17h16"/>
                <path x-show="open" x-cloak d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </div>

    {{-- Мобильное меню --}}
    <div x-show="open" x-cloak class="border-t-2 border-ink bg-paper md:hidden">
        <nav class="mx-auto flex max-w-6xl flex-col px-4 py-3 sm:px-6" aria-label="Мобильное меню">
            <a href="{{ url('/') }}" class="py-3 font-display uppercase tracking-wider">Главная</a>
            <a href="{{ url('/') }}#modes" @click="open = false" class="py-3 font-display uppercase tracking-wider">Играть</a>
            <a href="{{ route('leaderboard') }}" class="py-3 font-display uppercase tracking-wider">Рейтинг</a>
            @auth
                <a href="{{ route('dashboard') }}" class="py-3 font-display uppercase tracking-wider">Кабинет</a>
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.cars.index') }}" class="py-3 font-display uppercase tracking-wider">Админка</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="py-3 font-display uppercase tracking-wider">Профиль ({{ Auth::user()->name }})</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="py-3 font-display uppercase tracking-wider">Выйти</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="py-3 font-display uppercase tracking-wider">Войти</a>
                <a href="{{ route('register') }}" class="btn btn-dark mt-2">Регистрация</a>
            @endauth
        </nav>
    </div>
</header>
