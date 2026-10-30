@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-none border-2 border-ink bg-white px-3 py-2.5 text-ink placeholder:text-ink-muted focus:border-ink focus:ring-4 focus:ring-signal/60 disabled:bg-paper-deep']) }}>
