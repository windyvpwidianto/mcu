{{-- File: resources/views/components/hazard/status-panel.blade.php --}}
<div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-4 md:space-y-0 w-full">
    {{-- Status --}}
    <div class="flex-1">
        <label class="font-semibold block mb-1">Status</label>
        <div class="italic text-green-600 capitalize">{{ $hazard->status->value }}</div>
    </div>

    {{-- Proceed To --}}
    <div class="flex-1">
        <label class="font-semibold block mb-1">Proceed To</label>
        <select wire:model.live="proceedTo" class="w-full border rounded px-2 py-1">
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

    {{-- Assign To (ERM 1) --}}
    @if ($proceedTo === 'in_progress')
        <div class="flex-1">
            <label class="font-semibold block mb-1">Assign To</label>
            <select wire:model="assignTo1" class="w-full border rounded px-2 py-1">
                <option value="">Select an option</option>
                @foreach ($ermList as $erm)
                    <option value="{{ $erm->id }}">{{ $erm->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Also Assign To (ERM 2) --}}
        <div class="flex-1">
            <label class="font-semibold block mb-1">Also Assign To</label>
            <select wire:model="assignTo2" class="w-full border rounded px-2 py-1">
                <option value="">Select an option</option>
                @foreach ($ermList as $erm)
                    <option value="{{ $erm->id }}">{{ $erm->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Tombol Simpan --}}
    <div class="mt-4 md:mt-0">
        <button wire:click="processAction({{ $hazard->id }}, '{{ $proceedTo }}')"
            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            Simpan
        </button>
    </div>
</div>
