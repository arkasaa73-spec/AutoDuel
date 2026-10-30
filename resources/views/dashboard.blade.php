<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-semibold uppercase tracking-tight">Кабинет</h1>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <div class="border-2 border-ink bg-white p-6 shadow-hard sm:p-8">
            <p class="eyebrow">Привет, {{ Auth::user()->name }}</p>
            <h2 class="mt-4 font-display text-4xl font-bold uppercase tracking-tight">Готов к дуэли?</h2>
            <p class="mt-3 max-w-xl text-ink-soft">Выбери режим и начни серию. Статистика и лучшие результаты появятся здесь, когда запустим игру.</p>

            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ url('/game/price') }}" class="btn btn-primary">Играть: цена</a>
                <a href="{{ url('/game/hp') }}" class="btn btn-ghost">Играть: мощность</a>
            </div>
        </div>
    </div>
</x-app-layout>
