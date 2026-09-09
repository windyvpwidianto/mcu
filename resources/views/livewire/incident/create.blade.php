<section class="w-full">
    <x-toast />

    {{-- Breadcrumb --}}
    <div class="flex justify-start mb-2" wire:ignore>
        {{ Breadcrumbs::render('incident-create') }}
    </div>

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-2 md:flex-row md:items-end">
        <div>
            <flux:heading level="1" class="mb-1 capitalize">
                {{ __('Buat Laporan Insiden') }}
            </flux:heading>
            <flux:subheading size="sm" class="text-accent">
                {{ __('Laporkan insiden dengan detail untuk penanganan yang tepat.') }}
            </flux:subheading>
        </div>
        <div class="flex gap-2">
            <span class="badge badge-info font-bold p-4 shadow-sm italic uppercase tracking-widest text-[10px]">Mode Input </span>
        </div>
    </div>

    <x-incident.layout>
        {{-- Iterasi hanya sampai Bagian 2 --}}
        @for ($i = 1; $i <= 2; $i++)
            @php
            $hasErrorInStep=$errors->any() && $this->isFieldInStep($i, $errors->toArray());

            $stepTitles = [
            1 => __('Detil Laporan'),
            2 => __('Pihak Terlibat Langsung'),
            ];
            @endphp

            <div
                wire:key="step-create-container-{{ $i }}"
                @class([ 'border collapse collapse-arrow bg-base-100 border-base-300 rounded-xl relative z-0 transition-all duration-300 ease-in-out' , 'hover:-translate-x-1 hover:shadow-xl hover:z-10' , 'border-error shadow-md'=> $hasErrorInStep,
                'hover:border-info' => !$hasErrorInStep,
                'opacity-60 pointer-events-none' => $currentStep < $i
                    ])>

                    <input
                        type="radio"
                        name="create-accordion"
                        wire:click="goToStep({{ $i }})"
                        value="{{ $i }}"
                        {{ $currentStep == $i ? 'checked' : '' }} />

                    {{-- HEADER COLLAPSE --}}
                    <div @class([ 'flex items-center justify-between font-semibold collapse-title transition-colors duration-300' , 'bg-error text-error-content'=> $hasErrorInStep,
                        'bg-linear-to-r from-success to-info text-white' => $currentStep == $i,
                        'bg-base-200 text-base-content' => $currentStep != $i
                        ])>

                        <h3 class="flex items-center gap-2 text-xs font-bold tracking-wide uppercase">
                            <span>{{ __('BAGIAN') }} {{ $i }}</span>
                            <span class="hidden md:inline">– {{ $stepTitles[$i] }}</span>

                            @if($hasErrorInStep)
                            <span class="ml-2 text-white border-none badge badge-sm badge-ghost bg-white/20 animate-pulse">⚠️ ERROR</span>
                            @else
                            @if($currentStep > $i)
                            <span class="px-1 ml-2 text-white border-none badge badge-sm badge-success">✓</span>
                            @endif
                            @endif
                        </h3>
                    </div>

                    {{-- KONTEN --}}
                    <div class="text-xs collapse-content bg-base-100">
                        <div class="pt-4">
                            @if($hasErrorInStep)
                            <div class="p-2 mb-4 text-xs border rounded-lg bg-error/10 text-error border-error/20">
                                <strong>{{ __('Perhatian:') }}</strong> {{ __('Beberapa kolom wajib di bagian ini belum terisi dengan benar.') }}
                            </div>
                            @endif

                            @include('livewire.incident.step_create.incident-step-' . $i)

                            {{-- NAVIGASI TOMBOL --}}
                            <div class="flex justify-between pt-4 mt-4 border-t border-base-200">
                                <div>
                                    @if($i > 1)
                                    <button type="button" wire:click="goToStep({{ $i - 1 }})" class="btn btn-ghost btn-xs">
                                        « Kembali
                                    </button>
                                    @endif
                                </div>

                                <div class="flex gap-2">
                                    {{-- Tombol "Lanjut" hanya muncul di Bagian 1 --}}
                                    @if ($i < 2)
                                        <button wire:click="nextStep" class="px-4 text-white shadow-sm btn btn-info btn-xs">
                                        {{ __('Lanjut ke Bagian') }} {{ $i + 1 }} »
                                        </button>
                                        {{-- Tombol "Submit" muncul di Bagian 2 --}}
                                        @else
                                        <button type="button"
                                            wire:click="save"
                                            wire:loading.attr="disabled"
                                            class="px-4 text-white shadow-md btn btn-xs btn-success">
                                            <span wire:loading.remove wire:target="save">{{ __('Submit Laporan') }} </span>
                                            <span wire:loading.remove.class="hidden" wire:target="save" class="hidden">{{ __('Proses Submit...') }}</span>
                                            <span wire:loading.remove.class="hidden" wire:target="save" class="hidden loading loading-spinner loading-xs"></span>
                                        </button>
                                        @endif
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            @endfor
    </x-incident.layout>

    @push('scripts')
    <script>
        window.addEventListener('scroll-to-top', event => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
    @endpush
</section>