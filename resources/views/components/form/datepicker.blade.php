@props([
    'label' => null,
    'placeholder' => 'Pilih Tanggal...',
    'model' => null,
    'size' => 'input-xs',
    'dateFormat' => 'd F Y', // Format yang tampil ke user
])

<fieldset class="fieldset ">
    <div
        class="{{ $errors->has($model) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500 rounded' : 'ring-base-300 focus:ring-base-300 focus:border-base-300 rounded' }}">


        <label {{ $attributes->merge(['class' => 'floating-label w-full']) }} wire:ignore x-data="{
            reportDate: @entangle($model),
            fp: null,
            init() {
                this.fp = flatpickr(this.$refs.tanggalInput, {
                    disableMobile: true,
                    altInput: true,
                    altFormat: '{{ $dateFormat }}',
                    dateFormat: 'Y-m-d',
                    defaultDate: this.reportDate,
                    onChange: (selectedDates, dateStr) => {
                        this.reportDate = dateStr;
                    }
                });

                this.$watch('reportDate', (newVal) => {
                    this.fp.setDate(newVal, false);
                });
            }
        }">

            <input x-ref="tanggalInput" type="text" placeholder="{{ $placeholder ?: $label }}" readonly
                {{ $model ? "wire:model.live=$model" : '' }} {{ $attributes->whereDoesntStartWith('class') }}
                class="input input-bordered {{ $size }} w-full focus-within:outline-none focus-within:border-info focus-within:ring-0
            border-gray-300 rounded
            {{ $errors->has($model) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />

            @if ($label)
                <span>{{ $label }}</span>
            @endif
        </label>
    </div>
    {{-- Tampilkan Error --}}
    @if ($model)
        @error($model)
            <x-label-error :messages="$errors->get($model)" />
        @enderror
    @endif
</fieldset>
