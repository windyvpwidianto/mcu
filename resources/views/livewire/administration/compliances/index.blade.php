<section class="w-full">
    <x-toast />
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    @endpush
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    @endpush

    <div class="flex flex-col items-center px-2 rounded-lg shadow-sm lg:flex-row lg:justify-between bg-stone-400/20">

        {{-- BAGIAN KIRI: Tombol Aksi (Create & Import) --}}
        {{-- Ikon Tombol Sejajar Horizontal --}}
        <div class="flex flex-row gap-2">

            {{-- Tombol 'tambah data' --}}

            <x-button.btn-tooltip wire:click="create" color="primary" icon="add" tooltip="Tambah Data" />
            {{-- Komponen Import --}}
            @livewire('administration.compliances.compliance-import')

        </div>

        {{-- BAGIAN KANAN: Filter (Search & Date Range) --}}
        {{-- Menggunakan flex-row untuk membuat input search dan date range bersebelahan --}}
        <div class="flex flex-col gap-2 md:flex-row md:items-center">
            <div class="w-full">
                <select wire:model.live="class_search"
                class="w-full max-w-sm select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                <option value="">-- Select Existing Class --</option>

                {{-- Loop data class yang unik dari database --}}
                @foreach($this->existing_classes as $item)
                <option value="{{ $item }}">{{ $item }}</option>
                @endforeach

            </select>
            </div>
        </div>
    </div>

    <x-manhours.layout>
        <div class="overflow-x-auto ">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>name</th>
                        <th>Title (Name & Duration)</th>
                        <th>Class</th>
                        <th>Description</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ComplianceMaster as $index => $master)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $master->name }}</strong>
                        </td>
                        <td>
                            {{ $master->title }}
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $master->class }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ Str::limit($master->description, 50) }}</small>
                        </td>
                        <td class="text-center">
                            @if($master->duration_months)
                            {{ $master->duration_months }} Months
                            @else
                            <span class="badge bg-info text-dark">Lifetime</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($master->status)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <th class='flex flex-row justify-center gap-2'>
                            <x-button.btn-tooltip color="warning" icon="edit"
                                wireClick="edit({{ $master->id }})"
                                tooltip="Update" />
                            <x-button.btn-tooltip color="error" icon="delete"
                                        wireClick="showDelete({{ $master->id }})" modalId="delete_modal"
                                        tooltip="hapus data" />
                        </th>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-4 text-center">No compliance data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="absolute inset-x-0 bottom-0 z-50 mt-4 shadow-md bg-base-100 inset-shadow-sm">
            {{ $ComplianceMaster->links() }}
        </div>
        <dialog id='compliance_modal' class="modal" wire:ignore.self wire:target='store'>
            <div class="overflow-y-auto modal-box">
                <div class="space-y-4">
                    <x-form.input-text label="Compliance Name" model="name" placeholder="Compliance Name..." required />

                    <x-form.input-text label="Description" model="description" placeholder="Description..." required />

                    <fieldset class="fieldset">
                        <x-form.label label="Pilih Class" required />
                        <select wire:model.live="class"
                            class="w-full select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="">-- Select Existing Class --</option>

                            {{-- Loop data class yang unik dari database --}}
                            @foreach($this->existing_classes as $item)
                            <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach

                            {{-- Opsi jika ingin menambah kategori baru secara manual (opsional) --}}
                            <option value="new_class">+ Add New Class...</option>
                        </select>
                        <x-label-error :messages="$errors->get('class')" />
                    </fieldset>
                    @if($class === 'new_class')
                    <fieldset class="fieldset">
                        <x-form.input-text
                            label="New Class Name"
                            model="class"
                            placeholder="Type new class name here..." />
                    </fieldset>
                    @endif
                    <fieldset class="fieldset">
                        <x-form.label label="Duration (Months)" required />
                        <select wire:model.live="duration_months"
                            class="w-full select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="">-- Select Duration --</option>
                            <option value="0">Permanen (Lifetime)</option>
                            <option value="6">6 Bulan</option>
                            <option value="12">1 Tahun</option>
                            <option value="24">2 Tahun</option>
                            <option value="36">3 Tahun</option>
                            <option value="60">5 Tahun</option>
                        </select>
                        <small class="mt-1 text-muted">Pilih 0 jika tidak ada masa berlaku.</small>
                        <x-label-error :messages="$errors->get('duration_months')" />
                    </fieldset>

                    <fieldset class="p-2 mt-2 border rounded fieldset bg-gray-50">
                        <label class="text-xs font-semibold text-gray-500">Preview Title:</label>
                        <p class="text-sm font-bold text-info">
                            {{ $name ?: '...' }}
                            ({{ $duration_months > 0 ? "expiry in $duration_months bulan" : "Permanen" }})
                        </p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <x-form.label label="Status" required />
                        <select wire:model.live="status"
                            class="w-full select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <x-label-error :messages="$errors->get('status')" />
                    </fieldset>
                </div>
                <div class="modal-action">
                    <button class="btn btn-primary btn-soft btn-xs" wire:click='save'>{{$selected_id? 'Update':'Save' }}</button>
                    <form method="dialog">
                        <button class="btn btn-xs btn-soft btn-error">Close</button>
                    </form>
                </div>
            </div>
        </dialog>
        {{-- Modal konfirmasi --}}
        <dialog id='delete_modal' class="modal" wire:ignore.self>
            <div class="modal-box">
                <h3 class="text-lg font-bold">Konfirmasi Hapus</h3>
                <p class="py-4">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.</p>

                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn btn-xs btn-soft btn-accent">Batal</button>
                    </form>

                    <label class="btn btn-error btn-xs btn-soft" wire:click="delete" wire:loading.attr="disabled"
                        wire:target="delete">
                        Hapus
                    </label>
                </div>
            </div>
        </dialog>


    </x-manhours.layout>
    <script>
        // Mendengarkan signal dari Livewire untuk BUKA modal
        window.addEventListener('open-compliance-modal', event => {
            const modal = document.getElementById('compliance_modal');
            if (modal) {
                modal.showModal();
            }
        });

        // Mendengarkan signal dari Livewire untuk TUTUP modal
        window.addEventListener('close-compliance-modal', event => {
            const modal = document.getElementById('compliance_modal');
            if (modal) {
                modal.close();
            }
        });
         window.addEventListener('close-delete-modal', event => {
        const modal = document.getElementById('delete_modal');
            if (modal) {
                modal.close();
            }
    });
    </script>
</section>
