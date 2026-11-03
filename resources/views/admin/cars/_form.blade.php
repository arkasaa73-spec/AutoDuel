@php
    $fields = [
        ['brand', 'Марка', 'text'],
        ['model', 'Модель', 'text'],
        ['year', 'Год выпуска', 'number'],
        ['hp', 'Мощность, л.с.', 'number'],
        ['price', 'Цена, USD (базовая комплектация)', 'number'],
    ];
    $label = 'block font-display text-sm uppercase tracking-wider';
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    @foreach ($fields as [$name, $title, $type])
        <div>
            <label for="{{ $name }}" class="{{ $label }}">{{ $title }}</label>
            <x-text-input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" :value="old($name, $car->{$name})" class="mt-1" required />
            <x-input-error :messages="$errors->get($name)" class="mt-1" />
        </div>
    @endforeach
</div>

<div class="mt-8 border-t-2 border-ink pt-6">
    <p class="font-display text-lg font-semibold uppercase tracking-wide">Фото</p>

    @if ($car->image_path)
        <div class="mt-3 flex flex-wrap items-center gap-4">
            <img src="{{ $car->image_url }}" alt="Текущее фото" class="h-24 w-36 border-2 border-ink object-cover">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remove_image" value="1" class="rounded-none border-2 border-ink">
                Удалить текущее фото
            </label>
        </div>
    @endif

    <div class="mt-4">
        <label for="image" class="{{ $label }}">{{ $car->image_path ? 'Заменить фото' : 'Загрузить фото' }}</label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
               class="mt-1 block w-full border-2 border-ink bg-white p-2 text-sm file:mr-4 file:border-0 file:bg-ink file:px-4 file:py-2 file:font-display file:uppercase file:tracking-wider file:text-paper hover:file:bg-signal hover:file:text-ink">
        <p class="mt-1 text-xs text-ink-muted">jpg, png или webp, до 2 МБ. Лучше горизонтальный кадр спереди.</p>
        <x-input-error :messages="$errors->get('image')" class="mt-1" />
    </div>

    <div class="mt-4">
        <label for="image_credit" class="{{ $label }}">Подпись к фото (автор и лицензия)</label>
        <x-text-input id="image_credit" name="image_credit" type="text" :value="old('image_credit', $car->image_credit)" class="mt-1" maxlength="255" />
        <x-input-error :messages="$errors->get('image_credit')" class="mt-1" />
    </div>
</div>
