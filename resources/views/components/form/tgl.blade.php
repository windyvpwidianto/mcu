@props([
'label' => null,
'placeholder' => 'Pilih Tanggal...',
'model' => null,
'size' => 'input-xs',
'required' => false,
'disabled' => false,
'dateFormat' => 'd F Y',
'format' => 'Y-m-d' // Perbaikan typo f -> d
])

<fieldset class="w-full fieldset">
    @if ($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    <div wire:ignore x-data="{
        reportDate: @entangle($model),
        // Tambahkan state untuk mendeteksi error secara reaktif di Alpine
        hasError: {{ $errors->has($model) ? 'true' : 'false' }},
        fp: null,
        init() {
            this.fp = flatpickr(this.$refs.tanggalInput, {
                disableMobile: true,
                altInput: true,
                static: true,
                altFormat: '{{ $dateFormat }}',
                dateFormat: '{{ $format }}',
                defaultDate: this.reportDate,
                // Tambahkan class ke input yang dibuat Flatpickr jika ada error
                altInputClass: 'input input-bordered w-full focus:outline-none {{ $size }} ' + (this.hasError ? 'border-rose-500 ring-1 ring-rose-500' : 'border-gray-300'),
                onChange: (selectedDates, dateStr) => {
                    this.reportDate = dateStr;
                }
            });

            this.$watch('reportDate', (newVal) => {
                if (this.fp && newVal !== this.fp.currentSelectedDateString) {
                    this.fp.setDate(newVal, false);
                }
            });
        }
    }">
        <input
            x-ref="tanggalInput"
            type="text"
            readonly
            {{ $disabled ? 'disabled' : '' }}
            placeholder="{{ $placeholder ?: $label }}"
            {{ $attributes->merge([
            'class' => "input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 $size border-gray-300 " .
                       ($errors->has($model) ? 'border-rose-500 ring-1 ring-rose-500' : 'border-gray-300')
        ]) }} />
    </div>

    @if($model)
    @error($model)
    <x-label-error :messages="$errors->get($model)" />
    @enderror
    @endif
</fieldset>