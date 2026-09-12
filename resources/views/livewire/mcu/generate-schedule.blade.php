<section class="w-full px-4 mx-auto pb-24 lg:pb-8 sm:px-6 lg:px-8">
    <x-toast />

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-base-200">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-primary/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold md:text-2xl text-base-content">Buat Jadwal MCU Baru</h2>
                <p class="text-sm text-base-content/60">Tentukan tanggal, lokasi, dan pilih peserta MCU.</p>
            </div>
        </div>
        
        @if(auth()->user()->hasRole('administrator') || auth()->user()->hasRole('medical staff'))
        <div>
            <button wire:click="openImportModal" class="btn btn-outline btn-primary btn-sm md:btn-md gap-2 shadow-sm hover:shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Import Excel
            </button>
        </div>
        @endif
    </div>

    <form wire:submit="generateJadwal" class="space-y-6">
        <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Kiri: Form & Daftar Peserta (Ambil 2 Kolom di Layar Besar) -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Card Informasi Jadwal -->
                <div class="border shadow-sm card bg-base-100 border-base-200 rounded-xl">
                    <div class="p-4 card-body md:p-6">
                        <h3
                            class="flex items-center gap-2 mb-4 text-base font-semibold md:text-lg text-base-content/80">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-info" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Informasi Jadwal
                        </h3>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-6">
                            <fieldset class="w-full fieldset">
                                <x-form.label label="Tanggal MCU" required />
                                <input type="text" readonly id="schedule_date" wire:model="schedule_date"
                                    placeholder="Pilih Tanggal MCU"
                                    class="w-full cursor-pointer input input-xs input-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0 focus:border-primary transition-all {{ $errors->has('schedule_date') ? 'input-error focus:ring-error/20' : '' }}"
                                    x-data="{ fp: null }" x-init="fp = flatpickr($refs.input, {
                                        altInput: true,
                                        altFormat: 'F j, Y',
                                        dateFormat: 'Y-m-d',
                                        static: true,
                                    });
                                    $wire.on('dateLoaded', () => {
                                        if ($wire.schedule_date) {
                                            fp.setDate($wire.schedule_date);
                                        }
                                    });" x-ref="input" />
                                <x-label-error :messages="$errors->get('schedule_date')" />
                            </fieldset>

                            <div class="w-full">
                                <x-form.input-text label="Lokasi MCU" model="location"
                                    placeholder="Masukkan detail lokasi" required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Peserta di dalam Layout MCU -->
                <x-mcu.layout>
                    <div class="border shadow-sm card bg-base-100 border-base-200 rounded-xl overflow-hidden">

                        <!-- Header Daftar Peserta -->
                        <div
                            class="flex flex-col items-start justify-between gap-4 p-4 border-b bg-base-200/30 md:flex-row md:items-center border-base-200">
                            <div>
                                <h3 class="flex items-center gap-2 text-base font-bold md:text-lg text-base-content">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Daftar Peserta
                                </h3>
                                <p class="mt-1 text-xs md:text-sm text-base-content/60">Pilih karyawan yang akan
                                    dijadwalkan dan atur notifikasinya.</p>
                            </div>
                            <div class="w-full md:w-72 lg:w-80">
                                <x-form.input-floating label="Cari Nama Karyawan..." model="search" />
                            </div>
                        </div>

                        <!-- Body Daftar Peserta -->
                        <div class="p-0 card-body">
                            @error('participantsData')
                                <div class="p-3 mx-4 mt-4 text-sm rounded-lg alert alert-warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 stroke-current shrink-0"
                                        fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Pilih minimal 1 peserta untuk melanjutkan.</span>
                                </div>
                            @enderror

                            @if ($employees->isNotEmpty())
                                <div class="flex flex-col divide-y divide-base-200">
                                    @foreach ($employees as $employee)
                                        @php
                                            $isSelected =
                                                isset($participantsData[$employee->id]['selected']) &&
                                                $participantsData[$employee->id]['selected'];
                                        @endphp

                                        <!-- Row Peserta Flex Layout (Bukan lagi Table) -->
                                        <div wire:key="row-emp-{{ $employee->id }}"
                                            class="flex flex-col gap-4 p-4 transition-all duration-200 md:flex-row md:items-start hover:bg-base-200/40 {{ $isSelected ? 'bg-primary/5 border-l-4 border-l-primary' : 'border-l-4 border-l-transparent bg-base-100' }}">

                                            <!-- Checkbox & Info Dasar -->
                                            <div class="flex items-start gap-3 md:w-1/3 shrink-0">
                                                <div class="pt-0.5">
                                                    <input type="checkbox"
                                                        wire:model.live="participantsData.{{ $employee->id }}.selected"
                                                        value="true"
                                                        class="checkbox checkbox-primary checkbox-sm md:checkbox-md" />
                                                </div>
                                                <div class="flex flex-col">
                                                    <div class="text-sm font-bold md:text-base">{{ $employee->name }}
                                                    </div>
                                                    <div class="text-xs text-base-content/70 mt-0.5">
                                                        {{ $employee->email }}</div>

                                                    <!-- Info Dept & NIK khusus di Mobile -->
                                                    <div class="flex flex-wrap items-center gap-2 mt-2 md:hidden">
                                                        <span
                                                            class="badge badge-ghost badge-sm text-[10px]">{{ $employee->department_name ?? 'Belum ada Dept' }}</span>
                                                        <span class="text-[10px] text-base-content/60 font-mono">NIK:
                                                            {{ $employee->employee_id ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Info Dept & NIK di Desktop/Tab -->
                                            <div class="hidden md:flex md:flex-col md:w-1/4 shrink-0">
                                                <div
                                                    class="mb-1 text-xs font-medium md:text-sm badge badge-ghost badge-sm md:badge-md">
                                                    {{ $employee->department_name ?? 'Belum ada Dept' }}</div>
                                                <div class="mt-1 text-xs text-base-content/60">NIK: <span
                                                        class="font-mono">{{ $employee->employee_id ?? '-' }}</span>
                                                </div>
                                            </div>

                                            <!-- Form Pengaturan WA -->
                                            <div class="w-full md:flex-1">
                                                @if ($isSelected)
                                                    <div
                                                        class="w-full p-4 space-y-4 border shadow-sm bg-base-100 border-base-200/80 rounded-xl animate-fade-in-down">
                                                        <div
                                                            class="flex items-center gap-1.5 text-xs font-bold tracking-wide uppercase text-primary mb-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Pengaturan Notifikasi
                                                        </div>

                                                        <div class="flex flex-col gap-4">
                                                            <x-form.input-text type="number"
                                                                model="participantsData.{{ $employee->id }}.wa"
                                                                placeholder="No WA (cth: 0812...)"
                                                                label="Nomor WhatsApp Karyawan" />

                                                            <div class="pt-2"
                                                                x-on:focusin="$wire.set('activeDeptRowId', {{ $employee->id }})">
                                                                <x-form.searchable-select-advanced
                                                                    label="Dept Head (Opsional)"
                                                                    placeholder="Cari Dept Head..."
                                                                    modelsearch="searchDeptHead.{{ $employee->id }}"
                                                                    modelid="participantsData.{{ $employee->id }}.dept_head_id"
                                                                    :options="$deptHeads" :showdropdown="$showDeptHeadDropdown[$employee->id] ??
                                                                        false"
                                                                    :manualMode="$manualDeptHeadMode[$employee->id] ??
                                                                        false"
                                                                    manualModelName="manualDeptHeadName.{{ $employee->id }}"
                                                                    enableManualAction="enableManualDeptHead({{ $employee->id }})"
                                                                    addManualAction="addDeptHeadManual({{ $employee->id }})"
                                                                    clickaction="selectDeptHead" />
                                                            </div>

                                                            <div class="pt-2"
                                                                x-on:focusin="$wire.set('activeSpvRowId', {{ $employee->id }})">
                                                                <x-form.searchable-select-advanced
                                                                    label="Supervisor (Opsional)"
                                                                    placeholder="Cari SPV..."
                                                                    modelsearch="searchSupervisor.{{ $employee->id }}"
                                                                    modelid="participantsData.{{ $employee->id }}.spv_id"
                                                                    :options="$managers" :showdropdown="$showSupervisorDropdown[$employee->id] ??
                                                                        false"
                                                                    :manualMode="$manualSupervisorMode[$employee->id] ??
                                                                        false"
                                                                    manualModelName="manualSupervisorName.{{ $employee->id }}"
                                                                    enableManualAction="enableManualSupervisor({{ $employee->id }})"
                                                                    addManualAction="addSupervisorManual({{ $employee->id }})"
                                                                    clickaction="selectSupervisor" />
                                                            </div>

                                                            <x-form.input-text type="number"
                                                                model="participantsData.{{ $employee->id }}.wa_spv"
                                                                placeholder="No WA SPV (Opsional)"
                                                                label="Nomor WhatsApp SPV" />
                                                        </div>
                                                    </div>
                                                @else
                                                    <div
                                                        class="hidden text-sm italic md:block text-base-content/40 md:pt-1">
                                                        Centang checkbox di sebelah kiri untuk mengatur notifikasi...
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="flex flex-col items-center justify-center w-full py-16 px-4 text-center bg-base-100">
                                    <div class="p-4 rounded-full bg-base-200 text-base-content/40 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-base-content">Belum Ada Data Karyawan</h3>
                                    <p class="max-w-xs mt-1 text-sm text-base-content/60">Silakan tambahkan karyawan
                                        baru untuk mulai mengatur jadwal dan notifikasi WhatsApp.</p>
                                    <div class="mt-4">
                                        <livewire:people.add-people />
                                    </div>
                                </div>
                            @endif

                            <div class="p-4 border-t border-base-200 bg-base-50">
                                {{ $employees->links() }}
                            </div>
                        </div>
                    </div>
                </x-mcu.layout>
            </div>

            <!-- Kanan: Sidebar Peserta Dipilih & Tombol Submit -->
            <div class="lg:col-span-1">
                <!-- Sticky Container agar mengikuti layar saat discroll ke bawah -->
                <div class="sticky flex flex-col h-full gap-4 top-6 lg:h-auto">

                    <div class="border shadow-sm card bg-base-100 border-base-200 rounded-xl">
                        <div class="p-5 card-body">
                            <h3 class="flex items-center gap-2 mb-1 text-lg font-bold text-base-content">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Peserta Dipilih
                            </h3>

                            @php
                                $totalSelected = collect($participantsData)->where('selected', true)->count();
                            @endphp

                            <p class="mb-4 text-sm font-medium text-base-content/70">
                                Total: <span class="badge badge-primary badge-sm">{{ $totalSelected }}</span> karyawan
                            </p>

                            <!-- Area List Peserta (Max height ditambahkan agar tidak kebablasan) -->
                            <div
                                class="overflow-y-auto max-h-[300px] lg:max-h-[450px] space-y-2 pr-1 custom-scrollbar">
                                @foreach ($participantsData as $id => $data)
                                    @if (isset($data['selected']) && $data['selected'])
                                        <div
                                            class="flex items-center justify-between p-3 text-sm transition-all border rounded-lg border-base-200 bg-base-50 group hover:border-primary/30 hover:shadow-sm">
                                            <span class="font-medium truncate text-base-content">
                                                {{ $this->getEmployeeName($id) }}
                                            </span>

                                            <button type="button"
                                                wire:click="removeParticipant('{{ $id }}')"
                                                class="p-1.5 text-error bg-error/10 hover:bg-error hover:text-white rounded-md transition-colors lg:opacity-0 lg:group-hover:opacity-100"
                                                title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach

                                @if ($totalSelected === 0)
                                    <div
                                        class="flex flex-col items-center justify-center py-8 text-center border border-dashed rounded-lg border-base-300 bg-base-50/50">
                                        <p class="text-sm italic text-base-content/40">Belum ada peserta yang dipilih.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Action (Fixed di bawah saat mobile, normal saat desktop) -->
                    <div
                        class="fixed bottom-0 left-0 right-0 z-50 p-4 border-t shadow-[0_-4px_15px_-3px_rgba(0,0,0,0.1)] border-base-200 bg-base-100 lg:relative lg:bg-transparent lg:border-none lg:shadow-none lg:p-0 flex justify-end w-full">
                        <button type="submit"
                            class="w-full text-base transition-all rounded-full shadow-md btn btn-primary lg:w-full hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-50"
                            @if ($totalSelected === 0) disabled @endif>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" wire:loading.remove
                                wire:target="generateJadwal">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            <span wire:loading.remove wire:target="generateJadwal">Generate Jadwal</span>
                            <span wire:loading wire:target="generateJadwal"
                                class="hidden loading loading-spinner loading-sm"></span>
                            <span wire:loading wire:target="generateJadwal" class="hidden ml-2">Memproses...</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <!-- Modal Import Excel -->
    @if ($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in-down">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">
                
                <!-- Header Modal -->
                <div class="flex justify-between items-center p-5 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Import Jadwal MCU via Excel
                    </h3>
                    <button wire:click="closeImportModal" class="btn btn-sm btn-circle btn-ghost text-gray-500 hover:bg-gray-100">✕</button>
                </div>

                <!-- Body Modal -->
                <div class="p-5 overflow-y-auto custom-scrollbar flex-1">
                    
                    @if(is_null($importResults))
                        <div class="alert alert-info bg-blue-50 text-blue-800 border-blue-200 mb-5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <h3 class="font-bold">Ketentuan Format Excel:</h3>
                                <div class="text-sm">
                                    Minimal memiliki 3 kolom (baris pertama sebagai header):
                                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                                        <li><span class="font-mono bg-blue-100 px-1 rounded">employee_id</span> (NIK Karyawan)</li>
                                        <li><span class="font-mono bg-blue-100 px-1 rounded">tanggal_mcu</span> (Format: YYYY-MM-DD atau cell date)</li>
                                        <li><span class="font-mono bg-blue-100 px-1 rounded">lokasi_mcu</span> (Nama klinik/RS)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold text-gray-700">Pilih File Excel (.xls, .xlsx)</span>
                            </label>
                            <input type="file" wire:model="excelFile" class="file-input file-input-bordered file-input-primary w-full bg-gray-50" accept=".xls,.xlsx" />
                            @error('excelFile') 
                                <span class="text-error text-sm mt-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ $message }}
                                </span> 
                            @enderror
                        </div>

                        <div wire:loading wire:target="excelFile" class="mt-3 text-sm text-info flex items-center gap-2">
                            <span class="loading loading-spinner loading-sm"></span> Mengunggah file...
                        </div>
                    @else
                        <!-- Tampilan Hasil Import -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-center shadow-sm">
                                <div class="text-2xl font-bold text-green-600">{{ $importResults['success'] }}</div>
                                <div class="text-xs text-green-700 uppercase tracking-wide mt-1">Sukses</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-center shadow-sm">
                                <div class="text-2xl font-bold text-red-600">{{ $importResults['failed'] }}</div>
                                <div class="text-xs text-red-700 uppercase tracking-wide mt-1">Gagal</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-center shadow-sm">
                                <div class="text-2xl font-bold text-blue-600">{{ $importResults['new_schedules'] }}</div>
                                <div class="text-xs text-blue-700 uppercase tracking-wide mt-1">Jadwal Baru</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-xl border border-purple-100 text-center shadow-sm">
                                <div class="text-2xl font-bold text-purple-600">{{ $importResults['new_participants'] }}</div>
                                <div class="text-xs text-purple-700 uppercase tracking-wide mt-1">Peserta Baru</div>
                            </div>
                        </div>

                        @if(count($importErrors) > 0)
                            <div class="mt-2">
                                <h4 class="font-bold text-red-600 mb-3 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Daftar Baris Error ({{ count($importErrors) }})
                                </h4>
                                <div class="overflow-x-auto border border-red-100 rounded-lg shadow-inner bg-red-50/30 max-h-60">
                                    <table class="table table-sm table-pin-rows">
                                        <thead>
                                            <tr class="bg-red-100 text-red-800">
                                                <th class="w-16">Baris</th>
                                                <th class="w-32">Employee ID</th>
                                                <th>Alasan Gagal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($importErrors as $error)
                                                <tr class="hover:bg-red-50">
                                                    <td class="font-mono text-center">{{ $error['row'] }}</td>
                                                    <td class="font-mono">{{ $error['employee_id'] }}</td>
                                                    <td class="text-sm text-red-700">{{ $error['reason'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Footer Modal -->
                <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 rounded-b-2xl">
                    <button wire:click="closeImportModal" class="btn btn-outline border-gray-300 text-gray-700 hover:bg-gray-200 hover:border-gray-400">Tutup</button>
                    
                    @if(is_null($importResults))
                        <button wire:click="importExcel" class="btn btn-primary shadow-md hover:shadow-lg transition-all" wire:loading.attr="disabled" {{ empty($excelFile) ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="importExcel">Mulai Import</span>
                            <span wire:loading wire:target="importExcel" class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-sm"></span> Memproses...
                            </span>
                        </button>
                    @else
                        <button wire:click="resetImport" class="btn btn-primary shadow-md hover:shadow-lg transition-all">
                            Import File Lain
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>
