@php
    // Временно считаем машины прямо здесь, позже вынесем в контроллер
    $carsCount = \App\Models\Car::count();
@endphp

<x-app-layout>
    {{-- HERO --}}
    <section class="border-b-2 border-ink">
        <div class="mx-auto grid max-w-6xl gap-12 px-4 py-12 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:items-center lg:py-20">
            <div>
                <p class="eyebrow">Автомобильная викторина</p>
                <h1 class="mt-5 font-display text-5xl font-bold uppercase leading-[0.95] tracking-tight sm:text-7xl">
                    У какой машины <span class="bg-signal px-2">больше?</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-ink-soft">
                    Две машины на экране и один вопрос: у какой выше цена или мощнее мотор.
                    Отвечай верно, растягивай серию и попадай в рейтинг игроков.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ url('/game/price') }}" class="btn btn-primary">Играть: цена</a>
                    <a href="{{ url('/game/hp') }}" class="btn btn-ghost">Играть: мощность</a>
                </div>

                @guest
                    <p class="mt-5 text-sm text-ink-muted">
                        Чтобы результат попал в рейтинг,
                        <a href="{{ route('register') }}" class="font-semibold text-ink underline decoration-2 underline-offset-4 hover:bg-signal">создай аккаунт</a>.
                    </p>
                @endguest
            </div>

            {{-- Демо-карточка дуэли: чисто визуальный пример, не интерактивная --}}
            <div class="border-2 border-ink bg-white shadow-hard" aria-hidden="true">
                <div class="flex items-center justify-between bg-ink px-4 py-2.5">
                    <span class="font-display text-sm uppercase tracking-widest text-signal">Что дороже?</span>
                    <span class="font-display text-sm uppercase tracking-widest text-paper/60">Пример дуэли</span>
                </div>

                <div class="relative grid grid-cols-2">
                    <div class="border-r-2 border-ink p-4 sm:p-6">
                        <div class="hatch aspect-[4/3] border border-line"></div>
                        <p class="mt-4 text-xs font-medium uppercase tracking-widest text-ink-muted">Honda · 2023</p>
                        <p class="font-display text-2xl font-semibold uppercase sm:text-3xl">Civic</p>
                        <p class="mt-3 font-display text-2xl text-ink-muted">???</p>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="hatch aspect-[4/3] border border-line"></div>
                        <p class="mt-4 text-xs font-medium uppercase tracking-widest text-ink-muted">Porsche · 2023</p>
                        <p class="font-display text-2xl font-semibold uppercase sm:text-3xl">911 GT3</p>
                        <p class="mt-3 font-display text-2xl text-ink-muted">???</p>
                    </div>

                    <div class="absolute left-1/2 top-1/2 grid h-14 w-14 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-2 border-ink bg-signal font-display text-lg font-bold">
                        VS
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ЦИФРЫ --}}
    <section class="bg-ink text-paper">
        <dl class="mx-auto grid max-w-6xl grid-cols-1 divide-y divide-paper/15 px-4 sm:grid-cols-2 sm:divide-x sm:divide-y-0 sm:px-6">
            <div class="py-6 sm:pr-8">
                <dt class="font-display text-sm uppercase tracking-widest text-paper/60">Машин в базе</dt>
                <dd class="mt-1 font-display text-5xl font-bold text-signal">{{ $carsCount }}</dd>
            </div>
            <div class="py-6 sm:pl-8">
                <dt class="font-display text-sm uppercase tracking-widest text-paper/60">Режима игры</dt>
                <dd class="mt-1 font-display text-5xl font-bold text-signal">2</dd>
            </div>
        </dl>
    </section>

    {{-- РЕЖИМЫ --}}
    <section id="modes" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6">
        <p class="eyebrow">Режимы</p>
        <h2 class="mt-4 font-display text-4xl font-bold uppercase tracking-tight sm:text-5xl">Выбери дуэль</h2>

        <div class="mt-10 grid gap-6 md:grid-cols-2">
            <a href="{{ url('/game/price') }}"
               class="group block border-2 border-ink bg-asphalt p-7 text-paper shadow-hard transition hover:-translate-y-1 sm:p-9">
                <p class="font-display text-sm uppercase tracking-widest text-signal">Режим 01</p>
                <h3 class="mt-3 font-display text-5xl font-bold uppercase">Цена</h3>
                <p class="mt-3 max-w-sm text-paper/75">Какая машина дороже? Ориентир: базовая комплектация, доллары США.</p>
                <span class="mt-8 inline-flex items-center gap-2 font-display uppercase tracking-wider text-signal transition-all group-hover:gap-4">
                    Начать
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </span>
            </a>

            <a href="{{ url('/game/hp') }}"
               class="group block border-2 border-ink bg-signal p-7 text-ink shadow-hard transition hover:-translate-y-1 sm:p-9">
                <p class="font-display text-sm uppercase tracking-widest">Режим 02</p>
                <h3 class="mt-3 font-display text-5xl font-bold uppercase">Мощность</h3>
                <p class="mt-3 max-w-sm text-ink-soft">У какой машины больше лошадиных сил? Чем ближе значения, тем сложнее выбор.</p>
                <span class="mt-8 inline-flex items-center gap-2 font-display uppercase tracking-wider transition-all group-hover:gap-4">
                    Начать
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </span>
            </a>
        </div>
    </section>

    {{-- КАК ЭТО РАБОТАЕТ --}}
    <section class="border-t-2 border-ink bg-paper-deep">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <p class="eyebrow">Правила</p>
            <h2 class="mt-4 font-display text-4xl font-bold uppercase tracking-tight sm:text-5xl">Как это работает</h2>

            <ol class="mt-10 grid gap-8 md:grid-cols-3">
                <li class="border-t-4 border-ink pt-4">
                    <span class="inline-block bg-signal px-2 font-display text-3xl font-bold">01</span>
                    <h3 class="mt-4 font-display text-xl font-semibold uppercase">Две машины</h3>
                    <p class="mt-2 text-ink-soft">На экране появляются фото двух автомобилей и вопрос режима.</p>
                </li>
                <li class="border-t-4 border-ink pt-4">
                    <span class="inline-block bg-signal px-2 font-display text-3xl font-bold">02</span>
                    <h3 class="mt-4 font-display text-xl font-semibold uppercase">Твой выбор</h3>
                    <p class="mt-2 text-ink-soft">Нажимаешь на ту машину, у которой, по-твоему, значение больше.</p>
                </li>
                <li class="border-t-4 border-ink pt-4">
                    <span class="inline-block bg-signal px-2 font-display text-3xl font-bold">03</span>
                    <h3 class="mt-4 font-display text-xl font-semibold uppercase">Серия и рейтинг</h3>
                    <p class="mt-2 text-ink-soft">Верный ответ продолжает серию, а лучший результат попадает в рейтинг игроков.</p>
                </li>
            </ol>
        </div>
    </section>
</x-app-layout>
