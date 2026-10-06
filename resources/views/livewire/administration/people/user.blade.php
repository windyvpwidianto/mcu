<section class="w-full">
    <x-toast />
    <x-tabs-relation.layout>

            <div class="flex items-center justify-between border rounded-t-lg border-neutral-200 dark:border-base-200 md:px-4 md:absolute md:inset-x-0 md:top-0 md:z-20 md:flex-row bg-base-100 md:inset-shadow-sm">
                <div class="flex flex-row">

                    <x-button.btn-tooltip color="secondary" icon="refresh" modalId="showBulkUpdateModal" tooltip="Bulk Update" />
                    <div class="mx-2 w-60">
                              <x-form.input-floating label="Cari Pelapor" model="searchPeople" placeholder="Cari Pelapor..."  />
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <x-button.btn-tooltip color="primary" icon="add" modalId="create_modal" tooltip="Tambah Employee" />
                    <x-button.btn-tooltip modalId="import_modal" color="accent" icon="file-import" tooltip="Import Data User" />
                    <x-button.btn-tooltip modalId="import_phone_modal" color="info" icon="excel" tooltip="Import No HP by ID Badge" />
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="table table-xs">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" wire:model.live="selectAll">
                            </th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Username</th>
                            <th>Department / Perusahaan</th>
                            <th>Employee ID</th>
                            <th>Email</th>
                            <th>Nomor HP</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td>
                                <input type="checkbox" value="{{ $user->id }}" wire:model.live="selectedUsers">
                            </td>
                            <td>{{ $user->formatted_name }}</td>
                            <td>{{ $user->gender }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->department_name ?? $user->company_name ?? '-' }}</td>
                            <td>{{ $user->employee_id }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone_number ?? '-' }}</td>
                            <td class="flex gap-2">
                                <!-- Edit -->
                                <x-button.btn-tooltip color="warning" icon="edit" href="{{ route('people.details', $user->id) }}" tooltip="Details" />
                                <x-button.btn-tooltip color="error" icon="delete" modalId="delete_modal" wireClick="confirmDelete({{ $user->id }})" tooltip="Hapus" />

                                <!-- Delete -->

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
    </x-tabs-relation.layout>
    {{ $users->links() }}

    {{-- Create/Edit Modal --}}
    <dialog wire:ignore.self id="create_modal" class="modal">
        <div class="w-11/12 max-w-2xl modal-box">
            <h3 class="text-lg font-bold">{{ $userId ? 'Edit User ' . $name_user : 'Add User' }}</h3>

            <div class="grid grid-cols-2 gap-4 mt-4">

                <fieldset class="fieldset">
                    <x-form.label label="Nama" required />
                    <input type="text" wire:model.live="name"
                        class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('name') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                    <x-label-error :messages="$errors->get('name')" />
                </fieldset>

                <fieldset class="fieldset">
                    <x-form.label label="NIK" />
                    <input type="text" wire:model.live="nik"
                        class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('nik') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                    <x-label-error :messages="$errors->get('nik')" />
                </fieldset>

                <fieldset class="fieldset">
                    <x-form.label label="Nomor HP" />
                    <input type="text" wire:model.live="phone_number"
                        class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('phone_number') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                    <x-label-error :messages="$errors->get('phone_number')" />
                </fieldset>

                <fieldset class="fieldset">
                    <x-form.label label="Jenis Kelamin" required />
                    <select wire:model.live="gender"
                        class="w-full select select-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki - laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    <x-label-error :messages="$errors->get('gender')" />
                </fieldset>
                <fieldset class="relative fieldset">
                     <x-form.label label="Tanggal Lahir" required />
                    <div
                        class="{{ $errors->has('tanggal') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500 rounded' : 'ring-base-300 focus:ring-base-300 focus:border-base-300 rounded' }}">
                        <div class="relative " wire:ignore x-data="{
                            fp: null,
                            initFlatpickr() {
                                if (this.fp) this.fp.destroy();
                                this.fp = flatpickr(this.$refs.tanggalInput, {
                                    disableMobile: true,
                                    enableTime: false,
                                    defaultDate: this.$wire.entangle('date_birth').defer,
                                   dateFormat: 'Y-m-d',
                                    clickOpens: true,
                                    // HAPUS ATAU KOMENTARI BARIS INI (appendTo)
                                    // appendTo: this.$refs.wrapper,
                                    static: true,
                                    // TAMBAHKAN ATAU UBAH OPSI POSITION
                                    position: 'auto-below', // Opsi ini akan memaksa kalender muncul di bawah input.

                                    onChange: (selectedDates, dateStr) => {
                                        this.$wire.set('date_birth', dateStr);
                                    }
                                });
                            }
                        }" x-ref="wrapper"
                            x-init="initFlatpickr();
                            Livewire.hook('message.processed', () => {
                                initFlatpickr();
                            });">
                            <input type="text" x-ref="tanggalInput" wire:model.live='date_birth'
                                placeholder="Tanggal Lahir" readonly
                                class="input input-bordered cursor-pointer w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('tanggal') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                        </div>
                    </div>
                    <x-label-error :messages="$errors->get('date_birth')" />
                </fieldset>
                <fieldset class="fieldset">
                    <x-form.label label="Username" :required="!$userId" />
                    <input type="text" wire:model.live="username"
                        class="w-full input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
                    <x-label-error :messages="$errors->get('username')" />
                </fieldset>

                <fieldset>
                    <input id="department" value="department" wire:model="deptCont"
                        class="peer/department radio radio-xs radio-accent" type="radio" name="deptCont" checked />
                    <x-form.label for="department" class="peer-checked/department:text-accent text-[10px]"
                        label="PT. MSM & PT. TTN" required />
                    <input id="contractor" value="contractor" wire:model="deptCont"
                        class="peer/contractor radio radio-xs radio-primary" type="radio" name="deptCont" />
                    <x-form.label for="contractor" class="peer-checked/contractor:text-primary" label="Kontraktor"
                        required />

                    <div class="hidden peer-checked/department:block mt-0.5">
                        {{-- Department --}}
                        <div class="relative mb-1">
                            <!-- Input Search -->
                            <input name="search" type="text" wire:model.live.debounce.300ms="search"
                                wire:key="search-dept-{{ $userId }}" placeholder="Cari departemen..."
                                class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('department_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                            <!-- Dropdown hasil search -->
                            @if ($showDropdown && count($departments) > 0)
                            <ul
                                class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                <!-- Spinner ketika klik salah satu -->
                                <div wire:loading wire:target="selectDepartment" class="p-2 text-center">
                                    <span class="loading loading-spinner loading-sm text-secondary"></span>
                                </div>
                                @foreach ($departments as $dept)
                                <li wire:click="selectDepartment({{ $dept->id }}, '{{ $dept->department_name }}')"
                                    class="px-3 py-2 cursor-pointer hover:bg-base-200">
                                    {{ $dept->department_name }}
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @if ($deptCont === 'department')
                        <x-label-error :messages="$errors->get('department_id')" />
                        @endif
                    </div>
                    <div class="hidden peer-checked/contractor:block mt-0.5">
                        {{-- Contractor --}}
                        <div class="relative mb-1">
                            <!-- Input Search -->
                            <input name="searchContractor" type="text"
                                wire:model.live.debounce.300ms="searchContractor"
                                wire:key="search-contractor-{{ $userId }} placeholder=" Cari kontraktor..."
                                class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('contractor_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                            <!-- Dropdown hasil search -->
                            @if ($showContractorDropdown && count($contractors) > 0)
                            <ul
                                class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                <!-- Spinner ketika klik -->
                                <div wire:loading wire:target="selectContractor" class="p-2 text-center">
                                    <span class="loading loading-spinner loading-sm text-secondary"></span>
                                </div>
                                @foreach ($contractors as $contractor)
                                <li wire:click="selectContractor({{ $contractor->id }}, '{{ $contractor->contractor_name }}')"
                                    class="px-3 py-2 cursor-pointer hover:bg-base-200">
                                    {{ $contractor->contractor_name }}
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @if ($deptCont === 'contractor')
                        <x-label-error :messages="$errors->get('contractor_id')" />
                        @endif
                    </div>
                </fieldset>

                <fieldset class="fieldset">
                    <x-form.label label="Employee ID" required />
                    <input type="text" wire:model.live="employee_id"
                        class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('employee_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                    <x-label-error :messages="$errors->get('employee_id')" />
                </fieldset>

                <fieldset class="fieldset">
                    <x-form.label label="Tanggal masuk" required />
                    <input type="text" readonly id="date_commenced" wire:model="date_commenced"
                        class="cursor-pointer input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('date_commenced') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"
                        placeholder="Pilih tanggal masuk" x-data="{ fp: null }" {{-- Tambahkan state untuk Flatpickr instance --}}
                        x-init="// Inisialisasi Flatpickr dan simpan instance-nya
                        fp = flatpickr($refs.input, {
                            dateFormat: 'Y-m-d',
                             static: true,
                        });

                        // Dengarkan event 'dateLoaded' dari Livewire
                        $wire.on('dateLoaded', () => {
                            // Set tanggal menggunakan nilai Livewire saat event dipanggil
                            if ($wire.date_commenced) {
                                fp.setDate($wire.date_commenced);
                            }
                        });" x-ref="input" />
                    <x-label-error :messages="$errors->get('date_commenced')" />
                </fieldset>

                <fieldset class="fieldset">
                    <x-form.label label="Email" :required="!$userId" />
                    <input type="email" wire:model.live="email"
                        class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('email') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                    <x-label-error :messages="$errors->get('email')" />
                </fieldset>
                <fieldset class="fieldset">
                    <x-form.label label="Pilih Peran" required />
                    <select wire:model.live="role_id"
                        class="select select-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs{{ $errors->has('role_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                        <option value="">-- Pilih --</option>
                        @foreach ($role as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <x-label-error :messages="$errors->get('role_id')" />
                </fieldset>
                <fieldset class="fieldset">
                    <x-form.label label="Password" required="{{ $userId ? false : true }}" />
                    <input type="password" wire:model="password"
                        class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('password') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                    {{-- Teks bantuan saat edit --}}
                    @if ($userId)
                    <p class="text-[8px] text-gray-500 mt-0.5">Kosongkan jika tidak ingin mengubah password.
                    </p>
                    @endif
                    <x-label-error :messages="$errors->get('password')" />
                </fieldset>

                <fieldset class="fieldset">
                    <x-form.label label="Konfirmasi Password" required="{{ $userId ? false : true }}" />
                    <input type="password" wire:model="password_confirmation"
                        class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('password_confirmation') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                    <x-label-error :messages="$errors->get('password_confirmation')" />
                </fieldset>
            </div>

            <div class="modal-action">
                <flux:button wire:click="save" size="xs" icon:trailing="save" variant="primary">Simpan</flux:button>
                <form method="dialog">
                    <button class="btn btn-xs btn-error btn-soft">Close</button>
                </form>
            </div>
        </div>
    </dialog>

    {{-- Delete Confirmation Modal --}}
    <dialog class="modal" wire:ignore.self id="delete_modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Konfirmasi Penghapusan</h3>
            <p>Yakin menghapus Data User ini?</p>
            <div class="modal-action">
                 <form method="dialog">
                    <button class="btn btn-xs btn-error btn-soft">Batal</button>
                </form>
                <button class="btn btn-primary btn-soft btn-xs" wire:click="delete">Hapus</button>
            </div>
        </div>
    </dialog>
    </div>

    <dialog class="modal" wire:ignore.self id="import_modal">
        <div class="w-11/12 max-w-md modal-box">
            <h3 class="text-lg font-bold">Import Users</h3>

            <fieldset class="fieldset">
                <label class="block">File Excel</label>
                <input type="file" wire:model.live="file"
                    class="w-full input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />

                {{-- Error message --}}
                <x-label-error :messages="$errors->get('file')" />

                {{-- Loading indicator saat pilih file --}}
                <div wire:loading wire:target="file" wire:loading.class.remove="hidden"
                    class="hidden mt-1 text-sm text-info">
                    ⏳ Sedang mengunggah file...
                </div>
            </fieldset>
            @if (session()->has('success'))
            <div class="my-2 alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            <div class="modal-action">
                {{-- Tombol Import --}}
                <button wire:click="import" class="btn-xs btn btn-primary btn-soft"
                    wire:loading.attr="disabled" wire:target="import,file">

                    <span wire:loading.remove wire:target="import,file">Import</span>
                    <span wire:loading.class.remove='hidden' class="hidden"
                        wire:target="import,file">Mengimpor...</span>
                </button>

                {{-- Tombol Batal --}}
               <form method="dialog">
                    <button class="btn btn-xs btn-error btn-soft">Batal</button>
                </form>
            </div>
        </div>
    </dialog>

    <dialog class="modal" wire:ignore.self id="import_phone_modal">
        <div class="w-11/12 max-w-lg modal-box">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                Import Nomor HP by ID Badge
            </h3>
            <p class="text-xs text-base-content/70 mt-1">
                Upload file Excel (.xlsx / .csv) untuk mengupdate otomatis nomor telepon karyawan berdasarkan ID Badge (Employee ID).
            </p>

            <div class="mt-3 p-3 bg-base-200/60 rounded-lg text-xs space-y-1">
                <p class="font-semibold text-base-content">Format kolom yang didukung (Header Excel):</p>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <div class="bg-base-100 p-2 rounded border border-base-300">
                        <span class="font-mono text-primary font-bold">id_badge</span> / <span class="font-mono text-primary font-bold">employee_id</span>
                        <div class="text-[10px] text-base-content/60">ID Badge Karyawan</div>
                    </div>
                    <div class="bg-base-100 p-2 rounded border border-base-300">
                        <span class="font-mono text-secondary font-bold">phone_number</span> / <span class="font-mono text-secondary font-bold">nomor_hp</span>
                        <div class="text-[10px] text-base-content/60">Nomor HP / WhatsApp</div>
                    </div>
                </div>
            </div>

            <fieldset class="fieldset mt-4">
                <label class="block font-medium text-xs">Pilih File Excel / CSV</label>
                <input type="file" wire:model.live="phoneFile" accept=".xlsx,.xls,.csv"
                    class="w-full input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />

                <x-label-error :messages="$errors->get('phoneFile')" />

                <div wire:loading wire:target="phoneFile" class="mt-1 text-xs text-info flex items-center gap-1">
                    <span class="loading loading-spinner loading-xs"></span>
                    <span>Sedang mengunggah file ke server...</span>
                </div>

                @if ($phoneFile && !$errors->has('phoneFile'))
                <div wire:loading.remove wire:target="phoneFile" class="mt-1 text-xs text-success font-medium flex items-center gap-1">
                    <span>✓ File siap diimpor: <strong>{{ $phoneFile->getClientOriginalName() }}</strong></span>
                </div>
                @endif
            </fieldset>

            @if (session()->has('phone_success'))
            <div class="my-2 alert alert-success text-xs">
                {{ session('phone_success') }}
            </div>
            @endif

            @if (session()->has('phone_error'))
            <div class="my-2 alert alert-error text-xs">
                {{ session('phone_error') }}
            </div>
            @endif

            @if ($phoneImportResults)
            <div class="my-3 p-3 bg-base-200 rounded-lg text-xs space-y-1">
                <p class="font-semibold">Hasil Import Terakhir:</p>
                <p class="text-success font-medium">✓ Berhasil diupdate: {{ $phoneImportResults['updated'] }} karyawan</p>
                @if($phoneImportResults['notFound'] > 0)
                <p class="text-warning font-medium">⚠ Tidak ditemukan: {{ $phoneImportResults['notFound'] }} ID Badge</p>
                <p class="text-[11px] text-base-content/70 truncate">Badge: {{ implode(', ', array_slice($phoneImportResults['notFoundBadges'], 0, 10)) }} {{ count($phoneImportResults['notFoundBadges']) > 10 ? '...' : '' }}</p>
                @endif
                @if($phoneImportResults['skipped'] > 0)
                <p class="text-base-content/60">Dilewati: {{ $phoneImportResults['skipped'] }} baris (data kosong)</p>
                @endif
            </div>
            @endif

            <div class="modal-action">
                <button type="button" wire:click="importPhone" class="btn-xs btn btn-primary btn-soft"
                    wire:loading.attr="disabled" wire:target="importPhone,phoneFile">
                    <span wire:loading.remove wire:target="importPhone">Mulai Import</span>
                    <span wire:loading wire:target="importPhone" class="flex items-center gap-1">
                        <span class="loading loading-spinner loading-xs"></span>
                        Mengimpor...
                    </span>
                </button>

                <form method="dialog">
                    <button type="submit" class="btn btn-xs btn-error btn-soft">Tutup</button>
                </form>
            </div>
        </div>
    </dialog>

    <dialog class="modal"  wire:ignore.self id="showBulkUpdateModal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Bulk Update User</h3>

            <fieldset class="fieldset">
                <label class="block">Role Baru</label>
                <select wire:model="bulkRole" class="w-full select select-bordered input-xs">
                    <option value="">-- Pilih Role --</option>
                    @foreach ($roles as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </fieldset>
            <div class="modal-action">
                <button class="btn btn-xs btn-soft btn-primary" wire:click="bulkUpdate" variant="primary">Update</button>
               <form method="dialog">
                    <button class="btn btn-xs btn-error btn-soft">Batal</button>
                </form>
            </div>
        </div>
    </dialog>
</section>
