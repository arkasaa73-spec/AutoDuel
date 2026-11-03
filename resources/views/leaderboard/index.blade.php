@php
    // Ссылка на эту же страницу с изменённым фильтром
    $url = fn (array $change) => route('leaderboard', array_merge(
        ['mode' => $mode->value, 'level' => $level->value, 'period' => $period],
        $change
    ));

    $periods = ['all' => 'За всё время', 'week' => 'За неделю'];
    $chip = 'border-2 border-ink px-4 py-2 font-display text-sm font-semibold uppercase tracking-wider transition';
    $on = 'bg-ink text-paper';
    $off = 'bg-white hover:bg-signal';
@endphp

<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14">
        <p class="eyebrow">Лучшие игроки</p>
        <h1 class="mt-3 font-display text-4xl font-bold uppercase tracking-tight sm:text-6xl">Рейтинг</h1>
        <p class="mt-3 max-w-xl text-ink-soft">Топ-10 по лучшему счёту. У каждого режима и уровня свой рейтинг, один игрок встречается один раз.</p>

        {{-- Фильтры --}}
        <div class="mt-8 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="w-20 font-display text-xs uppercase tracking-widest text-ink-muted">Режим</span>
                @foreach (\App\Enums\GameMode::cases() as $item)
                    <a href="{{ $url(['mode' => $item->value]) }}" class="{{ $chip }} {{ $item === $mode ? $on : $off }}">{{ $item->label() }}</a>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="w-20 font-display text-xs uppercase tracking-widest text-ink-muted">Уровень</span>
                @foreach (\App\Enums\GameLevel::cases() as $item)
                    <a href="{{ $url(['level' => $item->value]) }}" class="{{ $chip }} {{ $item === $level ? $on : $off }}">{{ $item->label() }}</a>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="w-20 font-display text-xs uppercase tracking-widest text-ink-muted">Период</span>
                @foreach ($periods as $value => $label)
                    <a href="{{ $url(['period' => $value]) }}" class="{{ $chip }} {{ $value === $period ? $on : $off }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        {{-- Таблица --}}
        <div class="mt-8 border-2 border-ink bg-white shadow-hard">
            <div class="flex items-center justify-between bg-ink px-4 py-3 text-paper">
                <span class="font-display text-sm uppercase tracking-widest text-signal">{{ $mode->label() }} · {{ $level->label() }}</span>
                <span class="font-display text-sm uppercase tracking-widest text-paper/60">{{ $periods[$period] }}</span>
            </div>

            @forelse ($rows as $row)
                @php($mine = auth()->id() === $row['user_id'])
                <div class="flex items-center gap-4 border-b border-line px-4 py-3 last:border-b-0 {{ $mine ? 'bg-signal/40' : '' }}">
                    <span class="grid h-10 w-10 shrink-0 place-items-center border-2 border-ink font-display text-lg font-bold {{ $row['rank'] <= 3 ? 'bg-signal' : 'bg-white' }}">{{ $row['rank'] }}</span>
                    <span class="min-w-0 flex-1 truncate font-medium">
                        {{ $row['name'] }}
                        @if ($mine)<span class="ml-2 text-xs uppercase tracking-widest text-ink-muted">это ты</span>@endif
                    </span>
                    <span class="font-display text-3xl font-bold">{{ $row['best'] }}</span>
                </div>
            @empty
                <div class="px-6 py-10 text-center">
                    <p class="font-display text-2xl uppercase">Здесь пока пусто</p>
                    <p class="mt-2 text-ink-soft">Никто ещё не набрал очков на этом уровне. Стань первым.</p>
                </div>
            @endforelse

            {{-- Место текущего игрока, если он вне топ-10 --}}
            @if ($me && ! $meInTop)
                <div class="flex items-center gap-4 border-t-2 border-ink bg-signal/40 px-4 py-3">
                    <span class="grid h-10 min-w-10 shrink-0 place-items-center border-2 border-ink bg-white px-1 font-display text-lg font-bold">{{ $me['place'] }}</span>
                    <span class="flex-1 font-medium">Твоё место</span>
                    <span class="font-display text-3xl font-bold">{{ $me['best'] }}</span>
                </div>
            @endif
        </div>

        @guest
            <p class="mt-6 text-ink-soft">
                Хочешь попасть в рейтинг?
                <a href="{{ route('register') }}" class="font-semibold text-ink underline decoration-2 underline-offset-4 hover:bg-signal">Создай аккаунт</a>
                и сыграй партию.
            </p>
        @endguest

        <div class="mt-8 flex flex-wrap gap-4">
            <a href="{{ url('/game/' . $mode->value) }}" class="btn btn-primary">Играть: {{ mb_strtolower($mode->label()) }}</a>
            <a href="{{ url('/') }}" class="btn btn-ghost">На главную</a>
        </div>
    </div>
</x-app-layout>
