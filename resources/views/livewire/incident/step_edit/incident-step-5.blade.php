<div class="mt-4">
    {{-- Header Control --}}
    <div class="flex items-center justify-end pb-2 border-b">
        {{-- Kontrol di sisi kanan tanpa judul --}}
        <div class="flex gap-2">
            @if($canEdit)

            <flux:tooltip content="{{ __('Klik untuk menambah tahapan analisis berikutnya') }}" position="top">
                <flux:button icon="add-icon" wire:click="addWhyColumn" variant="primary" size="xs">
                    <span class="hidden sm:inline">{{ __('Tambah Why') }}</span>
                </flux:button>
            </flux:tooltip>
            @else
            <div class="gap-1 italic badge badge-ghost badge-sm opacity-70">
                <x-icon name="lock" class="w-3 h-3" /> Terkunci
            </div>
            @endif
        </div>
    </div>

    <div class="mt-4 border shadow-sm card bg-base-100 border-base-300">
        <div class="p-3 card-body md:p-6">

            {{-- Referensi Kronologi --}}
            <div class="relative p-3 mb-6 border-l-4 rounded-r-lg bg-info/5 border-info md:p-4">
                <div class="flex items-center gap-2 mb-1 text-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Deskripsi Kejadian') }}</span>
                </div>
                <p class="text-xs italic md:text-sm text-base-content/70 line-clamp-3 md:line-clamp-none">
                    {{ $description ?: __('Deskripsi belum diisi.') }}
                </p>
            </div>

            {{-- ANALISIS FLOW --}}
            <div @class([ 'flex flex-col gap-2 md:grid md:gap-4 md:overflow-x-auto pb-4' ,
                $this->gridClass => $whyCount >= 1
                ])>
                @for($i = 1; $i <= $whyCount; $i++)
                    <div class="relative flex flex-col md:flex-row" wire:key="why-container-{{ $i }}">

                    <div @class([ 'relative flex-1 p-4 transition-all border shadow-inner rounded-xl bg-base-200/50 border-base-300' , 'focus-within:border-primary/50'=> $canEdit
                        ])>
                        <div class="flex items-center justify-between mb-2">
                            <span class="italic font-bold tracking-tighter badge badge-primary badge-sm">{{ __('WHY') }} {{ $i }}</span>

                            {{-- Tombol Hapus (Hanya muncul jika boleh edit & urutan terakhir) --}}
                            @if($canEdit && $whyCount > 1 && $i == $whyCount)
                            <button wire:click="removeWhyColumn" class="btn btn-ghost btn-xs text-error">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            @endif
                        </div>

                        <x-form.text_area
                            model="why_analysis.why{{ $i }}"
                            placeholder="{{ $canEdit ? __('Mengapa hal di atas terjadi?') : __('Tidak ada analisis.') }}"
                            rows="3"
                            class="bg-base-100"
                            :disabled="!$canEdit" />
                    </div>

                    {{-- Indikator Panah --}}
                    @if($i < $whyCount)
                        <div class="flex items-center justify-center py-1 md:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-base-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
            </div>
            {{-- Divider Desktop --}}
            <div class="items-center justify-center hidden px-1 md:flex text-base-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                </svg>
            </div>
            @endif
        </div>
        @endfor
    </div>

    {{-- Hint untuk User --}}
    <div class="mt-2 text-center">
        <p class="text-[10px] text-base-content/40 italic">
            {{ $canEdit? __('Teruskan bertanya \"Mengapa\" sampai menemukan akar masalah sistemik.'): __('Analisis ini telah dikunci dan bersifat permanen.')}}
        </p>
    </div>
</div>
</div>
</div>