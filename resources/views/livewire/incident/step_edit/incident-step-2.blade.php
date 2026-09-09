<div class="mt-4">
    {{-- Header & Tombol Tambah --}}
    <div class="flex items-center justify-between mb-4">
        {{-- Tombol Tambah hanya muncul jika boleh edit --}}
        @if($canEdit)
        <x-button.btn-tooltip color="primary" icon="add" wireClick="addDirectlyInvolvedRow" tooltip="Tambah Data" position="right sm:right" />
        @else
        <div class="gap-2 p-3 text-xs italic badge badge-ghost">
            <x-icon name="lock" class="w-3 h-3" /> Mode Baca Saja
        </div>
        @endif
    </div>

    {{-- TAMPILAN MOBILE --}}
    <div class="space-y-4 md:hidden">
        @foreach($directly_involved as $index => $person)
        <div class="relative p-4 border shadow-sm rounded-xl bg-base-100 border-base-300" wire:key="involved-mobile-{{ $index }}">
            {{-- Tombol Hapus Mobile - Hanya muncul jika boleh edit --}}
            @if($canEdit && count($directly_involved) > 1)
            <button type="button" wire:click="removeDirectlyInvolvedRow({{ $index }})"
                class="absolute btn btn-circle btn-ghost btn-xs text-error right-2 top-2">✕</button>
            @endif

            <div class="grid grid-cols-1 gap-3">
                <div class="form-control">
                    <x-form.searchable-select2 label="Nama" placeholder="Cari Nama..."
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
                        addManualAction="addManualData({{ $index }})"

                        :disabled="!$canEdit" />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <x-form.input-text label="NIK/ID" model="directly_involved.{{ $index }}.employee_nik"
                        :disabled="!$canEdit || ($person['employee_id'] ? true : false)" />
                    <x-form.input-text label="Dept/Pers" model="directly_involved.{{ $index }}.dept_cont"
                        :disabled="!$canEdit || ($person['employee_id'] ? true : false)" />
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <x-form.input-text label="Jabatan" model="directly_involved.{{ $index }}.jabatan" :disabled="!$canEdit" />
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <x-form.input-text label="Roster" model="directly_involved.{{ $index }}.roster" :disabled="!$canEdit" />
                    <x-form.input-text label="Sift" model="directly_involved.{{ $index }}.sift" :disabled="!$canEdit" />
                    <x-form.input-text label="Exp (Thn)" model="directly_involved.{{ $index }}.pengalaman_kerja" :disabled="!$canEdit" />
                </div>

                <div class="form-control">
                    <x-form.select label="Keterlibatan" model="directly_involved.{{ $index }}.keterlibatan"
                        :options="$this->keterlibatanOptions" :disabled="!$canEdit" />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- TAMPILAN TABLET & DESKTOP --}}
    <div class="hidden overflow-x-auto border rounded-lg md:block border-base-300">
        <table class="table w-full table-xs lg:table-sm">
            <thead>
                <tr class="bg-base-200 text-base-content">
                    <th class="w-1/4 font-bold border-r border-base-300">{{ __('Nama') }}</th>
                    <th class="font-bold border-r border-base-300">{{ __('ID & Divisi') }}</th>
                    <th class="font-bold border-r border-base-300">{{ __('Jabatan') }}</th>
                    <th class="font-bold text-center border-r border-base-300">{{ __('Roster/Sift') }}</th>
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
                        <x-form.searchable-select2 label="Nama" placeholder="Cari Nama..."
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
                            addManualAction="addManualData({{ $index }})"

                            :disabled="!$canEdit" />
                    </td>

                    {{-- ID & Dept --}}
                    <td class="p-1 space-y-1 align-top border-r border-base-300">
                        <x-form.input-text model="directly_involved.{{ $index }}.employee_nik" placeholder="NIK"
                            :disabled="!$canEdit || ($person['employee_id'] ? true : false)" />
                        <x-form.input-text model="directly_involved.{{ $index }}.dept_cont" placeholder="Dept"
                            :disabled="!$canEdit || ($person['employee_id'] ? true : false)" />
                    </td>

                    <td class="p-1 align-top border-r border-base-300">
                        <x-form.input-text model="directly_involved.{{ $index }}.jabatan" placeholder="Jabatan" :disabled="!$canEdit" />
                    </td>

                    <td class="p-1 align-top border-r border-base-300">
                        <div class="flex gap-1">
                            <x-form.input-text model="directly_involved.{{ $index }}.roster" placeholder="Ros" :disabled="!$canEdit" />
                            <x-form.input-text model="directly_involved.{{ $index }}.sift" placeholder="Shi" :disabled="!$canEdit" />
                        </div>
                    </td>

                    <td class="p-1 align-top border-r border-base-300">
                        <x-form.select model="directly_involved.{{ $index }}.keterlibatan" :options="$this->keterlibatanOptions" :disabled="!$canEdit" />
                    </td>

                    <td class="p-1 align-top border-r border-base-300">
                        <x-form.input-text model="directly_involved.{{ $index }}.pengalaman_kerja" :disabled="!$canEdit" />
                    </td>

                    <td class="p-1 text-center align-middle">
                        @if($canEdit && count($directly_involved) > 1)
                        <x-button.btn-tooltip color="error" icon="delete" wireClick="removeDirectlyInvolvedRow({{ $index }})" tooltip="Hapus Data" position="top md:left" />
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>