<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-semibold uppercase tracking-tight">Админка: новая машина</h1>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <form method="POST" action="{{ route('admin.cars.store') }}" enctype="multipart/form-data"
              class="border-2 border-ink bg-white p-6 shadow-hard sm:p-8">
            @csrf
            @include('admin.cars._form')

            <div class="mt-8 flex flex-wrap gap-4">
                <x-primary-button>Добавить</x-primary-button>
                <a href="{{ route('admin.cars.index') }}" class="btn btn-ghost">Отмена</a>
            </div>
        </form>
    </div>
</x-app-layout>
