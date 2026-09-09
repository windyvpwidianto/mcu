{{-- resources/views/components/flux/select-searchable.blade.php --}}
<div wire:ignore>
    <select id="{{ $id }}" class="select select-xs select-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden">
        {{ $slot }}
    </select>
</div>



