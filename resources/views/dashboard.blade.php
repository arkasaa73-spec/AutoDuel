@php
    $userId = Auth::id();
    $summary = \App\Models\GameRun::summaryFor($userId);
    $recent = \App\Models\GameRun::recentFor($userId);

    $totalGames = (int) $summary->sum('games');
    $bestScore = (int) $summary->max('best');
    $averageScore = $totalGames > 0 ? round($summary->sum('total') / $totalGames, 1) : 0;

    // Названия режима и уровня по значению из БД
    $modeLabel = fn ($value) => \App\Enums\GameMode::tryFrom($value)?->label() ?? $value;
    $levelLabel = fn ($value) => \App\Enums\GameLevel::tryFrom($value)?->label() ?? $value;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-semibold uppercase tracking-tight">Кабинет</h1>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-10 px-4 py-10 sm:px-6">

        {{-- Приветствие и быстрый старт --}}
        <div class="border-2 border-ink bg-white p-6 shadow-hard sm:p-8">
            <p class="eyebrow">Привет, {{ Auth::user()->name }}</p>
            <h2 class="mt-4 font-display text-4xl font-bold uppercase tracking-tight">Готов к дуэли?</h2>
            <div class="mt-6 flex flex-wrap gap-4">
                <a href="{{ url('/game/price') }}" class="btn btn-primary">Играть: цена</a>
                <a href="{{ url('/game/hp') }}" class="btn btn-ghost">Играть: мощность</a>
                <a href="{{ route('leaderboard') }}" class="btn btn-ghost">Рейтинг</a>
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.cars.index') }}" class="btn btn-dark">Админка</a>
                @endif
            </div>
        </div>

        @if ($totalGames === 0)
            <div class="border-2 border-dashed border-ink p-8 text-center">
                <p class="font-display text-2xl uppercase">Пока нет сыгранных партий</p>
                <p class="mt-2 text-ink-soft">Сыграй первую партию, и здесь появится твоя статистика.</p>
            </div>
        @else
            {{-- Общие цифры --}}
            <dl class="grid grid-cols-1 divide-y divide-paper/15 bg-ink text-paper sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                <div class="px-6 py-5">
                    <dt class="font-display text-sm uppercase tracking-widest text-paper/60">Партий</dt>
                    <dd class="mt-1 font-display text-5xl font-bold text-signal">{{ $totalGames }}</dd>
                </div>
                <div class="px-6 py-5">
                    <dt class="font-display text-sm uppercase tracking-widest text-paper/60">Лучший счёт</dt>
                    <dd class="mt-1 font-display text-5xl font-bold text-signal">{{ $bestScore }}</dd>
                </div>
                <div class="px-6 py-5">
                    <dt class="font-display text-sm uppercase tracking-widest text-paper/60">Средний счёт</dt>
                    <dd class="mt-1 font-display text-5xl font-bold text-signal">{{ $averageScore }}</dd>
                </div>
            </dl>

            {{-- По режимам и уровням --}}
            <section>
                <p class="eyebrow">Подробно</p>
                <h2 class="mt-3 font-display text-3xl font-bold uppercase tracking-tight">По режимам и уровням</h2>

                <div class="mt-6 overflow-x-auto border-2 border-ink bg-white">
                    <table class="w-full min-w-[32rem] text-left text-sm">
                        <thead class="border-b-2 border-ink bg-paper-deep font-display uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Режим</th>
                                <th class="px-4 py-3">Уровень</th>
                                <th class="px-4 py-3 text-right">Партий</th>
                                <th class="px-4 py-3 text-right">Лучший</th>
                                <th class="px-4 py-3 text-right">Место</th>
                                <th class="px-4 py-3 text-right">Средний</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summary as $row)
                                <tr class="border-b border-line last:border-b-0">
                                    <td class="px-4 py-3 font-medium">{{ $modeLabel($row->mode) }}</td>
                                    <td class="px-4 py-3">{{ $levelLabel($row->difficulty) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $row->games }}</td>
                                    <td class="px-4 py-3 text-right font-display text-lg font-semibold">{{ $row->best }}</td>
                                    @php
                                        // Место в рейтинге за всё время для этого режима и уровня
                                        $place = \App\Models\GameRun::placeOf(
                                            $userId,
                                            \App\Enums\GameMode::from($row->mode),
                                            \App\Enums\GameLevel::from($row->difficulty),
                                            false
                                        );
                                    @endphp
                                    <td class="px-4 py-3 text-right">{{ $place ? '#' . $place['place'] : '-' }}</td>
                                    <td class="px-4 py-3 text-right">{{ $row->average }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Последние партии --}}
            <section>
                <p class="eyebrow">История</p>
                <h2 class="mt-3 font-display text-3xl font-bold uppercase tracking-tight">Последние партии</h2>

                <div class="mt-6 overflow-x-auto border-2 border-ink bg-white">
                    <table class="w-full min-w-[32rem] text-left text-sm">
                        <thead class="border-b-2 border-ink bg-paper-deep font-display uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Дата</th>
                                <th class="px-4 py-3">Режим</th>
                                <th class="px-4 py-3">Уровень</th>
                                <th class="px-4 py-3 text-right">Счёт</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $run)
                                <tr class="border-b border-line last:border-b-0">
                                    <td class="px-4 py-3 text-ink-soft">{{ $run->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $modeLabel($run->mode) }}</td>
                                    <td class="px-4 py-3">{{ $levelLabel($run->difficulty) }}</td>
                                    <td class="px-4 py-3 text-right font-display text-lg font-semibold">{{ $run->score }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
