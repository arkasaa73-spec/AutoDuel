@php
    $field = 'w-full rounded-none border-2 border-ink bg-white px-3 py-2.5 text-ink focus:border-ink focus:ring-4 focus:ring-signal/60';
    // Ссылка на сортировку с сохранением текущих фильтров
    $sortUrl = route('admin.runs.index', array_filter([
        'mode' => $mode?->value,
        'level' => $level?->value,
        'q' => $search,
        'sort' => $sort === 'score' ? null : 'score',
    ]));
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-semibold uppercase tracking-tight">Админка</h1>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6">
        <div>
            @include('admin._tabs', ['active' => 'runs'])
        </div>

        {{-- Фильтры --}}
        <form method="GET" action="{{ route('admin.runs.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.4fr_auto_auto]">
            <input type="hidden" name="sort" value="{{ $sort === 'score' ? 'score' : '' }}">

            <select name="mode" class="{{ $field }}" aria-label="Режим">
                <option value="">Все режимы</option>
                @foreach (\App\Enums\GameMode::cases() as $item)
                    <option value="{{ $item->value }}" @selected($mode === $item)>{{ $item->label() }}</option>
                @endforeach
            </select>

            <select name="level" class="{{ $field }}" aria-label="Уровень">
                <option value="">Все уровни</option>
                @foreach (\App\Enums\GameLevel::cases() as $item)
                    <option value="{{ $item->value }}" @selected($level === $item)>{{ $item->label() }}</option>
                @endforeach
            </select>

            <input type="search" name="q" value="{{ $search }}" placeholder="Имя или e-mail игрока" maxlength="50" class="{{ $field }}">

            <button type="submit" class="btn btn-dark !py-2.5">Применить</button>
            <a href="{{ route('admin.runs.index') }}" class="btn btn-ghost !py-2.5">Сбросить</a>
        </form>

        <div class="overflow-x-auto border-2 border-ink bg-white">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead class="border-b-2 border-ink bg-paper-deep font-display uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-3">Дата</th>
                        <th class="px-3 py-3">Игрок</th>
                        <th class="px-3 py-3">Режим</th>
                        <th class="px-3 py-3">Уровень</th>
                        <th class="px-3 py-3 text-right">
                            <a href="{{ $sortUrl }}" class="underline decoration-2 underline-offset-4 hover:bg-signal" title="Сортировка по счёту">
                                Счёт {{ $sort === 'score' ? '↓' : '' }}
                            </a>
                        </th>
                        <th class="px-3 py-3 text-right">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($runs as $run)
                        <tr class="border-b border-line last:border-b-0">
                            <td class="whitespace-nowrap px-3 py-2 text-ink-soft">{{ $run->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-3 py-2">
                                {{ $run->user?->name ?? 'удалён' }}
                                <span class="block text-xs text-ink-muted">{{ $run->user?->email }}</span>
                            </td>
                            <td class="px-3 py-2">{{ \App\Enums\GameMode::tryFrom($run->mode)?->label() ?? $run->mode }}</td>
                            <td class="px-3 py-2">{{ \App\Enums\GameLevel::tryFrom($run->difficulty)?->label() ?? $run->difficulty }}</td>
                            <td class="px-3 py-2 text-right font-display text-lg font-semibold">{{ $run->score }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right">
                                <form method="POST" action="{{ route('admin.runs.destroy', $run) }}" class="inline"
                                      data-confirm="Удалить результат {{ $run->score }} игрока {{ $run->user?->name }}?" onsubmit="return confirm(this.dataset.confirm)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-brake underline decoration-2 underline-offset-4 hover:bg-brake hover:text-white">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-ink-soft">Партий по этим фильтрам нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $runs->links('pagination.autoduel') }}
    </div>
</x-app-layout>
