<!-- resources/views/livewire/hazard-status-updater.blade.php -->
<div>
    <div>Status: {{ $hazard->status }}</div>
    @foreach (['in_progress', 'pending', 'closed', 'cancelled'] as $status)
        <button wire:click="update_Status('{{ $status }}')" class="btn btn-sm m-1">
            {{ ucfirst(str_replace('_', ' ', $status)) }}
        </button>
    @endforeach
</div>
