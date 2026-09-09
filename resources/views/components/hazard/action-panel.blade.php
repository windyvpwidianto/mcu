@props([
    'hazard',
    'ermList' => [],
    'effectiveRole' => 'moderator',
    'proceedTo' => null,
    'assignTo1' => null,
    'assignTo2' => null
])

<div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end mb-6">
    {{-- Status --}}
    <div class="md:col-span-3">
        <label class="font-semibold block">Status</label>
        <div class="italic text-green-600 capitalize">{{ $hazard->status->value }}</div>
    </div>

    {{-- Proceed To --}}
    <div class="md:col-span-3">
        <label class="font-semibold block">Proceed To {{ ucfirst($effectiveRole) }}</label>
        <select wire:model.live="proceedTo" class="w-full border rounded px-2 py-1 mt-1">
            <option value="">-- Pilih Aksi --</option>

            @if ($effectiveRole === 'moderator')
                @if ($hazard->status->value === 'submitted')
                    <option value="in_progress">Assigned to ERM</option>
                @elseif ($hazard->status->value === 'pending')
                    <option value="in_progress">Kirim Ulang ke ERM</option>
                    <option value="closed">Tutup Laporan</option>
                @endif
                @if (in_array($hazard->status->value, ['closed', 'cancelled']))
                    <option value="in_progress">Kirim Ulang ke ERM</option>
                @endif
            @endif

            @if ($effectiveRole === 'erm' && $hazard->status->value === 'in_progress')
                <option value="pending">Kembalikan ke Moderator</option>
            @endif
        </select>
    </div>

    {{-- Assign To --}}
    @if ($proceedTo === 'in_progress')
        <div class="md:col-span-2">
            <label class="font-semibold block">Assign To</label>
            <select wire:model="assignTo1" class="w-full border rounded px-2 py-1 mt-1">
                <option value="">Select an option</option>
                @foreach ($ermList as $erm)
                    <option value="{{ $erm->id }}">{{ $erm->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="font-semibold block">Also Assign To</label>
            <select wire:model="assignTo2" class="w-full border rounded px-2 py-1 mt-1">
                <option value="">Select an option</option>
                @foreach ($ermList as $erm)
                    <option value="{{ $erm->id }}">{{ $erm->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Simpan Button --}}
    <div class="md:col-span-2">
        <button
            wire:click="processAction({{ $hazard->id }}, '{{ $proceedTo }}')"
            class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mt-2 md:mt-0"
        >
            Simpan
        </button>
    </div>
</div>
