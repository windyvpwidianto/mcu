<div class="space-y-2">
    <label class="font-semibold">Pilih ERM Tujuan (maks. 2)</label>

    <select wire:model="selectedErms" multiple class="w-full border rounded px-2 py-1 focus:ring"
            size="4">
        @foreach($ermList as $erm)
            <option value="{{ $erm->id }}">{{ $erm->name }}</option>
        @endforeach
    </select>

    @error('selectedErms')
        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
    @enderror

    <button wire:click="assign"
        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        @if(count($selectedErms) < 1 || count($selectedErms) > 2) disabled @endif
    >
        Kirim ke ERM
    </button>
</div>
