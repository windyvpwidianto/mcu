<section class="w-full">
    <x-toast />
    <div class="flex justify-start " wire:ignore>
        @php
            $layoutComponent = $reportId ? 'tabs-wpi.layout-edit' : 'tabs-wpi.layout';
            $currentRoute = Route::currentRouteName();
            $currentStatus = strtolower($status ?? '');
            $isDisabled = in_array($currentStatus, ['closed', 'cancelled']);
        @endphp

        @if (Breadcrumbs::exists($currentRoute))
            {!! Breadcrumbs::render($currentRoute, isset($reportId) ? $reportId : null) !!}
        @endif
    </div>
    @if ($reportId)
        <div class="mb-2 border shadow-md border-primary card bg-base-100">
            <div class="px-4 py-2 card-body">
                {{-- STATUS + Tombol Audit Trail --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="label">
                            <span class="text-xs font-semibold label-text">Status :</span>
                        </label>
                        <span
                            class="badge-xs italic badge {{ $this->getRandomBadgeColor($status) }} capitalize px-3 py-2">
                            {{ $status }}
                        </span>
                    </div>

                    {{-- Tombol buka modal Audit Trail --}}

                    {{-- Tombol Download PDF --}}
                    <div wire:ignore class="flex items-center gap-2">
                        <flux:tooltip content="Download PDF" position="top">
                            <flux:button wire:click="exportPDF({{ $reportId }})" size="xs"
                                icon="document-arrow-down" variant="primary" color="blue">
                            </flux:button>
                        </flux:tooltip>
                        <flux:tooltip content="Lihat Riwayat Perubahan" position="left">
                            <flux:button size="xs" variant="accent" icon='clock'
                                onclick="my_modal_2.showModal()">
                            </flux:button>
                        </flux:tooltip>
                    </div>

                </div>

                {{-- Form Action Workflow --}}
                <div class="flex flex-col gap-4 mt-2 md:flex-row md:items-end">
                    {{-- PROCEED TO (Dropdown Transisi) --}}
                    <div class="w-full max-w-xs">
                        <label class="py-1 label">
                            <span class="text-[10px] font-bold uppercase text-gray-500">Lanjutkan Ke / Transition
                                To</span> {{ $reviewed_by }}
                        </label>
                        <select wire:model.live="proceedTo"
                            class="w-full select select-xs select-bordered focus:ring-1 focus:border-info focus:ring-info">
                            <option value="">-- Pilih Aksi --</option>
                            @foreach ($availableTransitions as $label => $targetStatus)
                                <option value="{{ $targetStatus }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- LOGIKA ASSIGN ERM (Hanya tampil jika aksi yang dipilih mengarah ke InProgress/Assigned) --}}
                    @if (in_array($proceedTo, ['Assigned', 'Review Event']))
                        <div class="w-full max-w-xs">
                            <label class="py-1 label">
                                <span class="text-[10px] font-bold uppercase text-gray-500">Di Review Oleh</span>
                            </label>
                            <select wire:model="assignTo1" class="w-full select select-xs select-bordered focus:ring-1">
                                <option value="">-- Pilih User --</option>
                                @foreach ($ermList as $erm)
                                    <option value="{{ $erm['id'] }}">{{ $erm['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <div class="w-full max-w-xs">
                            <label class="py-1 label">
                                <span class="text-[10px] font-bold uppercase text-gray-500">Pilih ERM </span>
                            </label>
                            <select wire:model="assignTo2 "
                                class="w-full select select-xs select-bordered focus:ring-1">
                                <option value="">-- Pilih User --</option>
                                @foreach ($ermList as $erm)
                                    <option value="{{ $erm['id'] }}">{{ $erm['name'] }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                    @endif

                    {{-- TOMBOL KIRIM ACTION --}}
                    <div class="flex items-end">
                        <flux:button size="xs" {{-- Mengirim variabel proceedTo (nama status tujuan) ke fungsi di Component --}}
                            wire:click="processStatusChange('{{ $proceedTo }}')" icon-trailing="paper-airplane"
                            variant="primary" class="px-4" wire:loading.attr="disabled">
                            Kirim Aksi
                        </flux:button>
                    </div>
                </div>

                {{-- Modal Audit Trail --}}
                <dialog class="modal" id="my_modal_2" role="dialog">
                    <div class="max-w-4xl modal-box">
                        <form method="dialog">
                            <button class="absolute btn btn-sm btn-circle btn-ghost right-2 top-2">✕</button>
                        </form>
                        <h3 class="mb-4 text-lg font-bold">Audit Trail / Riwayat Laporan</h3>
                        <div class="max-h-[60vh] overflow-y-auto">
                            <table class="table border table-xs table-pin-rows">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-2 py-1 border">Tanggal</th>
                                        <th class="px-2 py-1 border">User</th>
                                        <th class="px-2 py-1 border">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Ambil aktivitas milik Report Utama
                                        $reportActivities = Spatie\Activitylog\Models\Activity::where(
                                            'subject_type',
                                            \App\Models\WpiReport::class,
                                        )->where('subject_id', $reportId);

                                        // Ambil aktivitas milik Finding yang berelasi dengan Report ini
                                        $findingIds = \App\Models\WpiFinding::where('wpi_report_id', $reportId)->pluck(
                                            'id',
                                        );
                                        $findingActivities = Spatie\Activitylog\Models\Activity::where(
                                            'subject_type',
                                            \App\Models\WpiFinding::class,
                                        )->whereIn('subject_id', $findingIds);

                                        // Gabungkan dan urutkan berdasarkan waktu terbaru
                                        $allActivities = $reportActivities->union($findingActivities)->latest()->get();
                                    @endphp

                                    @forelse($allActivities as $activity)
                                        <tr>
                                            <td class="px-2 py-1 border text-[10px]">
                                                {{ $activity->created_at->format('d-m-Y H:i') }}</td>
                                            <td class="px-2 py-1 italic font-semibold border">
                                                {{ $activity->causer->name ?? 'System' }}</td>
                                            <td class="px-2 py-1 border">
                                                {{-- Badge Tipe Aktivitas --}}
                                                <span
                                                    class="badge {{ $activity->subject_type == \App\Models\WpiReport::class ? 'badge-info' : 'badge-warning' }} badge-xs mb-1 uppercase font-bold text-[8px]">
                                                    {{ $activity->subject_type == \App\Models\WpiReport::class ? 'Report' : 'Finding' }}
                                                </span>

                                                <span
                                                    class="text-blue-600 text-[10px] block mb-1 uppercase font-bold">{{ $activity->description }}</span>

                                                @foreach ($activity->changes['attributes'] ?? [] as $field => $new)
                                                    @continue($field === 'updated_at' || $field === 'wpi_report_id' || str_ends_with($field, '_label'))

                                                    @php
                                                        $oldValue = $activity->changes['old'][$field] ?? '-';
                                                        $newValue = $new;

                                                        // Logic Switch untuk merubah ID menjadi Nama (Human Readable)
                                                        switch ($field) {
                                                            case 'created_by':
                                                                $oldValue =
                                                                    \App\Models\User::find($oldValue)?->name ??
                                                                    $oldValue;
                                                                $newValue =
                                                                    \App\Models\User::find($newValue)?->name ??
                                                                    $newValue;
                                                                break;
                                                            // MENGGUNAKAN LABEL HASIL tapActivity UNTUK INSPECTORS
                                                            case 'inspectors':
                                                                $oldValue =
                                                                    $activity->changes['old']['inspectors_label'] ??
                                                                    '-';
                                                                $newValue =
                                                                    $activity->changes['attributes'][
                                                                        'inspectors_label'
                                                                    ] ?? '-';
                                                                break;
                                                            case 'department_id':
                                                                $oldValue =
                                                                    \App\Models\Department::find($oldValue)
                                                                        ?->department_name ?? $oldValue;
                                                                $newValue =
                                                                    \App\Models\Department::find($newValue)
                                                                        ?->department_name ?? $newValue;
                                                                break;
                                                            case 'contractor_id':
                                                                $oldValue =
                                                                    \App\Models\Contractor::find($oldValue)
                                                                        ?->contractor_name ?? $oldValue;
                                                                $newValue =
                                                                    \App\Models\Contractor::find($newValue)
                                                                        ?->contractor_name ?? $newValue;
                                                                break;
                                                            case 'inspectors':
                                                            case 'pic_responsible':
                                                            case 'photos':
                                                            case 'photos_prevention':
                                                                // Jika data berupa array atau JSON, buat string yang enak dibaca
                                                                $oldValue = is_array($oldValue)
                                                                    ? implode(
                                                                        ', ',
                                                                        collect($oldValue)->flatten()->toArray(),
                                                                    )
                                                                    : $oldValue;
                                                                $newValue = is_array($newValue)
                                                                    ? implode(
                                                                        ', ',
                                                                        collect($newValue)->flatten()->toArray(),
                                                                    )
                                                                    : $newValue;
                                                                break;
                                                        }

                                                        $label = ucfirst(str_replace(['_id', '_'], ['', ' '], $field));
                                                    @endphp

                                                    <div class="text-[10px] border-l-2 border-primary pl-2 ml-1 mb-1">
                                                        <strong class="text-gray-600">{{ $label }}</strong>:
                                                        <span
                                                            class="text-red-500 line-through">{{ $oldValue ?: '-' }}</span>
                                                        <span class="mx-1">→</span>
                                                        <span
                                                            class="font-medium text-green-600">{{ $newValue ?: '-' }}</span>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-4 italic text-center text-gray-500">Belum
                                                ada riwayat aktivitas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>
            </div>
        </div>
    @endif

    {{-- BAGIAN CONTENT UTAMA --}}
    <x-dynamic-component :component="$layoutComponent" :heading="$reportId ? '' : 'Buat Laporan WPI Baru'" :subheading="$reportId ? '' : 'TT-MGT-FRS-024A'">
        {{-- BAGIAN WORKFLOW & AUDIT TRAIL (Hanya tampil jika Edit/Bukan laporan baru) --}}
        <form wire:submit.prevent="save" class="overflow-hidden border rounded-lg shadow-xl border-base-200">

            <div class="p-6 border-b border-primary ">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <fieldset class="relative fieldset">
                            <x-form.label label="Tanggal / Date" required />
                            <div class="{{ $errors->has('report_date') ? 'ring-1 ring-rose-500 rounded' : '' }}">
                                <div class="relative" wire:ignore x-data="{

                                    reportDate: @entangle('report_date'),
                                    fp: null,
                                    init() {
                                        this.fp = flatpickr(this.$refs.tanggalInput, {
                                            disableMobile: true,
                                            altInput: true,
                                            altFormat: 'd F Y',
                                            dateFormat: 'Y-m-d',
                                            // Set nilai awal dari Livewire ke Flatpickr
                                            defaultDate: this.reportDate,
                                            onChange: (selectedDates, dateStr) => {
                                                this.reportDate = dateStr;
                                            }
                                        });

                                        // Pantau perubahan dari sisi Livewire (misal: saat reset form atau edit data)
                                        this.$watch('reportDate', (newVal) => {
                                            this.fp.setDate(newVal, false);
                                        });
                                    }
                                }">

                                    <input {{ $isDisabled ? 'disabled' : '' }} type="text" x-ref="tanggalInput"
                                        placeholder="Pilih Tanggal..." readonly
                                        class="input input-bordered cursor-pointer w-full focus:ring-1 focus:border-info input-xs  {{ $errors->has('report_date') ? 'ring-1 ring-rose-500' : '' }}" />
                                </div>
                            </div>
                            <x-label-error :messages="$errors->get('report_date')" />
                        </fieldset>

                        <fieldset class="relative fieldset">
                            <x-form.label label="Jam / Time" required />
                            <div
                                class="{{ $errors->has('report_time') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500 rounded' : 'ring-base-300 focus:ring-base-300 focus:border-base-300 rounded' }}">
                                <div class="relative " wire:ignore x-data="{

                                    fp: null,
                                    initFlatpickr() {
                                        if (this.fp) this.fp.destroy();
                                        this.fp = flatpickr(this.$refs.tanggalInput, {
                                            disableMobile: true,
                                            enableTime: true,
                                            noCalendar: true,
                                            time_24hr: true,
                                            defaultDate: this.$wire.entangle('report_time').defer,
                                            dateFormat: 'H:i',
                                            clickOpens: true,
                                            // HAPUS ATAU KOMENTARI BARIS INI (appendTo)
                                            // appendTo: this.$refs.wrapper,

                                            // TAMBAHKAN ATAU UBAH OPSI POSITION
                                            position: 'auto-below', // Opsi ini akan memaksa kalender muncul di bawah input.

                                            onChange: (selectedDates, dateStr) => {
                                                this.$wire.set('report_time', dateStr);
                                            }
                                        });
                                    }
                                }" x-ref="wrapper"
                                    x-init="initFlatpickr();
                                    Livewire.hook('message.processed', () => {
                                        initFlatpickr();
                                    });">
                                    <input {{ $isDisabled ? 'disabled' : '' }} type="text" x-ref="tanggalInput"
                                        wire:model.live='report_time' placeholder="Pilih Tanggal dan Waktu..." readonly
                                        class="input input-bordered cursor-pointer w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs  {{ $errors->has('report_time') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                                </div>
                            </div>
                            <x-label-error :messages="$errors->get('report_time')" />
                        </fieldset>

                        <x-form.searchable-dropdown label="Lokasi / Location" required modelsearch="searchLocation"
                            :disabled="$isDisabled" modelid="location" :options="$locations" :showdropdown="$show_location"
                            clickaction="selectLocation" namedb="name" />
                    </div>

                    <div class="space-y-4">
                        <fieldset>
                            <input id="department" value="department" wire:model="deptCont"
                                {{ $isDisabled ? 'disabled' : '' }}
                                class="peer/department radio radio-xs radio-accent" type="radio" name="deptCont"
                                checked />
                            <x-form.label for="department" class="peer-checked/department:text-accent text-[10px]"
                                label="PT. MSM & PT. TTN" required />
                            <input id="company" value="company" wire:model="deptCont"
                                {{ $isDisabled ? 'disabled' : '' }} class="peer/company radio radio-xs radio-primary"
                                type="radio" name="deptCont" />
                            <x-form.label for="company" class="peer-checked/company:text-primary" label="Kontraktor"
                                required />

                            <div class="hidden peer-checked/department:block">
                                {{-- Department --}}
                                <div class="relative mb-1 ">
                                    <x-form.searchable-dropdown-without-label modelsearch="search" modelid="dept_cont"
                                        :disabled="$isDisabled" placeholder="Cari Departemen..." :options="$departments"
                                        :showdropdown="$showDropdown" clickaction="selectDepartment" namedb="department_name" />
                                </div>
                            </div>
                            <div class="hidden peer-checked/company:block">
                                {{-- Contractor --}}
                                <div class="relative mb-1 ">
                                    <x-form.searchable-dropdown-without-label modelsearch="searchContractor"
                                        :disabled="$isDisabled" placeholder="Cari Kontraktor..." modelid="dept_cont"
                                        :options="$contractors" :showdropdown="$showContractorDropdown" clickaction="selectContractor"
                                        namedb="contractor_name" />
                                </div>
                            </div>
                        </fieldset>
                        {{-- Menggunakan komponen baru --}}
                        <x-form.input-text :disabled="$isDisabled" label="Area Kerja" model="area"
                            placeholder="Area kerja..." required />
                        <fieldset class="fieldset">
                            <x-form.label label="Nama Site " required />
                            <input {{ $isDisabled ? 'disabled' : '' }} name="location_specific" type="text"
                                wire:model.live="location_specific" placeholder="Masukkan detail lokasi spesifik..."
                                value="Tokatindung" disabled
                                class=" input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('location_specific') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                            <x-label-error :messages="$errors->get('location_specific')" />
                        </fieldset>

                    </div>
                </div>
            </div>

            <div class="p-6 ">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold tracking-wider text-gray-700 uppercase">Nama Petugas Inspeksi /
                        Inspector</h3>
                    <button type="button" wire:click="addInspector"
                        class="px-3 py-1 text-xs text-base-content transition {{ $isDisabled ? 'btn btn-xs btn-disabled cursor-not-allowed' : 'btn btn-xs btn-info ' }} ">
                        + Tambah Petugas
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ($inspectors as $index => $inspector)
                        <div class="flex items-center space-x-2 " wire:key="ins-{{ $index }}">
                            <input type="hidden" wire:model="currentLoopIndex" value="{{ $index }}">
                            <span
                                class="flex-none mt-2 text-xs font-bold text-gray-400 w-14">{{ $index + 1 }}.</span>
                            <div class="flex-1 ">
                                {{-- Menggunakan Grid untuk membagi menjadi 3 kolom pada layar sedang/besar --}}
                                {{-- Container Utama dengan Grid Responsif --}}
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-1">

                                    <div class="flex flex-col w-full">
                                        {{-- Bagian Input: Full width di semua device --}}
                                        <div class="relative w-full">
                                            <x-form.searchable-select-advanced :disabled="$isDisabled"
                                                label="Petugas Inspeksi {{ $index + 1 }}"
                                                placeholder="Cari nama..."
                                                modelsearch="searchPetugas.{{ $index }}"
                                                modelid="inspectors.{{ $index }}.name" :options="$pelaporsAct"
                                                :showdropdown="$showDropdownPetugas[$index] ?? false" :manualMode="$manualActPelaporMode" clickaction="selectActPelapor" />
                                        </div>

                                        {{-- Bagian Detail: Responsif --}}
                                        {{-- HP: Stack vertical/wrap | Laptop/PC: Horizontal inline --}}
                                        <div
                                            class="flex flex-wrap items-center mt-1 gap-x-2 gap-y-1 text-[8px] leading-none text-gray-600 uppercase tracking-tight">

                                            <div class="flex items-center whitespace-nowrap">
                                                <span class="font-bold">ID NUMBER:</span>
                                                <span
                                                    class="ml-1 text-gray-800">{{ $inspectors[$index]['id_number'] ?: '-' }}</span>
                                            </div>

                                            {{-- Pemisah (Hidden di HP jika layar terlalu sempit/wrap) --}}
                                            <div class="hidden text-gray-300 sm:block">|</div>

                                            <div class="flex items-center whitespace-nowrap">
                                                <span class="font-bold">DEPT/CONT:</span>
                                                <span
                                                    class="ml-1 text-gray-800">{{ $inspectors[$index]['dept_con'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            {{-- Sembunyikan index di input tersembunyi agar bisa dibaca saat method dipanggil --}}
                            {{-- Tombol Remove --}}
                            <div class="flex-none w-14">
                                @if (count($inspectors) > 1)
                                    {{-- Tombol Hapus --}}
                                    <flux:tooltip content="Hapus Inspector {{ $index + 1 }}" position="top">
                                        <flux:button
                                            class="{{ $isDisabled ? 'btn-disabled cursor-not-allowed' : '' }} "
                                            wire:click="removeInspector({{ $index }})" size="xs"
                                            icon="trash" variant="danger">
                                        </flux:button>
                                    </flux:tooltip>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="p-6 overflow-x-auto border-t border-primary">
                    <table class="w-full text-xs text-left border border-collapse border-primary/80">
                        <thead class="italic uppercase bg-base-300 text-base-content">
                            <tr>
                                <th class="w-8 p-2 text-center border border-primary/80">#</th>
                                <th class="w-24 p-2 text-center border border-primary/80">OHS Risk</th>
                                <th class="p-2 border border-primary/80">Uraian Temuan & Foto / Descibe Unsafe Act &
                                    Photo
                                </th>
                                <th class="p-2 border border-primary/80">Tindakan Pencegahan & Foto / Prevention Action &
                                    Photo</th>
                                <th class="w-48 p-2 border border-primary/80">Tindak Lanjut/ Follow Up</th>
                                <th class="w-12 p-2 text-center border border-primary/80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($findings as $index => $finding)
                                <tr wire:key="find-{{ $index }}" class="align-top hover:bg-base-200">
                                    <td class="p-2 font-bold text-center border border-primary/80">{{ $index + 1 }}
                                    </td>
                                    <td class="p-2 text-center border border-primary/80 ">
                                        {{-- Select OHS Risk --}}
                                        <select wire:model="findings.{{ $index }}.ohs_risk"
                                            {{ $isDisabled ? 'disabled' : '' }}
                                            class="select select-xs select-success focus:outline-hidden focus:ring-1 focus:border-success focus:ring-success ">
                                            <option value="L">Rendah\<span
                                                    class="italic text-blue-400 ">Low</span>
                                            </option>
                                            <option value="M">Menengah\<span
                                                    class="italic text-blue-400 ">Moderate</span></option>
                                            <option value="H">Tinggi\<span
                                                    class="italic text-blue-400 ">High</span>
                                            </option>
                                            <option value="E">Ekstrem\<span
                                                    class="italic text-blue-400 ">Extreme</span>
                                            </option>
                                        </select>
                                    </td>
                                    <td class="p-2 border border-primary/80">
                                        {{-- Input Textarea Deskripsi --}}
                                        <x-form.textarea label="Deskripsi Temuan" required :disabled="$isDisabled"
                                            model="findings.{{ $index }}.description" />

                                        <div class="mt-1">
                                            {{-- Komponen Upload --}}
                                            <x-form.upload label="Lampirkan foto temuan" :disabled="$isDisabled"
                                                model="findings.{{ $index }}.new_photos" :file="$findings[$index]['new_photos'] ?? null" />

                                            {{-- AREA PREVIEW FILE BARU (TEMPORARY) --}}
                                            <div class="mt-2" wire:loading.remove
                                                wire:target="findings.{{ $index }}.new_photos">
                                                @if (isset($findings[$index]['new_photos']) && count($findings[$index]['new_photos']) > 0)
                                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                                        @foreach ($findings[$index]['new_photos'] as $fileKey => $newFile)
                                                            <div class="relative p-1 border rounded bg-gray-50"
                                                                wire:key="preview-{{ $index }}-{{ $fileKey }}">

                                                                @php
                                                                    $isUploadedFile = method_exists(
                                                                        $newFile,
                                                                        'temporaryUrl',
                                                                    );
                                                                    $extension = $isUploadedFile
                                                                        ? strtolower(
                                                                            $newFile->getClientOriginalExtension(),
                                                                        )
                                                                        : '';
                                                                @endphp

                                                                {{-- Tombol Hapus Temporary --}}
                                                                <x-button.remove
                                                                    click="removeTempPhoto({{ $index }}, {{ $fileKey }})"
                                                                    key="btn-remove-temp-{{ $index }}-{{ $fileKey }}" />

                                                                @if ($isUploadedFile && in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                                                    <img src="{{ $newFile->temporaryUrl() }}"
                                                                        class="object-cover w-full h-20 mt-2 border rounded" />
                                                                @else
                                                                    <div
                                                                        class="flex flex-col items-center justify-center h-20 mt-2 bg-gray-200 rounded">
                                                                        @if ($extension == 'pdf')
                                                                            <x-icon.pdf class="w-8 h-8 text-red-500" />
                                                                        @elseif(in_array($extension, ['doc', 'docx']))
                                                                            <x-icon.word
                                                                                class="w-8 h-8 text-blue-500" />
                                                                        @elseif(in_array($extension, ['xls', 'xlsx', 'csv']))
                                                                            <x-icon.excel
                                                                                class="w-8 h-8 text-green-600" />
                                                                        @else
                                                                            <x-icon.file
                                                                                class="w-8 h-8 text-gray-400" />
                                                                        @endif
                                                                        <span
                                                                            class="text-[8px] mt-1 truncate w-full px-2 text-center text-gray-600">
                                                                            {{ $isUploadedFile ? $newFile->getClientOriginalName() : 'File Error' }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- AREA FILE TERSIMPAN (PERMANENT DENGAN FITUR DOWNLOAD) --}}
                                            @if (!empty($finding['photos']))
                                                <div class="flex flex-wrap gap-2 pt-2 mt-2 border-t">
                                                    <p class="text-[9px] text-gray-400 w-full mb-1 uppercase italic">
                                                        File
                                                        Tersimpan:</p>
                                                    @foreach ($finding['photos'] as $photoKey => $photoPath)
                                                        @php
                                                            $extension = strtolower(
                                                                pathinfo($photoPath, PATHINFO_EXTENSION),
                                                            );
                                                            $isImage = in_array($extension, [
                                                                'jpg',
                                                                'jpeg',
                                                                'png',
                                                                'gif',
                                                            ]);
                                                        @endphp

                                                        <div class="relative group"
                                                            wire:key="saved-{{ $index }}-{{ $photoKey }}">

                                                            {{-- Jika Gambar: Klik untuk pratinjau di tab baru --}}
                                                            @if ($isImage)
                                                                <a href="{{ Storage::url($photoPath) }}"
                                                                    target="_blank">
                                                                    <img src="{{ Storage::url($photoPath) }}"
                                                                        class="object-cover w-12 h-12 transition-opacity border rounded shadow-sm opacity-80 hover:opacity-100">
                                                                </a>

                                                                {{-- Jika Dokumen: Klik untuk memicu public function downloadFile --}}
                                                            @else
                                                                <button type="button"
                                                                    wire:click="downloadFile('{{ $photoPath }}')"
                                                                    class="flex flex-col items-center justify-center w-12 h-12 transition-colors border rounded bg-gray-50 hover:bg-gray-100"
                                                                    title="Klik untuk unduh">

                                                                    @if ($extension == 'pdf')
                                                                        <x-icon.pdf class="w-6 h-6 text-red-500" />
                                                                    @elseif(in_array($extension, ['xls', 'xlsx', 'csv']))
                                                                        <x-icon.excel class="w-6 h-6 text-green-600" />
                                                                    @else
                                                                        <x-icon.word class="w-6 h-6 text-blue-500" />
                                                                    @endif
                                                                    <span
                                                                        class="text-[6px] mt-0.5 uppercase">{{ $extension }}</span>
                                                                </button>
                                                            @endif

                                                            {{-- Tombol Hapus Permanent tetap di sini --}}
                                                            <x-button.remove
                                                                click="removeSavedPhoto({{ $index }}, {{ $photoKey }})"
                                                                key="btn-remove-saved-{{ $index }}-{{ $photoKey }}"
                                                                confirm="Hapus file ini secara permanen?"
                                                                class="transition-opacity scale-75 opacity-0 -top-1 -right-1 group-hover:opacity-100" />
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-2 border border-primary/80">
                                        {{-- Input Textarea --}}
                                        <x-form.textarea label="Tindakan pencegahan" required :disabled="$isDisabled"
                                            placeholder="Tindakan pencegahan..."
                                            model="findings.{{ $index }}.prevention_action" rows="3" />

                                        <div class="mt-1">
                                            {{-- Komponen Upload --}}
                                            <x-form.upload label="Lampirkan foto pencegahan" :disabled="$isDisabled"
                                                model="findings.{{ $index }}.new_photos_prevention"
                                                :file="$findings[$index]['new_photos_prevention'] ?? null" />

                                            {{-- Logika Preview Foto Baru (Temporary) --}}
                                            <div class="mt-2" wire:loading.remove
                                                wire:target="findings.{{ $index }}.new_photos_prevention">
                                                @if (isset($findings[$index]['new_photos_prevention']) && count($findings[$index]['new_photos_prevention']) > 0)
                                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                                        @foreach ($findings[$index]['new_photos_prevention'] as $fileKey => $newFile)
                                                            <div class="relative p-1 border rounded bg-gray-50"
                                                                wire:key="preview-prevention-{{ $index }}-{{ $fileKey }}">

                                                                {{-- Tombol Hapus Temp Photo --}}
                                                                <x-button.remove
                                                                    click="removeTempPhotoPrevention({{ $index }}, {{ $fileKey }})"
                                                                    key="btn-rm-temp-prev-{{ $index }}-{{ $fileKey }}" />

                                                                @php
                                                                    $isUploadedFile = method_exists(
                                                                        $newFile,
                                                                        'temporaryUrl',
                                                                    );
                                                                    $extension = $isUploadedFile
                                                                        ? strtolower(
                                                                            $newFile->getClientOriginalExtension(),
                                                                        )
                                                                        : '';
                                                                @endphp

                                                                @if ($isUploadedFile && in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                                                    <img src="{{ $newFile->temporaryUrl() }}"
                                                                        class="w-40 h-auto mt-2 border rounded" />
                                                                @else
                                                                    <div
                                                                        class="flex flex-col items-center justify-center h-20 mt-2 bg-gray-200 rounded">
                                                                        @if ($extension == 'pdf')
                                                                            <x-icon.pdf class="w-8 h-8 text-red-500" />
                                                                        @elseif(in_array($extension, ['doc', 'docx']))
                                                                            <x-icon.word
                                                                                class="w-8 h-8 text-blue-500" />
                                                                        @elseif(in_array($extension, ['csv', 'xlsx', 'xls']))
                                                                            <x-icon.excel
                                                                                class="w-8 h-8 text-green-600" />
                                                                        @endif
                                                                        <span
                                                                            class="text-[8px] mt-1 truncate w-full px-1 text-center text-gray-600">
                                                                            {{ $isUploadedFile ? $newFile->getClientOriginalName() : 'File Error' }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- AREA FILE TERSIMPAN (PERMANENT) DENGAN FITUR DOWNLOAD --}}
                                            @if (!empty($finding['photos_prevention']))
                                                <div class="flex flex-wrap gap-2 pt-2 mt-2 border-t">
                                                    <p class="text-[9px] text-gray-400 w-full mb-1 uppercase italic">
                                                        File
                                                        Pencegahan Tersimpan:</p>
                                                    @foreach ($finding['photos_prevention'] as $photoKey => $photoPath)
                                                        @php
                                                            $extension = strtolower(
                                                                pathinfo($photoPath, PATHINFO_EXTENSION),
                                                            );
                                                            $isImage = in_array($extension, [
                                                                'jpg',
                                                                'jpeg',
                                                                'png',
                                                                'gif',
                                                            ]);
                                                        @endphp
                                                        <div class="relative group"
                                                            wire:key="saved-{{ $index }}-{{ $photoKey }}">

                                                            {{-- Jika Gambar: Klik untuk pratinjau di tab baru --}}
                                                            @if ($isImage)
                                                                <a href="{{ Storage::url($photoPath) }}"
                                                                    target="_blank">
                                                                    <img src="{{ Storage::url($photoPath) }}"
                                                                        class="object-cover w-12 h-12 transition-opacity border rounded shadow-sm opacity-80 hover:opacity-100">
                                                                </a>

                                                                {{-- Jika Dokumen: Klik untuk memicu public function downloadFile --}}
                                                            @else
                                                                <button type="button"
                                                                    wire:click="downloadFile('{{ $photoPath }}')"
                                                                    class="flex flex-col items-center justify-center w-12 h-12 transition-colors border rounded bg-gray-50 hover:bg-gray-100"
                                                                    title="Klik untuk unduh">

                                                                    @if ($extension == 'pdf')
                                                                        <x-icon.pdf class="w-6 h-6 text-red-500" />
                                                                    @elseif(in_array($extension, ['xls', 'xlsx', 'csv']))
                                                                        <x-icon.excel class="w-6 h-6 text-green-600" />
                                                                    @else
                                                                        <x-icon.word class="w-6 h-6 text-blue-500" />
                                                                    @endif
                                                                    <span
                                                                        class="text-[6px] mt-0.5 uppercase">{{ $extension }}</span>
                                                                </button>
                                                            @endif

                                                            {{-- Tombol Hapus Permanent --}}
                                                            <x-button.remove
                                                                click="removeSavedPhotoPrevention({{ $index }}, {{ $photoKey }})"
                                                                key="btn-rm-saved-prev-{{ $index }}-{{ $photoKey }}"
                                                                confirm="Hapus file pencegahan ini secara permanen?"
                                                                class="transition-opacity scale-75 opacity-0 -top-1 -right-1 group-hover:opacity-100" />
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-2 space-y-2 border border-primary/80">
                                        <x-form.searchable-select-advanced label="Person in charge (PIC)"
                                            placeholder="Cari dan klik nama..." :disabled="$isDisabled"
                                            modelsearch="search_pic.{{ $index }}"
                                            modelid="findings.{{ $index }}.pic_responsible" :options="$pelapors_pic"
                                            :showdropdown="$showDropdown_pic[$index] ?? false" :manualMode="$manualPICPelaporMode" clickaction="selectPicPelapor" />

                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @if (isset($findings[$index]['pic_responsible']) && is_array($findings[$index]['pic_responsible']))
                                                @foreach ($findings[$index]['pic_responsible'] as $picKey => $picName)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded">
                                                        {{ $picName }}
                                                        <button type="button"
                                                            wire:click="removePic({{ $index }}, {{ $picKey }})"
                                                            class=" text-black hover:text-red-500 {{ $isDisabled ? 'btn-disabled cursor-not-allowed' : '' }}">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                        <fieldset class="relative fieldset">
                                            <x-form.label label="Tanggal Jatuh Tempo:" required />
                                            <div
                                                class="{{ $errors->has('findings.' . $index . '.due_date') ? 'ring-1 ring-rose-500 rounded' : 'ring-base-300 rounded' }}">
                                                <div class="relative" wire:ignore x-data="{
                                                    dueDate: @entangle('findings.' . $index . '.due_date'),
                                                    fp: null,
                                                    init() {
                                                        this.fp = flatpickr(this.$refs.tanggalInput, {
                                                            disableMobile: true,
                                                            altInput: true,
                                                            altFormat: 'd F Y',
                                                            dateFormat: 'Y-m-d',
                                                            defaultDate: this.dueDate,
                                                            position: 'auto-below',
                                                            onChange: (selectedDates, dateStr) => {
                                                                this.dueDate = dateStr;
                                                            }
                                                        });

                                                        // Sinkronisasi saat data dari database/Livewire berubah
                                                        this.$watch('dueDate', (newVal) => {
                                                            if (this.fp) {
                                                                this.fp.setDate(newVal, false);
                                                            }
                                                        });
                                                    }
                                                }">

                                                    <input type="text" x-ref="tanggalInput"
                                                        {{ $isDisabled ? 'disabled' : '' }}
                                                        placeholder="Pilih Tanggal..." readonly
                                                        class="input input-bordered cursor-pointer w-full focus:ring-1 focus:border-info input-xs {{ $errors->has('findings.' . $index . '.due_date') ? 'border-rose-500' : '' }}" />
                                                </div>
                                            </div>
                                            <x-label-error :messages="$errors->get('findings.' . $index . '.due_date')" />
                                        </fieldset>
                                        <fieldset class="relative fieldset">
                                            <x-form.label label="Tanggal Selesai:" />
                                            <div
                                                class="{{ $errors->has('findings.' . $index . '.completion_date') ? 'ring-1 ring-rose-500 rounded' : 'ring-base-300 rounded' }}">
                                                <div class="relative" wire:ignore x-data="{
                                                    completionDate: @entangle('findings.' . $index . '.completion_date'),
                                                    fp: null,
                                                    init() {
                                                        this.fp = flatpickr(this.$refs.tanggalInput, {
                                                            disableMobile: true,
                                                            altInput: true,
                                                            altFormat: 'd F Y',
                                                            dateFormat: 'Y-m-d',
                                                            defaultDate: this.completionDate,
                                                            position: 'auto-below',
                                                            onChange: (selectedDates, dateStr) => {
                                                                this.completionDate = dateStr;
                                                            }
                                                        });

                                                        // Sinkronisasi: Update kalender jika data di Livewire berubah (misal saat edit data)
                                                        this.$watch('completionDate', (newVal) => {
                                                            if (this.fp && newVal) {
                                                                this.fp.setDate(newVal, false);
                                                            }
                                                        });
                                                    }
                                                }">

                                                    <input type="text" x-ref="tanggalInput"
                                                        {{ $isDisabled ? 'disabled' : '' }}
                                                        placeholder="Pilih Tanggal..." readonly
                                                        class="input input-bordered cursor-pointer w-full focus:ring-1 focus:border-info input-xs {{ $errors->has('findings.' . $index . '.completion_date') ? 'border-rose-500' : '' }}" />
                                                </div>
                                            </div>
                                            <x-label-error :messages="$errors->get('findings.' . $index . '.completion_date')" />
                                        </fieldset>
                                    </td>
                                    <td class="p-2 text-center border border-primary/80">
                                        @if (count($findings) > 1)
                                            <button type="button" wire:click="removeFinding({{ $index }})"
                                                class="btn btn-xs btn-square btn-error {{ $isDisabled ? 'btn-disabled cursor-not-allowed' : '' }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- ===== MOBILE & TABLET VIEW ===== --}}
            <div class="block space-y-3 lg:hidden">
                @foreach ($findings as $index => $finding)
                    <div class="shadow-sm card bg-base-100 card-xs">
                        <div class="card-body">
                            <h2 class="catitlerd-">Temuan {{ $index + 1 }}</h2>
                            <fieldset class="fieldset">
                                <x-form.label label="OHS Risk" required />
                                <select wire:model="findings.{{ $index }}.ohs_risk"
                                    {{ $isDisabled ? 'disabled' : '' }}
                                    class="select select-xs select-success focus:outline-hidden focus:ring-1 focus:border-success focus:ring-success ">
                                    <option value="L">Rendah\<span class="italic text-blue-400 ">Low</span>
                                    </option>
                                    <option value="M">Menengah\<span
                                            class="italic text-blue-400 ">Moderate</span></option>
                                    <option value="H">Tinggi\<span class="italic text-blue-400 ">High</span>
                                    </option>
                                    <option value="E">Ekstrem\<span class="italic text-blue-400 ">Extreme</span>
                                    </option>
                                </select>
                            </fieldset>

                            <div
                                class="border collapse collapse-arrow bg-base-100 border-base-300 {{ $errors->hasAny(['findings.' . $index . '.description']) ? 'ring-1 ring-rose-500 rounded collapse-open' : '' }}">
                                <input type="radio" name="my-accordion-2" />
                                <div class="font-semibold collapse-title">Uraian Temuan & Foto / Descibe Unsafe Act &
                                    Photo</div>
                                <div class="collapse-content">
                                    <x-form.textarea label="Deskripsi Temuan" required :disabled="$isDisabled"
                                        model="findings.{{ $index }}.description" />

                                    <div class="mt-1">
                                        {{-- Komponen Upload --}}
                                        <x-form.upload label="Lampirkan foto temuan" :disabled="$isDisabled"
                                            model="findings.{{ $index }}.new_photos" :file="$findings[$index]['new_photos'] ?? null" />

                                        {{-- AREA PREVIEW FILE BARU (TEMPORARY) --}}
                                        <div class="mt-2" wire:loading.remove
                                            wire:target="findings.{{ $index }}.new_photos">
                                            @if (isset($findings[$index]['new_photos']) && count($findings[$index]['new_photos']) > 0)
                                                <div class="grid grid-cols-2 gap-2 mt-2">
                                                    @foreach ($findings[$index]['new_photos'] as $fileKey => $newFile)
                                                        <div class="relative p-1 border rounded bg-gray-50"
                                                            wire:key="preview-{{ $index }}-{{ $fileKey }}">

                                                            @php
                                                                $isUploadedFile = method_exists(
                                                                    $newFile,
                                                                    'temporaryUrl',
                                                                );
                                                                $extension = $isUploadedFile
                                                                    ? strtolower($newFile->getClientOriginalExtension())
                                                                    : '';
                                                            @endphp

                                                            {{-- Tombol Hapus Temporary --}}
                                                            <x-button.remove
                                                                click="removeTempPhoto({{ $index }}, {{ $fileKey }})"
                                                                key="btn-remove-temp-{{ $index }}-{{ $fileKey }}" />

                                                            @if ($isUploadedFile && in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                                                <img src="{{ $newFile->temporaryUrl() }}"
                                                                    class="object-cover w-full h-20 mt-2 border rounded" />
                                                            @else
                                                                <div
                                                                    class="flex flex-col items-center justify-center h-20 mt-2 bg-gray-200 rounded">
                                                                    @if ($extension == 'pdf')
                                                                        <x-icon.pdf class="w-8 h-8 text-red-500" />
                                                                    @elseif(in_array($extension, ['doc', 'docx']))
                                                                        <x-icon.word class="w-8 h-8 text-blue-500" />
                                                                    @elseif(in_array($extension, ['xls', 'xlsx', 'csv']))
                                                                        <x-icon.excel class="w-8 h-8 text-green-600" />
                                                                    @else
                                                                        <x-icon.file class="w-8 h-8 text-gray-400" />
                                                                    @endif
                                                                    <span
                                                                        class="text-[8px] mt-1 truncate w-full px-2 text-center text-gray-600">
                                                                        {{ $isUploadedFile ? $newFile->getClientOriginalName() : 'File Error' }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        {{-- AREA FILE TERSIMPAN (PERMANENT DENGAN FITUR DOWNLOAD) --}}
                                        @if (!empty($finding['photos']))
                                            <div class="flex flex-wrap gap-2 pt-2 mt-2 border-t">
                                                <p class="text-[9px] text-gray-400 w-full mb-1 uppercase italic">
                                                    File
                                                    Tersimpan:</p>
                                                @foreach ($finding['photos'] as $photoKey => $photoPath)
                                                    @php
                                                        $extension = strtolower(
                                                            pathinfo($photoPath, PATHINFO_EXTENSION),
                                                        );
                                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                                    @endphp

                                                    <div class="relative group"
                                                        wire:key="saved-{{ $index }}-{{ $photoKey }}">

                                                        {{-- Jika Gambar: Klik untuk pratinjau di tab baru --}}
                                                        @if ($isImage)
                                                            <a href="{{ Storage::url($photoPath) }}"
                                                                target="_blank">
                                                                <img src="{{ Storage::url($photoPath) }}"
                                                                    class="object-cover w-12 h-12 transition-opacity border rounded shadow-sm opacity-80 hover:opacity-100">
                                                            </a>

                                                            {{-- Jika Dokumen: Klik untuk memicu public function downloadFile --}}
                                                        @else
                                                            <button type="button"
                                                                wire:click="downloadFile('{{ $photoPath }}')"
                                                                class="flex flex-col items-center justify-center w-12 h-12 transition-colors border rounded bg-gray-50 hover:bg-gray-100"
                                                                title="Klik untuk unduh">

                                                                @if ($extension == 'pdf')
                                                                    <x-icon.pdf class="w-6 h-6 text-red-500" />
                                                                @elseif(in_array($extension, ['xls', 'xlsx', 'csv']))
                                                                    <x-icon.excel class="w-6 h-6 text-green-600" />
                                                                @else
                                                                    <x-icon.word class="w-6 h-6 text-blue-500" />
                                                                @endif
                                                                <span
                                                                    class="text-[6px] mt-0.5 uppercase">{{ $extension }}</span>
                                                            </button>
                                                        @endif

                                                        {{-- Tombol Hapus Permanent tetap di sini --}}
                                                        <x-button.remove
                                                            click="removeSavedPhoto({{ $index }}, {{ $photoKey }})"
                                                            key="btn-remove-saved-{{ $index }}-{{ $photoKey }}"
                                                            confirm="Hapus file ini secara permanen?"
                                                            class="transition-opacity scale-75 opacity-0 -top-1 -right-1 group-hover:opacity-100" />
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div
                                class="border collapse collapse-arrow bg-base-100 border-base-300 {{ $errors->hasAny(['findings.' . $index . '.prevention_action']) ? 'ring-1 ring-rose-500 rounded collapse-open' : '' }}">
                                <input type="radio" name="my-accordion-2" />
                                <div class="font-semibold collapse-title">Tindakan Pencegahan & Foto / Prevention
                                    Action & Photo</div>
                                <div class="collapse-content">
                                    <x-form.textarea label="Tindakan pencegahan" required :disabled="$isDisabled"
                                        placeholder="Tindakan pencegahan..."
                                        model="findings.{{ $index }}.prevention_action" rows="3" />

                                    <div class="mt-1">
                                        {{-- Komponen Upload --}}
                                        <x-form.upload label="Lampirkan foto pencegahan" :disabled="$isDisabled"
                                            model="findings.{{ $index }}.new_photos_prevention"
                                            :file="$findings[$index]['new_photos_prevention'] ?? null" />

                                        {{-- Logika Preview Foto Baru (Temporary) --}}
                                        <div class="mt-2" wire:loading.remove
                                            wire:target="findings.{{ $index }}.new_photos_prevention">
                                            @if (isset($findings[$index]['new_photos_prevention']) && count($findings[$index]['new_photos_prevention']) > 0)
                                                <div class="grid grid-cols-2 gap-2 mt-2">
                                                    @foreach ($findings[$index]['new_photos_prevention'] as $fileKey => $newFile)
                                                        <div class="relative p-1 border rounded bg-gray-50"
                                                            wire:key="preview-prevention-{{ $index }}-{{ $fileKey }}">

                                                            {{-- Tombol Hapus Temp Photo --}}
                                                            <x-button.remove
                                                                click="removeTempPhotoPrevention({{ $index }}, {{ $fileKey }})"
                                                                key="btn-rm-temp-prev-{{ $index }}-{{ $fileKey }}" />

                                                            @php
                                                                $isUploadedFile = method_exists(
                                                                    $newFile,
                                                                    'temporaryUrl',
                                                                );
                                                                $extension = $isUploadedFile
                                                                    ? strtolower($newFile->getClientOriginalExtension())
                                                                    : '';
                                                            @endphp

                                                            @if ($isUploadedFile && in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                                                <img src="{{ $newFile->temporaryUrl() }}"
                                                                    class="w-40 h-auto mt-2 border rounded" />
                                                            @else
                                                                <div
                                                                    class="flex flex-col items-center justify-center h-20 mt-2 bg-gray-200 rounded">
                                                                    @if ($extension == 'pdf')
                                                                        <x-icon.pdf class="w-8 h-8 text-red-500" />
                                                                    @elseif(in_array($extension, ['doc', 'docx']))
                                                                        <x-icon.word class="w-8 h-8 text-blue-500" />
                                                                    @elseif(in_array($extension, ['csv', 'xlsx', 'xls']))
                                                                        <x-icon.excel class="w-8 h-8 text-green-600" />
                                                                    @endif
                                                                    <span
                                                                        class="text-[8px] mt-1 truncate w-full px-1 text-center text-gray-600">
                                                                        {{ $isUploadedFile ? $newFile->getClientOriginalName() : 'File Error' }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        {{-- AREA FILE TERSIMPAN (PERMANENT) DENGAN FITUR DOWNLOAD --}}
                                        @if (!empty($finding['photos_prevention']))
                                            <div class="flex flex-wrap gap-2 pt-2 mt-2 border-t">
                                                <p class="text-[9px] text-gray-400 w-full mb-1 uppercase italic">
                                                    File
                                                    Pencegahan Tersimpan:</p>
                                                @foreach ($finding['photos_prevention'] as $photoKey => $photoPath)
                                                    @php
                                                        $extension = strtolower(
                                                            pathinfo($photoPath, PATHINFO_EXTENSION),
                                                        );
                                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                                    @endphp
                                                    <div class="relative group"
                                                        wire:key="saved-{{ $index }}-{{ $photoKey }}">

                                                        {{-- Jika Gambar: Klik untuk pratinjau di tab baru --}}
                                                        @if ($isImage)
                                                            <a href="{{ Storage::url($photoPath) }}"
                                                                target="_blank">
                                                                <img src="{{ Storage::url($photoPath) }}"
                                                                    class="object-cover w-12 h-12 transition-opacity border rounded shadow-sm opacity-80 hover:opacity-100">
                                                            </a>

                                                            {{-- Jika Dokumen: Klik untuk memicu public function downloadFile --}}
                                                        @else
                                                            <button type="button"
                                                                wire:click="downloadFile('{{ $photoPath }}')"
                                                                class="flex flex-col items-center justify-center w-12 h-12 transition-colors border rounded bg-gray-50 hover:bg-gray-100"
                                                                title="Klik untuk unduh">

                                                                @if ($extension == 'pdf')
                                                                    <x-icon.pdf class="w-6 h-6 text-red-500" />
                                                                @elseif(in_array($extension, ['xls', 'xlsx', 'csv']))
                                                                    <x-icon.excel class="w-6 h-6 text-green-600" />
                                                                @else
                                                                    <x-icon.word class="w-6 h-6 text-blue-500" />
                                                                @endif
                                                                <span
                                                                    class="text-[6px] mt-0.5 uppercase">{{ $extension }}</span>
                                                            </button>
                                                        @endif

                                                        {{-- Tombol Hapus Permanent --}}
                                                        <x-button.remove
                                                            click="removeSavedPhotoPrevention({{ $index }}, {{ $photoKey }})"
                                                            key="btn-rm-saved-prev-{{ $index }}-{{ $photoKey }}"
                                                            confirm="Hapus file pencegahan ini secara permanen?"
                                                            class="transition-opacity scale-75 opacity-0 -top-1 -right-1 group-hover:opacity-100" />
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div
                                class="border collapse collapse-arrow bg-base-100 border-base-300 {{ $errors->hasAny(['findings.' . $index . '.due_date', 'findings.' . $index . '.pic_responsible']) ? 'ring-1 ring-rose-500 rounded collapse-open' : '' }}">
                                <input type="radio" name="my-accordion-2" />
                                <div class="font-semibold collapse-title">Tindak Lanjut/ Follow Up</div>
                                <div class="collapse-content">

                                    <x-form.searchable-select-advanced label="Person in charge (PIC)"
                                        placeholder="Cari dan klik nama..." :disabled="$isDisabled"
                                        modelsearch="search_pic.{{ $index }}"
                                        modelid="findings.{{ $index }}.pic_responsible" :options="$pelapors_pic"
                                        :showdropdown="$showDropdown_pic[$index] ?? false" :manualMode="$manualPICPelaporMode" clickaction="selectPicPelapor" />

                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @if (isset($findings[$index]['pic_responsible']) && is_array($findings[$index]['pic_responsible']))
                                            @foreach ($findings[$index]['pic_responsible'] as $picKey => $picName)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded">
                                                    {{ $picName }}
                                                    <button type="button"
                                                        wire:click="removePic({{ $index }}, {{ $picKey }})"
                                                        class="text-black hover:text-red-500 {{ $isDisabled ? 'btn-disabled cursor-not-allowed' : '' }}">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>

                                    <fieldset class="relative mt-4 fieldset">
                                        <x-form.label label="Tanggal Jatuh Tempo:" required />
                                        <div
                                            class="{{ $errors->has('findings.' . $index . '.due_date') ? 'ring-1 ring-rose-500 rounded' : '' }}">
                                            <div class="relative" wire:ignore x-data="{
                                                dueDate: @entangle('findings.' . $index . '.due_date'),
                                                fp: null,
                                                init() {
                                                    this.fp = flatpickr(this.$refs.tanggalInput, {
                                                        disableMobile: true,
                                                        altInput: true,
                                                        altFormat: 'd F Y',
                                                        dateFormat: 'Y-m-d',
                                                        defaultDate: this.dueDate,
                                                        onChange: (selectedDates, dateStr) => { this.dueDate = dateStr; }
                                                    });
                                                    this.$watch('dueDate', (newVal) => { if (this.fp) this.fp.setDate(newVal, false); });
                                                }
                                            }">
                                                <input type="text" x-ref="tanggalInput"
                                                    {{ $isDisabled ? 'disabled' : '' }}
                                                    placeholder="Pilih Tanggal..." readonly
                                                    class="input input-bordered w-full input-xs {{ $errors->has('findings.' . $index . '.due_date') ? 'border-rose-500' : '' }}" />
                                            </div>
                                        </div>
                                        <x-label-error :messages="$errors->get('findings.' . $index . '.due_date')" />
                                    </fieldset>

                                    <fieldset class="relative mt-2 fieldset">
                                        <x-form.label label="Tanggal Selesai:" />
                                        <div
                                            class="{{ $errors->has('findings.' . $index . '.completion_date') ? 'ring-1 ring-rose-500 rounded' : '' }}">
                                            <div class="relative" wire:ignore x-data="{
                                                completionDate: @entangle('findings.' . $index . '.completion_date'),
                                                fp: null,
                                                init() {
                                                    this.fp = flatpickr(this.$refs.tanggalInput, {
                                                        disableMobile: true,
                                                        altInput: true,
                                                        altFormat: 'd F Y',
                                                        dateFormat: 'Y-m-d',
                                                        defaultDate: this.completionDate,
                                                        onChange: (selectedDates, dateStr) => { this.completionDate = dateStr; }
                                                    });
                                                    this.$watch('completionDate', (newVal) => { if (this.fp && newVal) this.fp.setDate(newVal, false); });
                                                }
                                            }">
                                                <input type="text" x-ref="tanggalInput"
                                                    {{ $isDisabled ? 'disabled' : '' }}
                                                    placeholder="Pilih Tanggal..." readonly
                                                    class="input input-bordered w-full input-xs {{ $errors->has('findings.' . $index . '.completion_date') ? 'border-rose-500' : '' }}" />
                                            </div>
                                        </div>
                                        <x-label-error :messages="$errors->get('findings.' . $index . '.completion_date')" />
                                    </fieldset>

                                </div>
                            </div>
                            <div class="justify-end card-actions">
                                @if (count($findings) > 1)
                                    <label class="btn btn-error btn-xs "
                                        wire:click="removeFinding({{ $index }})">Hapus</label>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- ===== FORM ACTION BUTTONS ===== --}}

            <div
                class="flex flex-col items-center justify-between gap-4 p-6 border-t border-primary md:flex-row">
                <button type="button" wire:click="addFinding"
                    class="{{ $isDisabled ? 'btn btn-xs btn-disabled cursor-not-allowed' : 'btn btn-xs btn-info ' }} ">
                    + Tambah Baris Temuan
                </button>

                <div class="flex items-center w-full space-x-3 md:w-auto">
                    <a href="{{ route('wpi.list') }}" class="btn btn-error btn-xs btn-soft">
                        Batal
                    </a>
                    <button type="submit"
                        class = "{{ $isDisabled ? 'btn btn-xs btn-disabled cursor-not-allowed' : 'btn btn-xs btn-success btn-soft' }} ">
                        <span wire:loading.remove
                            wire:target="save">{{ $reportId ? 'Perbarui Laporan' : 'Simpan Laporan' }}</span>
                        <span class="hidden" wire:loading.remove.class='hidden'
                            wire:target="save">Memproses...</span>
                    </button>
                </div>
            </div>
        </form>
    </x-dynamic-component>
</section>
