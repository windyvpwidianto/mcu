<div class="mt-6">
    <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-primary">{{ __('Tim Investigasi') }}</h3>

    {{-- TAMPILAN MOBILE (Android/iPhone) --}}
    <div class="space-y-6 md:hidden">
        {{-- Loop untuk setiap kategori: Pemimpin, Facilitator, Anggota --}}
        @foreach(['pemimpin' => 'Pemimpin Investigasi (KPLH)', 'facilitator' => 'Facilitator (KPLH)', 'anggota' => 'Tim Anggota'] as $type => $label)
        <div class="space-y-3">
            <div class="badge badge-outline badge-sm font-bold">{{ $label }}</div>

            @foreach($$type as $index => $item)
            <div class="relative p-4 border shadow-sm rounded-xl bg-base-100 border-base-300" wire:key="mob-{{ $type }}-{{ $index }}">
                <div class="grid grid-cols-1 gap-3">
                    {{-- Search Nama --}}
                    <x-form.searchable-select2
                        label="Nama {{ $index + 1 }}"
                        wire:key="sel-mob-{{ $type }}-{{ $index }}"
                        placeholder="Cari Nama..."
                        modelsearch="searchQuery.{{ $index }}.{{ $type }}"
                        modelid="{{ $type }}.{{ $index }}.user_id"
                        :options="$options"
                        :showdropdown="($showDropdownPartisipan[$index] ?? false) && $activeType === $type && $activeIndex === $index"
                        clickaction="selectUser(VALUE_ID, {{ $index }}, '{{ $type }}')"
                        x-on:focusin="$wire.set('activeType', '{{ $type }}'); $wire.set('activeIndex', {{ $index }})" />

                    <div class="grid grid-cols-2 gap-2">
                        <x-form.input-text label="Dept" model="{{ $type }}.{{ $index }}.dept" :disabled="!empty($item['user_id'])" />
                        <x-form.input-text label="Jabatan" model="{{ $type }}.{{ $index }}.jabatan" />
                    </div>
                </div>

                {{-- Action Buttons Mobile --}}
                <div class="flex justify-end gap-2 mt-3">
                    @if(count($$type) > 1)
                    <button type="button" wire:click="removeRow('{{ $type }}', {{ $index }})" class="btn btn-error btn-xs btn-outline">Hapus</button>
                    @endif
                    <button type="button" wire:click="addRow('{{ $type }}')" class="btn btn-success btn-xs btn-outline">+ Tambah</button>
                </div>
            </div>
            @endforeach
        </div>
        @if(!$loop->last)
        <hr class="border-base-300"> @endif
        @endforeach
    </div>

    {{-- TAMPILAN TABLET & DESKTOP --}}
    <div class="hidden md:block overflow-x-auto border rounded-xl border-base-300 bg-base-100">
        <table class="table w-full border-collapse table-sm">
            <thead>
                <tr class="bg-black text-white text-xs uppercase">
                    <th class="w-1/4 border-r border-base-300">Peran</th>
                    <th class="w-1/4 border-r border-base-300">Nama</th>
                    <th class="w-1/4 border-r border-base-300">Dept/Perusahaan</th>
                    <th class="w-1/4 border-base-300">Jabatan & Aksi</th>
                </tr>
            </thead>
            <tbody class="text-xs">
                @foreach(['pemimpin' => 'Pemimpin Investigasi (KPLH)', 'facilitator' => 'Facilitator (KPLH)', 'anggota' => 'Tim Anggota'] as $type => $label)
                @foreach($$type as $index => $item)
                <tr wire:key="row-dt-{{ $type }}-{{ $index }}" class="hover:bg-base-200/50">
                    @if($loop->first)
                    <td rowspan="{{ count($$type) }}" class="font-bold border-r border-b border-base-300 bg-base-200/30 align-top pt-4 text-center">
                        {{ $label }}
                    </td>
                    @endif

                    <td class="p-2 border-r border-b border-base-300">
                        <x-form.searchable-select2
                            wire:key="sel-dt-{{ $type }}-{{ $index }}"
                            placeholder="Cari Nama..."
                            modelsearch="searchQuery.{{ $index }}.{{ $type }}"
                            modelid="{{ $type }}.{{ $index }}.user_id"
                            :options="$options"
                            :showdropdown="($showDropdownPartisipan[$index] ?? false) && $activeType === $type && $activeIndex === $index"
                            clickaction="selectUser(VALUE_ID, {{ $index }}, '{{ $type }}')"
                            x-on:focusin="$wire.set('activeType', '{{ $type }}'); $wire.set('activeIndex', {{ $index }})" />
                    </td>
                    <td class="p-2 border-r border-b border-base-300">
                        <x-form.input-text model="{{ $type }}.{{ $index }}.dept" :disabled="!empty($item['user_id'])" />
                    </td>
                    <td class="p-2 border-b border-base-300">
                        <div class="flex items-center gap-2">
                            <div class="flex-grow">
                                <x-form.input-text model="{{ $type }}.{{ $index }}.jabatan" />
                            </div>
                            <div class="flex gap-1">
                                <button type="button" wire:click="addRow('{{ $type }}')" class="btn btn-square btn-xs btn-success text-white">+</button>
                                @if(count($$type) > 1)
                                <button type="button" wire:click="removeRow('{{ $type }}', {{ $index }})" class="btn btn-square btn-xs btn-error text-white">×</button>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>