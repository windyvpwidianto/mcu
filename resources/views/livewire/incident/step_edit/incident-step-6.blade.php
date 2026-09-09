<div class="mt-4">
    {{-- TAMPILAN MOBILE (Android/Smartphone) --}}
    <div class="space-y-6 md:hidden">
        @php
        $sections = [
        ['title' => 'PENYEBAB LANGSUNG', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'subs' => [
        ['key' => 'unsafe_conditions', 'label' => 'Kondisi Tidak Aman', 'options' => $this->unsafeConditionOptions],
        ['key' => 'unsafe_acts', 'label' => 'Perilaku Tidak Aman', 'options' => $this->unsafeActOptions],
        ]],
        ['title' => 'PENYEBAB DASAR', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'subs' => [
        ['key' => 'personal_factors', 'label' => '2.1 Faktor Pribadi', 'options' => $this->personalFactorOptions],
        ['key' => 'job_factors', 'label' => '2.2 Faktor Pekerjaan', 'options' => $this->jobFactorOptions],
        ['key' => 'control_system_factors', 'label' => '2.3 Kelemahan Sistem Kontrol', 'options' => $this->controlSystemOptions],
        ]]
        ];
        @endphp

        @foreach($sections as $sec)
        <div class="space-y-3">
            <div class="{{ $sec['bg'] }} {{ $sec['text'] }} p-2 rounded-lg text-center font-bold italic text-sm border border-current/20">
                {{ $sec['title'] }}
            </div>

            @foreach($sec['subs'] as $sub)
            <div class="p-3 border shadow-sm rounded-xl bg-base-100 border-base-300">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold tracking-tighter uppercase opacity-70">{{ $sub['label'] }}</h4>
                    {{-- Tombol Tambah Mobile --}}
                    @if($canEdit)
                    <button type="button" wire:click="addRow('{{ $sub['key'] }}')" class="btn btn-primary btn-xs btn-circle">+</button>
                    @endif
                </div>

                <div class="space-y-4">
                    @foreach($this->{$sub['key']} as $index => $row)
                    <div class="relative p-3 border rounded-lg bg-base-200/40 border-base-200" wire:key="mob-{{ $sub['key'] }}-{{ $index }}">

                        {{-- Tombol Hapus Mobile --}}
                        @if($canEdit && count($this->{$sub['key']}) > 1)
                        <button type="button" wire:click="removeRow('{{ $sub['key'] }}', {{ $index }})"
                            class="absolute z-10 text-white shadow-md -top-2 -right-2 btn btn-error btn-xs btn-circle">✕</button>
                        @endif

                        <div class="space-y-2">
                            <x-form.select label="Kategori" model="{{ $sub['key'] }}.{{ $index }}.item" :options="$sub['options']" :disabled="!$canEdit" />
                            <x-form.text_area label="Deskripsi Detail" model="{{ $sub['key'] }}.{{ $index }}.description" rows="2" :disabled="!$canEdit" />
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- TAMPILAN TABLET & DESKTOP --}}
    <div class="hidden overflow-hidden border shadow-sm md:block rounded-xl border-base-300 bg-base-100">
        <table class="table w-full border-collapse table-xs">
            <tbody>
                @foreach($sections as $sec)
                <tr class="{{ $sec['bg'] }} {{ $sec['text'] }} text-center">
                    <th colspan="3" class="py-2 italic font-extrabold tracking-widest uppercase border-b border-base-300">{{ $sec['title'] }}</th>
                </tr>
                @foreach($sec['subs'] as $sub)
                <tr class="uppercase bg-gray-100 text-base-content">
                    <th class="w-1/3 px-4 py-2 font-bold border-r border-b border-base-300 text-[10px]">{{ $sub['label'] }}</th>
                    <th class="w-2/3 px-4 py-2 font-bold border-b border-base-300 text-[10px]">Description</th>
                    <th class="w-10 text-center border-b border-base-300">
                        @if(!$canEdit) <x-icon name="lock" class="w-3 h-3 mx-auto opacity-40" /> @endif
                    </th>
                </tr>

                @foreach($this->{$sub['key']} as $index => $row)
                <tr wire:key="dt-{{ $sub['key'] }}-{{ $index }}" class="transition-colors hover:bg-base-200/30">
                    <td class="p-1 align-top border-b border-r border-base-300 bg-base-50/30">
                        <x-form.select model="{{ $sub['key'] }}.{{ $index }}.item" :options="$sub['options']" placeholder="Pilih..." :disabled="!$canEdit" />
                    </td>
                    <td class="p-1 align-top border-b border-base-300">
                        <x-form.text_area model="{{ $sub['key'] }}.{{ $index }}.description" rows="2" :disabled="!$canEdit" />
                    </td>
                    <td class="p-1 text-center align-middle border-b border-l border-base-300">
                        {{-- Tombol Hapus Desktop --}}
                        @if($canEdit && count($this->{$sub['key']}) > 1)
                        <button type="button" wire:click="removeRow('{{ $sub['key'] }}', {{ $index }})" class="btn btn-ghost btn-xs text-error">✕</button>
                        @endif
                    </td>
                </tr>
                @endforeach

                {{-- Baris Tombol Tambah Desktop --}}
                @if($canEdit)
                <tr class="border-b border-base-300">
                    <td colspan="3" class="p-2 bg-base-200/20">
                        <button type="button" wire:click="addRow('{{ $sub['key'] }}')" class="font-bold btn btn-xs btn-info">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah {{ $sub['label'] }}
                        </button>
                    </td>
                </tr>
                @endif

                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>