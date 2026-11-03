@php
    $tab = 'border-2 border-ink px-4 py-2 font-display text-sm font-semibold uppercase tracking-wider transition';
@endphp

<div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.cars.index') }}" class="{{ $tab }} {{ $active === 'cars' ? 'bg-ink text-paper' : 'bg-white hover:bg-signal' }}">Машины</a>
    <a href="{{ route('admin.runs.index') }}" class="{{ $tab }} {{ $active === 'runs' ? 'bg-ink text-paper' : 'bg-white hover:bg-signal' }}">Результаты</a>
</div>

@if (session('status'))
    <div role="status" class="mt-6 border-2 border-ink bg-signal px-4 py-3 font-medium">{{ session('status') }}</div>
@endif
