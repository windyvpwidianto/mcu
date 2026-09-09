@props([
    'name',
    'label' => null,
    'required' => false,
])

<fieldset class="fieldset relative">
    @if($label)
        <x-form.label :label="$label" :required="$required" />
    @endif

    <div class="relative"
        wire:ignore
        x-data="{
            fp: null,
            initFlatpickr() {
                if (this.fp) this.fp.destroy();
                this.fp = flatpickr(this.$refs.tanggalInput, {
                    disableMobile: true,
                    enableTime: true,
                    dateFormat: 'd-m-Y H:i',
                    clickOpens: true,
                    appendTo: this.$refs.wrapper,
                    onChange: (selectedDates, dateStr) => {
                        $wire.set('{{ $name }}', dateStr);
                    }
                });
            }
        }"
        x-ref="wrapper"
        x-init="
            initFlatpickr();
            Livewire.hook('message.processed', () => {
                initFlatpickr();
            });
        "
    >
        <input 
            name="{{ $name }}"
            type="text"
            x-ref="tanggalInput"
            wire:model.live="{{ $name }}"
            placeholder="Pilih Tanggal dan Waktu..."
            readonly
            class="input input-bordered cursor-pointer w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has($name) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"
        />
    </div>

    <x-label-error :messages="$errors->get($name)" />
</fieldset>
