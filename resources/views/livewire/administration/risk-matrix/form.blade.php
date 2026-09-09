<div>
    @if($likelihood_id && $risk_consequence_id)
    <div class="max-w-xl p-4 mt-6 bg-white border rounded-md shadow">
        <h3 class="mb-2 font-bold">Edit Cell: L{{ $likelihood_id }} x C{{ $risk_consequence_id }}</h3>

        <div class="space-y-3">
            <div>
                <label>Severity</label>
                <select wire:model="severity" class="w-full p-2 border rounded">
                    <option value="">Select</option>
                    <option>Rendah</option>
                    <option>Sedang</option>
                    <option>Tinggi</option>
                    <option>Ekstrem</option>
                </select>
            </div>
            <div>
                <label>Description</label>
                <textarea wire:model="description" class="w-full p-2 border rounded"></textarea>
            </div>
            <div>
                <label>Action</label>
                <textarea wire:model="action" class="w-full p-2 border rounded"></textarea>
            </div>
            <button wire:click="save" class="px-4 py-2 text-white bg-blue-500 rounded">Save</button>
        </div>
    </div>
    @endif
</div>
