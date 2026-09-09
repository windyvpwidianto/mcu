<div class="mt-4">
    {{-- TAMPILAN MOBILE (Android/Smartphone) --}}
    <div class="space-y-4 md:hidden">
        @foreach($peepoFactors as $key => $label)
        <div @class([ 'collapse collapse-arrow border shadow-sm rounded-xl bg-base-100' , 'border-base-300'=> empty($peepo[$key]['temuan']) || empty($peepo[$key]['deskripsi']),
            'border-success/30' => !empty($peepo[$key]['temuan']) && !empty($peepo[$key]['deskripsi']),
            'opacity-80' => !$canEdit
            ]) wire:key="peepo-mob-{{ $key }}">
            <input type="checkbox" />
            <div class="flex items-center justify-between collapse-title">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold">{{ $label }}</span>
                    @if(!empty($peepo[$key]['temuan']) && !empty($peepo[$key]['deskripsi']))
                    <div class="badge badge-success badge-xs"></div>
                    @else
                    <div class="badge badge-ghost badge-xs bg-base-300"></div>
                    @endif
                </div>
                <div class="text-[10px] uppercase opacity-50">
                    {{ $canEdit ? 'Ketuk untuk isi' : 'Ketuk untuk lihat' }}
                </div>
            </div>
            <div class="space-y-4 collapse-content">
                <div class="pt-2 border-t border-base-200">
                    <x-form.text_area
                        label="Temuan"
                        model="peepo.{{ $key }}.temuan"
                        placeholder="Masukkan temuan..."
                        rows="3"
                        :disabled="!$canEdit" />
                </div>
                <div>
                    <x-form.text_area
                        label="Deskripsi (5W+1H)"
                        model="peepo.{{ $key }}.deskripsi"
                        placeholder="Detail kejadian..."
                        rows="4"
                        required
                        :disabled="!$canEdit" />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- TAMPILAN TABLET & DESKTOP --}}
    <div class="hidden overflow-hidden border shadow-sm md:block rounded-xl border-base-300 bg-base-100">
        <table class="table w-full border-collapse table-xs">
            <tbody>

                @foreach($peepoFactors as $key => $label)

                {{-- HEADER FACTOR --}}
                <tr class="bg-base-200 text-base-content">
                    <th colspan="3"
                        class="py-2 italic font-extrabold tracking-widest uppercase border-b border-base-300">

                        <div class="flex items-center justify-between">

                            <div class="flex items-center gap-2">
                                <span>{{ __($label) }}</span>

                                @php
                                $isComplete = collect($peepo[$key] ?? [])
                                ->every(fn($row) =>
                                !empty($row['temuan']) &&
                                !empty($row['deskripsi'])
                                );
                                @endphp

                                @if($isComplete && count($peepo[$key] ?? []))
                                <span class="badge badge-success badge-xs">
                                    Lengkap
                                </span>
                                @else
                                <span class="text-gray-400 badge badge-ghost badge-xs">
                                    Belum Lengkap
                                </span>
                                @endif
                            </div>

                            {{-- ADD BUTTON --}}
                            @if($canEdit)
                            <button
                                type="button"
                                wire:click="addRowPeepo('{{ $key }}')"
                                class="btn btn-info btn-xs">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-3 h-3 mr-1"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M12 4v16m8-8H4" />
                                </svg>

                                Tambah
                            </button>
                            @endif

                        </div>
                    </th>
                </tr>

                {{-- TABLE HEADER --}}
                <tr class="uppercase bg-base-100 text-base-content">
                    <th class="w-1/3 px-4 py-2 font-bold border-r border-b border-base-300 text-[10px]">
                        Temuan
                    </th>

                    <th class="px-4 py-2 font-bold border-b border-base-300 text-[10px]">
                        Deskripsi
                    </th>

                    <th class="w-10 border-b border-l border-base-300"></th>
                </tr>

                {{-- ROWS --}}
                @foreach(($peepo[$key] ?? []) as $index => $row)

                <tr
                    wire:key="peepo-{{ $key }}-{{ $index }}"
                    class="transition-colors hover:bg-base-200/30">

                    {{-- TEMUAN --}}
                    <td class="p-1 align-top border-b border-r border-base-300">

                        <x-form.text_area
                            model="peepo.{{ $key }}.{{ $index }}.temuan"
                            placeholder="Temuan faktor {{ $label }}..."
                            rows="2"
                            :disabled="!$canEdit" />

                    </td>

                    {{-- DESKRIPSI --}}
                    <td class="p-1 align-top border-b border-base-300">

                        <x-form.text_area
                            model="peepo.{{ $key }}.{{ $index }}.deskripsi"
                            placeholder="Detail siapa, apa, dimana, kapan, mengapa..."
                            rows="2"
                            required
                            :disabled="!$canEdit" />

                    </td>

                    {{-- DELETE --}}
                    <td class="p-1 text-center align-middle border-b border-l border-base-300">

                        @if($canEdit && count($peepo[$key]) > 1)

                        <button
                            type="button"
                            wire:click="removeRowPeepo('{{ $key }}', {{ $index }})"
                            class="btn btn-ghost btn-xs text-error">

                            ✕

                        </button>

                        @endif

                    </td>

                </tr>

                @endforeach

                @endforeach

            </tbody>
        </table>
    </div>
</div>