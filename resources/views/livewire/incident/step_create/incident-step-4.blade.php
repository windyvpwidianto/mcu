<div class="mt-4">
    <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-primary">{{ __('Analisis Faktor PEEPO') }}</h3>

    {{-- TAMPILAN MOBILE (Android/Smartphone) --}}
    <div class="space-y-4 md:hidden">
        @foreach($peepoFactors as $key => $label)
        <div @class([ 'collapse collapse-arrow border shadow-sm rounded-xl bg-base-100' , 'border-base-300'=> empty($peepo[$key]['temuan']) || empty($peepo[$key]['deskripsi']),
            'border-success/30' => !empty($peepo[$key]['temuan']) && !empty($peepo[$key]['deskripsi'])
            ]) wire:key="peepo-mob-{{ $key }}">
            <input type="checkbox" />
            <div class="flex items-center justify-between collapse-title">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-sm">{{ $label }}</span>
                    @if(!empty($peepo[$key]['temuan']) && !empty($peepo[$key]['deskripsi']))
                    <div class="badge badge-success badge-xs"></div>
                    @else
                    <div class="badge badge-ghost badge-xs bg-base-300"></div>
                    @endif
                </div>
                <div class="text-[10px] uppercase opacity-50">Ketuk untuk isi</div>
            </div>
            <div class="collapse-content space-y-4">
                <div class="pt-2 border-t border-base-200">
                    <x-form.text_area
                        label="Temuan"
                        model="peepo.{{ $key }}.temuan"
                        placeholder="Masukkan temuan..."
                        rows="3" />
                </div>
                <div>
                    <x-form.text_area
                        label="Deskripsi (5W+1H)"
                        model="peepo.{{ $key }}.deskripsi"
                        placeholder="Detail kejadian..."
                        rows="4"
                        required />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- TAMPILAN TABLET & DESKTOP --}}
    <div class="hidden md:block overflow-hidden border rounded-xl border-base-300 bg-base-100 shadow-sm">
        <table class="table w-full border-collapse">
            <thead>
                <tr class="bg-base-200/50 text-base-content border-b border-base-300">
                    <th class="w-1/6 px-4 py-3 font-bold border-r border-base-300">Factors</th>
                    <th class="w-2/5 px-4 py-3 font-bold border-r border-base-300">Temuan</th>
                    <th class="px-4 py-3 font-bold">Deskripsi (5W+1H)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-base-300">
                @foreach($peepoFactors as $key => $label)
                <tr class="hover:bg-base-200/20" wire:key="peepo-dt-{{ $key }}">
                    <td class="px-4 align-top pt-4 border-r border-base-300 bg-base-50/50">
                        <div class="flex flex-col gap-2">
                            <span class="font-bold text-sm">{{ $label }}</span>
                            @if(!empty($peepo[$key]['temuan']) && !empty($peepo[$key]['deskripsi']))
                            <span class="badge badge-success badge-outline badge-xs py-2 px-2">Lengkap</span>
                            @else
                            <span class="badge badge-ghost badge-xs py-2 px-2 text-gray-400">Belum Lengkap</span>
                            @endif
                        </div>
                    </td>
                    <td class="p-2 border-r border-base-300 align-top">
                        <x-form.text_area
                            model="peepo.{{ $key }}.temuan"
                            placeholder="Temuan faktor {{ $label }}..."
                            rows="3" />
                    </td>
                    <td class="p-2 align-top">
                        <x-form.text_area
                            model="peepo.{{ $key }}.deskripsi"
                            placeholder="Detail siapa, apa, dimana, kapan, mengapa..."
                            rows="3"
                            required />
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>