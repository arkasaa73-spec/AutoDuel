<x-app-layout>
    <div id="game"
         data-start-url="{{ route('game.start', $mode->value) }}"
         data-answer-url="{{ route('game.answer', $mode->value) }}"
         data-value-label="{{ $mode->label() }}"
         class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Режим: {{ $mode->label() }}</p>
                <h1 class="mt-3 font-display text-3xl font-bold uppercase leading-tight tracking-tight sm:text-5xl">{{ $mode->question() }}</h1>
            </div>
            <div class="border-2 border-ink bg-ink px-5 py-2 text-paper shadow-hard-sm">
                <span class="font-display text-xs uppercase tracking-widest text-paper/60">Счёт</span>
                <span id="score" class="ml-2 font-display text-4xl font-bold text-signal">0</span>
            </div>
        </div>

        <p id="hint" class="mt-4 min-h-[1.75rem] text-ink-soft" role="status" aria-live="polite">Загружаем машины...</p>

        <div class="mt-4 grid items-stretch gap-4 md:grid-cols-[1fr_auto_1fr] md:gap-6">
            <div id="slot-left"></div>
            <div class="grid place-items-center">
                <span class="grid h-12 w-12 place-items-center rounded-full border-2 border-ink bg-signal font-display font-bold" aria-hidden="true">VS</span>
            </div>
            <div id="slot-right"></div>
        </div>

        {{-- Конец партии --}}
        <div id="over" hidden class="mt-8 border-2 border-ink bg-white p-6 shadow-hard sm:p-8">
            <p class="eyebrow">Партия окончена</p>
            <p class="mt-4 font-display text-5xl font-bold uppercase tracking-tight">
                Счёт: <span id="final-score" class="bg-signal px-2">0</span>
            </p>
            <p id="note-saved" hidden class="mt-4 text-ink-soft">Результат сохранён в твоей истории партий.</p>
            <p id="note-guest" hidden class="mt-4 text-ink-soft">
                Гость играет без сохранения.
                <a href="{{ route('register') }}" class="font-semibold text-ink underline decoration-2 underline-offset-4 hover:bg-signal">Создай аккаунт</a>,
                чтобы результаты попадали в рейтинг.
            </p>
            <div class="mt-6 flex flex-wrap gap-4">
                <button type="button" id="restart" class="btn btn-primary">Сыграть ещё раз</button>
                <a href="{{ url('/') }}#modes" class="btn btn-ghost">Другой режим</a>
            </div>
        </div>
    </div>

    {{-- Шаблон карточки машины: JS клонирует его и заполняет по data-slot --}}
    <template id="card-template">
        <button type="button" class="duel-card">
            <div class="aspect-[4/3] w-full overflow-hidden border-b-2 border-ink bg-paper-deep">
                <img data-slot="image" alt="" class="h-full w-full object-cover">
            </div>
            <div class="flex flex-1 flex-col p-4 sm:p-5">
                <p data-slot="meta" class="text-xs font-medium uppercase tracking-widest text-ink-muted"></p>
                <p data-slot="model" class="mt-1 font-display text-2xl font-semibold uppercase leading-tight sm:text-3xl"></p>
                <p data-slot="credit" class="mt-1 text-[11px] text-ink-muted"></p>
                <div class="mt-auto pt-4">
                    <p data-slot="label" class="text-xs uppercase tracking-widest text-ink-muted"></p>
                    <p data-slot="value" class="duel-value font-display text-3xl font-bold"></p>
                    <p data-slot="verdict" class="mt-1 font-display text-sm font-semibold uppercase tracking-widest"></p>
                </div>
            </div>
        </button>
    </template>

    {{-- Скрипт лежит в public/js, сборка Vite для него не нужна --}}
    <script src="{{ asset('js/game.js') }}?v={{ filemtime(public_path('js/game.js')) }}" defer></script>
</x-app-layout>
