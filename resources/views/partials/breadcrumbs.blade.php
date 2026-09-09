@php
// Ambil nama route asli dari request pertama (bukan livewire.update)
$routeName = request()->route()?->getName();
// Jika bukan livewire.update & breadcrumb ada, generate
$crumbs = ($routeName && $routeName !== 'livewire.update' && Breadcrumbs::exists($routeName))
? Breadcrumbs::generate($routeName, ...array_values(request()->route()->parameters()))
: collect();
@endphp
@if ($crumbs->isNotEmpty())
<nav  class="breadcrumbs text-[10px] font-semibold hidden md:block">
    <ul>
        @foreach ($crumbs as $crumb)
        <li>
            @if ($crumb->url && !$loop->last)
            <a href="{{ $crumb->url }}" class="inline-flex items-center gap-1">
                {{-- Icon folder --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder-closed-icon lucide-folder-closed">
                    <path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z" />
                    <path d="M2 10h20" /></svg>
                {{ $crumb->title }}
            </a>
            @else
            <span class="inline-flex items-center gap-1">
                {{-- Icon file --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder-open-icon lucide-folder-open">
                    <path d="m6 14 1.5-2.9A2 2 0 0 1 9.24 10H20a2 2 0 0 1 1.94 2.5l-1.54 6a2 2 0 0 1-1.95 1.5H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3.9a2 2 0 0 1 1.69.9l.81 1.2a2 2 0 0 0 1.67.9H18a2 2 0 0 1 2 2v2" /></svg>
                {{ $crumb->title }}
            </span>
            @endif
        </li>
        @endforeach
    </ul>
</nav>
@endif
