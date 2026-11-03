<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-semibold uppercase tracking-tight">Админка</h1>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6">
        <div>
            @include('admin._tabs', ['active' => 'cars'])
        </div>

        <div class="flex flex-wrap items-end justify-between gap-4">
            <form method="GET" action="{{ route('admin.cars.index') }}" class="flex flex-wrap gap-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="Марка или модель" maxlength="50"
                       class="w-56 rounded-none border-2 border-ink bg-white px-3 py-2.5 text-ink placeholder:text-ink-muted focus:border-ink focus:ring-4 focus:ring-signal/60">
                <button type="submit" class="btn btn-dark !px-4 !py-2.5">Найти</button>
                @if ($search !== '')
                    <a href="{{ route('admin.cars.index') }}" class="btn btn-ghost !px-4 !py-2.5">Сбросить</a>
                @endif
            </form>
            <a href="{{ route('admin.cars.create') }}" class="btn btn-primary">Добавить машину</a>
        </div>

        <div class="overflow-x-auto border-2 border-ink bg-white">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead class="border-b-2 border-ink bg-paper-deep font-display uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-3">Фото</th>
                        <th class="px-3 py-3">Машина</th>
                        <th class="px-3 py-3">Год</th>
                        <th class="px-3 py-3 text-right">Л.с.</th>
                        <th class="px-3 py-3 text-right">Цена</th>
                        <th class="px-3 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cars as $car)
                        <tr class="border-b border-line last:border-b-0">
                            <td class="px-3 py-2">
                                <img src="{{ $car->image_url }}" alt="" loading="lazy" class="h-12 w-20 border border-line object-cover">
                            </td>
                            <td class="px-3 py-2">{{ $car->brand }} <span class="font-semibold">{{ $car->model }}</span></td>
                            <td class="px-3 py-2">{{ $car->year }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($car->hp, 0, '', ' ') }}</td>
                            <td class="px-3 py-2 text-right">${{ number_format($car->price, 0, '', ' ') }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right">
                                <a href="{{ route('admin.cars.edit', $car) }}" class="font-semibold underline decoration-2 underline-offset-4 hover:bg-signal">Изменить</a>
                                <form method="POST" action="{{ route('admin.cars.destroy', $car) }}" class="ml-3 inline"
                                      data-confirm="Удалить {{ $car->title }} вместе с фото?" onsubmit="return confirm(this.dataset.confirm)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-brake underline decoration-2 underline-offset-4 hover:bg-brake hover:text-white">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-ink-soft">Ничего не найдено.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $cars->links('pagination.autoduel') }}
    </div>
</x-app-layout>
