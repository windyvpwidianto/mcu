@props([
'label' => null,
'required' => false,
'model' => null,
'placeholder' => 'Pilih bulan',
'id' => null
])

@php
// Membuat ID statis & aman berbasis nama model agar tidak berubah-ubah saat render ulang
$safeId = $id ?? 'month-picker-' . str_replace(['.', '_'], '-', $model ?? 'default');
@endphp

<fieldset {{ $attributes->merge(['class' => 'w-full fieldset']) }}>
    @if($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    <div class="w-full"
        wire:ignore
        wire:key="{{ $safeId }}">

        <div x-data="{
                fp: null,
                dateValue: @entangle($model).live,

                init() {
                    // Beri jeda microtask agar DOM benar-benar siap
                    this.$nextTick(() => {
                        if (!this.$refs.input) return;

                        if (this.fp) {
                            this.fp.destroy();
                        }

                        // Catatan: hapus kata 'new' sebelum monthSelectPlugin jika masih error
                        this.fp = flatpickr(this.$refs.input, {
                            static: true,
                            plugins: [
                                new monthSelectPlugin({
                                    disableMobile: false,
                                    shorthand: true,
                                    dateFormat: 'M-Y',
                                    altFormat: 'F Y',
                                    theme: 'light'
                                })
                            ],
                            defaultDate: this.dateValue,
                            onChange: (selectedDates, dateStr) => {
                                this.dateValue = dateStr;
                            }
                        });
                    });

                    // Pengganti x-effect yang jauh lebih aman dari race condition
                    this.$watch('dateValue', (value) => {
                        if (this.fp) {
                            if (value) {
                                this.fp.setDate(value, false);
                            } else {
                                this.fp.clear();
                            }
                        }
                    });
                }
             }">

            <input x-ref="input"
                type="text"
                readonly
                class="w-full input input-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs"
                placeholder="{{ $placeholder }}" />
        </div>
    </div>

    @if($model)
    <x-label-error :messages="$errors->get($model)" />
    @endif
</fieldset>