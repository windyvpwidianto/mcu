<div class="mt-4">
    {{-- Header & Tombol Tambah --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold uppercase lg:text-base text-primary">{{ __('Personel Terlibat Langsung') }}</h3>
        <button type="button" wire:click="addDirectlyInvolvedRow" class="btn btn-primary btn-xs sm:btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Tambah') }}
        </button>
    </div>

    {{-- TAMPILAN MOBILE (Hanya muncul di layar < 768px) --}}
    <div class="space-y-4 md:hidden">
        @foreach($directly_involved as $index => $person)
        <div class="relative p-4 border shadow-sm rounded-xl bg-base-100 border-base-300" wire:key="involved-mobile-{{ $index }}">
            {{-- Tombol Hapus Mobile --}}
            @if(count($directly_involved) > 1)
            <button type="button" wire:click="removeDirectlyInvolvedRow({{ $index }})"
                class="absolute btn btn-circle btn-ghost btn-xs text-error right-2 top-2">✕</button>
            @endif

            <div class="grid grid-cols-1 gap-3">
                <div class="form-control">
                    <x-form.searchable-select2 label="Nama" placeholder="Cari Nama...{{ $index }}"
                        wire:key="mob-name-{{ $index }}"
                        modelsearch="searchKorban.{{ $index }}"
                        modelid="directly_involved.{{ $index }}.employee_name"
                        :options="$involved_personnel_options"
                        :showdropdown="$show_employee_dropdown[$index] ?? false"
                        clickaction="selectInvolvedPersonnel(VALUE_ID, VALUE_NAME, {{ $index }})"
                        {{-- Properti Manual Mode --}}
                        :manualMode="$manualMode[$index] ?? false"
                        manualModelName="manualEmployeeName.{{ $index }}"
                        enableManualAction="enableManualMode({{ $index }})"
                        addManualAction="addManualData({{ $index }})" />

                </div>
                <div class="grid grid-cols-2 gap-2">
                    <x-form.input-text label="NIK/ID" model="directly_involved.{{ $index }}.employee_nik" :disabled="$person['employee_id'] ? true : false" />
                    <x-form.input-text label="Dept/Pers" model="directly_involved.{{ $index }}.dept_cont" :disabled="$person['employee_id'] ? true : false" />
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <x-form.input-text label="Jabatan" model="directly_involved.{{ $index }}.jabatan" />
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <x-form.input-text label="Roster" model="directly_involved.{{ $index }}.roster" />
                    <x-form.input-text label="Shift" model="directly_involved.{{ $index }}.sift" />
                    <x-form.input-text label="Exp (Thn)" model="directly_involved.{{ $index }}.pengalaman_kerja" />
                </div>

                <div class="form-control">
                    <x-form.select label="Keterlibatan" model="directly_involved.{{ $index }}.keterlibatan" :options="$this->keterlibatanOptions" />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- TAMPILAN TABLET & DESKTOP (Muncul di layar >= 768px) --}}
    <div class="hidden overflow-x-auto border rounded-lg md:block border-base-300">
        <table class="table w-full table-xs lg:table-sm">
            <thead>
                <tr class="bg-base-200 text-base-content">
                    <th class="w-1/4 font-bold border-r border-base-300">{{ __('Nama') }}</th>
                    <th class="font-bold border-r border-base-300">{{ __('ID & Divisi') }}</th>
                    <th class="font-bold border-r border-base-300">{{ __('Jabatan') }}</th>
                    <th class="font-bold text-center border-r border-base-300">{{ __('Roster/Shift') }}</th>
                    <th class="font-bold border-r border-base-300">{{ __('Keterlibatan') }}</th>
                    <th class="font-bold text-center border-r border-base-300">{{ __('Pengalaman (Tahun)') }}</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-base-300">
                @foreach($directly_involved as $index => $person)
                <tr wire:key="involved-desktop-{{ $index }}" class="hover:bg-base-100/50">
                    {{-- Nama --}}
                    <td class="p-1 align-top border-r border-base-300">
                        <x-form.searchable-select2 placeholder="Cari Nama..."
                            wire:key="dt-name-{{ $index }}"
                            modelsearch="searchKorban.{{ $index }}"
                            modelid="directly_involved.{{ $index }}.employee_name"
                            :options="$involved_personnel_options"
                            :showdropdown="$show_employee_dropdown[$index] ?? false"
                            clickaction="selectInvolvedPersonnel(VALUE_ID, VALUE_NAME, {{ $index }})"
                            {{-- Properti Manual Mode --}}
                            :manualMode="$manualMode[$index] ?? false"
                            manualModelName="manualEmployeeName.{{ $index }}"
                            enableManualAction="enableManualMode({{ $index }})"
                            addManualAction="addManualData({{ $index }})" />
                    </td>

                    {{-- ID & Dept (Digabung untuk hemat tempat di Tablet) --}}
                    <td class="p-1 space-y-1 align-top border-r border-base-300">
                        <x-form.input-text model="directly_involved.{{ $index }}.employee_nik" placeholder="NIK" :disabled="$person['employee_id'] ? true : false" />
                        <x-form.input-text model="directly_involved.{{ $index }}.dept_cont" placeholder="Dept" :disabled="$person['employee_id'] ? true : false" />
                    </td>

                    <td class="p-1 align-top border-r border-base-300">
                        <x-form.input-text model="directly_involved.{{ $index }}.jabatan" placeholder="Jabatan" />
                    </td>

                    <td class="p-1 align-top border-r border-base-300">
                        <div class="flex gap-1">
                            <x-form.input-text model="directly_involved.{{ $index }}.roster" placeholder="Ros" />
                            <x-form.input-text model="directly_involved.{{ $index }}.sift" placeholder="Shi" />
                        </div>
                    </td>

                    <td class="p-1 align-top border-r border-base-300">
                        <x-form.select model="directly_involved.{{ $index }}.keterlibatan" :options="$this->keterlibatanOptions" />
                    </td>

                    <td class="p-1 align-top border-r border-base-300">
                        <x-form.input-text model="directly_involved.{{ $index }}.pengalaman_kerja" />
                    </td>

                    <td class="p-1 text-center align-middle">
                        @if(count($directly_involved) > 1)
                        <button type="button" wire:click="removeDirectlyInvolvedRow({{ $index }})" class="btn btn-ghost btn-xs text-error">✕</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>