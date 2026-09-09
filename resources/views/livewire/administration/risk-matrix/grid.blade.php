<section class="w-full">
    <x-toast />
    @include('partials.matrix')
    <x-tabs-manajemen-resiko.layout>
        <div class="overflow-x-auto w-80">
            <table class="table table-xs">
                <thead>
                    <tr class="text-center text-[9px]">
                        <th class="border-1">Likelihooc ↓ / Consequence →</th>
                        @foreach ($consequences as $c)
                        <th class="rotate_text border-1">{{ $c->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($likelihoods as $l)
                    <tr class="text-xs text-center">

                        <td class="w-1 font-bold  border-1">{{ $l->name }}</td>
                        @foreach ($consequences as $c)
                        @php
                        $cell = App\Models\RiskMatrixCell::where('likelihood_id', $l->id)->where('risk_consequence_id', $c->id)->first() ?? null;
                        $score = $l->level * $c->level;
                        $severity = $cell?->severity ?? '';
                        $color = match($severity) {
                        'Rendah' => 'bg-emerald-500',
                        'Sedang' => 'bg-sky-500',
                        'Tinggi' => 'bg-orange-300',
                        'Ekstrem' => 'bg-rose-500',
                        default => 'bg-gray-100',
                        };
                        @endphp
                        <td wire:click="edit({{ $l->id }}, {{ $c->id }})" class="border w-1 cursor-pointer {{ $color }}">
                            <div class="text-[6px] ">{{ $severity }}
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <flux:modal name="RiskMatrix">
            <form wire:submit.prevent="updateMatrix" class='grid justify-items-stretch'>
                @csrf
                <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                    <legend class="fieldset-legend"></legend>
                    {{-- Severity --}}
                    <x-label-req>{{ __('Severity') }} </x-label-req>
                    <flux:select size="xs" wire:model.live="severity" placeholder="Choose Status...">
                        <flux:select.option value="Rendah">Rendah</flux:select.option>
                        <flux:select.option value="Sedang">Sedang</flux:select.option>
                        <flux:select.option value="Tinggi">Tinggi</flux:select.option>
                        <flux:select.option value="Ekstrem">Ekstrem</flux:select.option>
                    </flux:select>
                    <x-label-error :messages="$errors->get('severity')" />
                    {{-- Description --}}
                    <x-label-req>{{ __('Description') }} </x-label-req>
                    <x-text-area wire:model.live='description' :error="$errors->get('description')" type="text" placeholder="Description" />
                    <x-label-error :messages="$errors->get('description')" />
                    {{-- Action --}}
                    <x-label-req>{{ __('Action') }} </x-label-req>
                    <x-text-area wire:model.live='action' :error="$errors->get('action')" type="text" placeholder="action" />
                    <x-label-error :messages="$errors->get('action')" />
                </fieldset>
                <div class="modal-action">
                    <flux:button size="xs" type="submit" icon="save-icon" variant="primary">
                        save</flux:button>
                    <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    </x-tabs-manajemen-resiko.layout>
</section>
