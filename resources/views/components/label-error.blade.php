@props(['messages'])

@if ($messages)
<ul {{ $attributes->merge(['class' => 'text-[10px] -mt-1 text-rose-500 space-y-1']) }}>
    @foreach ((array) $messages as $message)
    <li class="flex ">
        {{-- Ikon Warning --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L4.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>{{ __($message) }}</span>
    </li>
    @endforeach
</ul>
@endif