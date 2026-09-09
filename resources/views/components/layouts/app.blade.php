<x-layouts.app.sidebar :title="Route::currentRouteName() ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
