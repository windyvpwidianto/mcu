<!-- resources/views/livewire/hazard-list.blade.php -->
<section class="w-full">
    <x-toast />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <div class="flex justify-start mb-2 " wire:ignore>
        @if (Breadcrumbs::exists('hazard-detail'))
            {!! Breadcrumbs::render('hazard-detail', $hazard_id) !!}
        @endif
    </div>
    <div class="mb-2 shadow-md card bg-base-100 ">
        <div class="px-4 py-1 card-body ">
            {{-- STATUS + Tombol Audit Trail --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <label class="label">
                        <span class="text-xs font-semibold label-text">Status :</span>
                    </label>
                    <span class="badge-xs italic badge {{ $this->getRandomBadgeColor($hazard->status) }} capitalize">
                        {{ $hazard->status }}
                    </span>
                </div>

                {{-- Tombol buka modal --}}
                <div class="flex gap-2 ">
                    <flux:button size="xs" variant="accent" icon='clock' onclick="my_modal_2.showModal()">
                    </flux:button>

                    <div class="relative inline-block" wire:poll.10s>
                        <flux:button size="xs" variant="filled" icon="message-circle-more" wire:click="markAsRead"
                            {{-- Tambahkan ini --}} onclick="my_modal_5.showModal()">
                            Chat Laporan Hazard
                        </flux:button>

                        @if ($this->hasUnread)
                            <div class="absolute flex items-center justify-center -top-1 -right-1">
                                <div
                                    class="w-3 h-3 border-2 rounded-full status status-info animate-bounce border-base-100">
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            @php
                $isDisabled = in_array(optional($hazard)->status, ['cancelled', 'closed']);
            @endphp

            {{-- Form Action --}}
            <div class="flex flex-col gap-2 md:flex-row md:items-stretch ">
                {{-- PROCEED TO --}}
                <div class="max-w-sm">
                    <label class="label">
                        <span class="text-xs font-semibold label-text">{{ __('Lanjutkan Ke') }}</span>
                    </label>
                    <select wire:model.live="proceedTo"
                        class="w-full select select-xs select-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0">
                        <option value="">{{ __('-- Pilih Aksi --') }}</option>
                        @foreach ($availableTransitions as $label => $status)
                            <option class="text-{{ $this->getTextColor($status) }}" value="{{ $status }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PILIH ERM --}}
                @if ($proceedTo === 'in_progress')
                    <div class="max-w-sm">
                        <label class="label">
                            <span class="text-xs font-semibold label-text">{{ __('Pilih ERM Utama') }}</span>
                        </label>
                        <select wire:model="assignTo1"
                            class="w-full select select-xs select-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="">{{ __('-- Pilih --') }}</option>
                            @foreach ($ermList as $erm)
                                <option value="{{ $erm['id'] }}">{{ $erm['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="max-w-sm">
                        <label class="label">
                            <span
                                class="text-xs font-semibold label-text">{{ __('Pilih ERM Tambahan (Opsional)') }}</span>
                        </label>
                        <select wire:model="assignTo2"
                            class="w-full select select-xs select-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="">{{ __('-- Pilih --') }}</option>
                            @foreach ($ermList as $erm)
                                <option value="{{ $erm['id'] }}">{{ $erm['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- TOMBOL SIMPAN --}}
                <div class="self-end justify-end mt-1 card-actions">
                    <div x-data="{ proceedTo: @entangle('proceedTo') }" class="justify-end hidden card-actions md:block">
                        <div class="tooltip">
                            <div class="z-40 tooltip-content">
                                <div class="text-sm font-black text-orange-400 animate-bounce">{{ __('Kirim') }}
                                </div>
                            </div>
                            <flux:button size="xs" wire:click="processAction"
                                class="btn btn-active btn-square btn-primary btn-xs"> <x-icon.send /></flux:button>
                        </div>
                    </div>
                    <div x-data="{ proceedTo: @entangle('proceedTo') }" class="justify-end block card-actions md:hidden">
                        <div class="tooltip">
                            <div class="z-40 tooltip-content">
                                <div class="text-sm font-black text-orange-400 animate-bounce">{{ __('Kirim') }}
                                </div>
                            </div>
                            <button wire:click="processAction" class="btn btn-xs btn-active btn-primary">
                                {{ __('Kirim') }} <x-icon.send /></button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal DaisyUI --}}
            <dialog class="modal" id="my_modal_2" role="dialog" wire:ignore.self>
                <div class="md:max-w-4xl modal-box ">
                    <form method="dialog">
                        <button class="absolute btn btn-sm btn-circle btn-ghost right-2 top-2">✕</button>
                    </form>
                    <h3 class="mb-2 text-lg font-bold">Audit Trail</h3>
                    <div class="max-h-[80vh] overflow-y-auto overflow-x-auto">
                        <table class="table border table-xs table-pin-rows">
                            <thead>
                                <tr class="bg-primary">
                                    <th class="px-2 py-1 border">{{ __('Tanggal') }}</th>
                                    <th class="px-2 py-1 border">{{ __('User') }}</th>
                                    <th class="px-2 py-1 border">{{ __('Perubahan') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report->activities as $activity)
                                    <tr>
                                        <td class="px-2 py-1 border">{{ $activity->created_at->format('d-m-Y H:i') }}
                                        </td>
                                        <td class="px-2 py-1 border">
                                            {{ $activity->causer->name ?? $manualPelaporName }}
                                        </td>
                                        <td class="px-2 py-1 border">
                                            @if (str_contains($activity->description, 'ActionHazard'))
                                                {{-- Log khusus ActionHazard --}}
                                                <div class="mb-1 text-blue-600">
                                                    {{ $activity->description }}
                                                </div>
                                            @endif
                                            @foreach ($activity->changes['attributes'] ?? [] as $field => $new)
                                                @continue($field === 'updated_at')
                                                @php
                                                    $oldValue = $activity->changes['old'][$field] ?? '-';
                                                    $newValue = $new;

                                                    switch ($field) {
                                                        case 'penanggung_jawab_id':
                                                            $oldValue =
                                                                $activity->subject->penanggungJawab?->name ?? $oldValue;
                                                            $newValue =
                                                                \App\Models\User::find($new)?->name ?? $newValue;
                                                            break;
                                                        case 'pelapor_id':
                                                            $oldValue = $activity->subject->pelapor?->name ?? $oldValue;
                                                            $newValue =
                                                                \App\Models\User::find($new)?->name ?? $newValue;
                                                            break;
                                                        case 'department_id':
                                                            $oldValue =
                                                                $activity->subject->department?->department_name ??
                                                                $oldValue;
                                                            $newValue =
                                                                \App\Models\Department::find($new)?->department_name ??
                                                                $newValue;
                                                            break;
                                                        case 'contractor_id':
                                                            $oldValue =
                                                                $activity->subject->contractor?->contractor_name ??
                                                                $oldValue;
                                                            $newValue =
                                                                \App\Models\Contractor::find($new)?->contractor_name ??
                                                                $newValue;
                                                            break;
                                                        case 'location_id':
                                                            $oldValue =
                                                                $activity->subject->location?->name ?? $oldValue;
                                                            $newValue =
                                                                \App\Models\Location::find($new)?->name ?? $newValue;
                                                            break;
                                                        case 'event_type_id':
                                                            $oldValue =
                                                                $activity->subject->eventType?->event_type_name ??
                                                                $oldValue;
                                                            $newValue =
                                                                \App\Models\EventType::find($new)?->event_type_name ??
                                                                $newValue;
                                                            break;
                                                        case 'event_sub_type_id':
                                                            $oldValue =
                                                                $activity->subject->eventSubType
                                                                    ?->event_sub_type_name ?? $oldValue;
                                                            $newValue =
                                                                \App\Models\EventSubType::find($new)
                                                                    ?->event_sub_type_name ?? $newValue;
                                                            break;
                                                        case 'kondisi_tidak_aman_id':
                                                            $oldValue =
                                                                $activity->subject->hazardKondisiTidakAman?->name ??
                                                                $oldValue;
                                                            $newValue =
                                                                \App\Models\UnsafeCondition::find($new)?->name ??
                                                                $newValue;
                                                            break;
                                                        case 'tindakan_tidak_aman_id':
                                                            $oldValue =
                                                                $activity->subject->hazardTindakanTidakAman?->name ??
                                                                $oldValue;
                                                            $newValue =
                                                                \App\Models\UnsafeAct::find($new)?->name ?? $newValue;
                                                            break;
                                                        case 'consequence_id':
                                                            $oldValue =
                                                                $activity->subject->consequence?->name ?? $oldValue;
                                                            $newValue =
                                                                \App\Models\RiskConsequence::find($new)?->name ??
                                                                $newValue;
                                                            break;
                                                        case 'likelihood_id':
                                                            $oldValue =
                                                                $activity->subject->likelihood?->name ?? $oldValue;
                                                            $newValue =
                                                                \App\Models\Likelihood::find($new)?->name ?? $newValue;
                                                            break;
                                                    }
                                                    $label = ucfirst(str_replace('_', ' ', $field));
                                                @endphp

                                                <div class="mb-1">
                                                    <strong>{{ $label }}</strong>:
                                                    <span class="text-red-500">{{ $oldValue }}</span>
                                                    →
                                                    <span class="text-green-600">{{ $newValue }}</span>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-2 text-center text-gray-500">Belum ada perubahan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </dialog>

        </div>

    </div>
    <form wire:submit.prevent="submit">
        <div class="w-full p-1 mb-2 rounded bg-base-200">
            {{-- Tombol Update --}}
            <button
                class="{{ $isDisabled ? 'btn-xs btn btn-disabled cursor-not-allowed' : 'btn btn-primary btn-xs btn-active' }}"
                type="submit">
                <x-icon.edit />
                {{ __('Update Laporan') }}
            </button>

            {{-- Tombol Hapus dengan Konfirmasi --}}
            <button type="button"
                class=" {{ $isDisabled ? ' btn-xs btn btn-disabled cursor-not-allowed' : 'btn btn-error btn-xs btn-active' }}"
                wire:click="deleteHazard({{ $hazard_id }})" wire:confirm="{{ __('Yakin hapus Laporan ini?') }}">
                <x-icon.delete />
                {{ __('Hapus Laporan') }}
            </button>
        </div>
        <x-tab-hazard.layout>
            <div wire:loading.class="skeleton animate-pulse skeleton-text" wire:target="submit">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <fieldset class="fieldset">
                        <x-form.label label="Tipe Bahaya" required />
                        <select {{ $isDisabled ? 'disabled' : '' }} wire:model.live="tipe_bahaya"
                            class="w-full select select-xs select-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="">{{ __('-- Pilih --') }}</option>
                            @foreach ($eventTypes as $et)
                                <option value="{{ $et->id }}">{{ $et->event_type_name }}</option>
                            @endforeach
                        </select>
                        <x-label-error :messages="$errors->get('tipe_bahaya')" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <x-form.label label="Jenis Bahaya" required />
                        <select {{ $isDisabled ? 'disabled' : '' }} wire:model.live="sub_tipe_bahaya"
                            class="w-full select select-xs select-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="">{{ __('-- Pilih --') }}</option>
                            @if ($tipe_bahaya)
                                @foreach ($subTypes as $et)
                                    <option value="{{ $et->id }}">{{ __($et->event_sub_type_name) }}</option>
                                @endforeach
                            @endif

                        </select>
                        <x-label-error :messages="$errors->get('sub_tipe_bahaya')" />
                    </fieldset>

                    <fieldset>
                        <input {{ $isDisabled ? 'disabled' : '' }} id="kta" value="kta"
                            wire:model.live="keyWord" class="peer/kta radio radio-xs radio-accent" type="radio"
                            name="keyWord" checked />
                        <x-form.label for="kta" class="peer-checked/kta:text-accent text-[10px]"
                            label="Kondisi Tidak Aman" required />
                        <input {{ $isDisabled ? 'disabled' : '' }} id="tta" value="tta"
                            wire:model.live="keyWord" class="peer/tta radio radio-xs radio-primary" type="radio"
                            name="keyWord" />
                        <x-form.label for="tta" class="peer-checked/tta:text-primary text-[10px]"
                            label="Tindakan Tidak Aman" required />
                        <div class="hidden peer-checked/kta:block ">
                            <select {{ $isDisabled ? 'disabled' : '' }} wire:model.live="kondisi_tidak_aman"
                                class="select select-xs mb-1 select-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('kondisi_tidak_aman') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                                <option value="">{{ __('-- Pilih Kategori Bahaya --') }}</option>
                                @foreach ($ktas as $kta)
                                    <option value="{{ $kta->id }}">{{ __($kta->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="hidden peer-checked/tta:block ">
                            <select {{ $isDisabled ? 'disabled' : '' }} wire:model.live="tindakan_tidak_aman"
                                class="select select-xs mb-1 select-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('tindakan_tidak_aman') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                                <option value="">{{ __('-- Pilih Kategori Bahaya --') }}</option>
                                @foreach ($ttas as $tta)
                                    <option value="{{ $tta->id }}">{{ __($tta->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($keyWord === 'kta')
                            <x-label-error :messages="$errors->get('kondisi_tidak_aman')" />
                        @endif
                        @if ($keyWord === 'tta')
                            <x-label-error :messages="$errors->get('tindakan_tidak_aman')" />
                        @endif
                    </fieldset>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <fieldset class="mb-4 fieldset lg:col-span-2">
                        <x-form.label label="Deskripsi" required />
                        <div x-data="ckeditorHelper('description')" wire:ignore>
                            <div x-ref="editorElement" data-placeholder="Masukkan deskripsi..."></div>
                        </div>
                        <x-label-error :messages="$errors->get('description')" />
                    </fieldset>
                    <x-form.file-upload label="Lampirkan foto atau dokumentasi" model="new_doc_deskripsi"
                        :existingFile="$doc_deskripsi" :newFile="$new_doc_deskripsi" :isDisabled="$isDisabled" />
                </div>
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                    <x-form.searchable-dropdown label="Lokasi" required modelsearch="searchLocation"
                        modelid="location_id" :options="$locations" :showdropdown="$showLocationDropdown" clickaction="selectLocation"
                        :disabled="$isDisabled" namedb="name" />

                    {{-- Lokasi spesifik muncul hanya jika lokasi utama sudah dipilih --}}
                    @if ($location_id)
                        <fieldset class="fieldset">
                            <x-form.label label="Lokasi Spesifik" required />
                            <input {{ $isDisabled ? 'disabled' : '' }} type="text"
                                wire:model.live="location_specific" placeholder="Masukkan detail lokasi spesifik..."
                                class="w-full input input-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs" />
                            <x-label-error :messages="$errors->get('location_specific')" />
                        </fieldset>
                    @endif
                    <fieldset class="relative fieldset">
                        <x-form.label label="Tanggal & Waktu" required />
                        <div class="relative " wire:ignore x-data="{
                            fp: null,
                            // Properti Alpine.js untuk menampung nilai awal dari Livewire
                            tanggalValue: '{{ $this->tanggal }}',
                            initFlatpickr() {
                                if (this.fp) this.fp.destroy();
                                this.fp = flatpickr(this.$refs.tanggalInput, {
                                    disableMobile: true,
                                    enableTime: true,
                                    time_24hr: true,
                                    defaultDate: @js($this->tanggal),
                                    dateFormat: 'd-m-Y H:i',
                                    clickOpens: true,
                                    position: 'auto-below',
                        
                                    onChange: (selectedDates, dateStr) => {
                                        this.$wire.set('tanggal', dateStr);
                                    }
                                });
                            }
                        }" x-ref="wrapper"
                            x-init="initFlatpickr();
                            Livewire.hook('message.processed', () => {
                                // Re-initialize hanya jika Anda yakin properti 'tanggal' di Livewire berubah
                                // dan perlu diperbarui tanpa interaksi user.
                                // initFlatpickr();
                            });">
                            <input {{ $isDisabled ? 'disabled' : '' }} type="text" x-ref="tanggalInput"
                                wire:model.live='tanggal' placeholder="{{ __('Pilih Tanggal') }} dan Waktu..."
                                readonly
                                class="w-full cursor-pointer input input-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs" />
                        </div>
                        <x-label-error :messages="$errors->get('tanggal')" />
                    </fieldset>

                    <x-form.searchable-select-advanced label="Dilaporkan Oleh" placeholder="Cari Nama Pelapor..."
                        modelsearch="searchPelapor" modelid="pelapor_id" {{-- ID asli di DB --}} :options="$pelapors"
                        :showdropdown="$showPelaporDropdown" {{-- Logic Manual --}} :manualMode="$manualPelaporMode"
                        manualModelName="manualPelaporName" enableManualAction="enableManualPelapor"
                        addManualAction="addPelaporManual" clickaction="selectPelapor" :disabled="$isDisabled" />
                </div>
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                    <fieldset class="mb-4 fieldset md:col-span-2" wire:key="field-immediate">

                        <x-form.label label="kondisi atau tindakan yang sudah dilakukan" required />
                        <div x-data="ckeditorHelper('immediate_corrective_action')" wire:ignore>
                            <div x-ref="editorElement"
                                data-placeholder="Masukkan kondisi atau tindakan yang sudah dilakukan..."></div>
                        </div>

                        <x-label-error :messages="$errors->get('immediate_corrective_action')" />
                    </fieldset>
                    <x-form.file-upload label="Lampirkan foto atau dokumentasi" model="new_doc_corrective"
                        :existingFile="$doc_corrective" :newFile="$new_doc_corrective" :isDisabled="$isDisabled" />

                </div>
                <fieldset class="p-3 my-4 border border-gray-200 shadow-md fieldset card bg-base-100">
                    <legend class="text-sm font-semibold card-title ">{{ __('Penanggung Jawab') }}</legend>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:mb-4 ">
                        {{-- workgroup --}}
                        <fieldset>
                            <input {{ $isDisabled ? 'disabled' : '' }} id="department" value="department"
                                wire:model="deptCont" class="peer/department radio radio-xs radio-accent"
                                type="radio" name="deptCont" checked />
                            <x-form.label for="department" class="peer-checked/department:text-accent text-[10px]"
                                label="PT. MSM & PT. TTN" required />
                            <input {{ $isDisabled ? 'disabled' : '' }} id="company" value="company"
                                wire:model="deptCont" class="peer/company radio radio-xs radio-primary"
                                type="radio" name="deptCont" />
                            <x-form.label for="company" class="peer-checked/company:text-primary" label="Kontraktor"
                                required />
                            <div class="hidden mt-2 peer-checked/department:block">
                                {{-- Department --}}
                                <div class="relative mb-1">
                                    <!-- Input Search -->

                                    <input {{ $isDisabled ? 'disabled' : '' }} type="text"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="{{ __('Cari departemen...') }}"
                                        class="w-full input input-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs " />
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
                            <div class="hidden mt-2 peer-checked/company:block">
                                {{-- Contractor --}}
                                <div class="relative mb-1">
                                    <!-- Input Search -->
                                    <input {{ $isDisabled ? 'disabled' : '' }} type="text"
                                        wire:model.live.debounce.300ms="searchContractor"
                                        placeholder="{{ __('Cari kontraktor...') }}"
                                        class="w-full input input-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs" />
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
                                @if ($deptCont === 'company')
                                    <x-label-error :messages="$errors->get('contractor_id')" />
                                @endif
                            </div>
                        </fieldset>
                        <fieldset class="fieldset">
                            <x-form.label label="PIC" required />
                            <select {{ $isDisabled ? 'disabled' : '' }} wire:model.live="penanggungJawab"
                                class="w-full select select-xs select-bordered focus-within:outline-none focus-within:border-info focus-within:ring-0">
                                <option value="">{{ __('-- Pilih --') }}</option>
                                @foreach ($penanggungJawabOptions as $pj)
                                    <option value="{{ $pj['id'] }}">{{ $pj['name'] }}</option>
                                @endforeach
                            </select>
                            <x-label-error :messages="$errors->get('penanggungJawab')" />
                        </fieldset>
                    </div>
                </fieldset>

                <fieldset class="p-3 my-4 border border-gray-200 shadow-md fieldset card bg-base-100">
                    <legend class="text-sm font-semibold card-title ">{{ __('Tindakan Lanjutan') }}</legend>

                    <!-- Deskripsi Tindakan -->
                    <fieldset class="fieldset md:col-span-1" wire:key="field-action">
                        <x-form.label label="Deskripsi Tindakan" required />
                        <div x-data="ckeditorHelper('action_description')" wire:ignore>
                            <div x-ref="editorElement" data-placeholder="Masukkan action deskripsi..."></div>
                        </div>
                        <x-label-error :messages="$errors->get('action_description')" />
                    </fieldset>
                    <div class="grid items-end grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Tanggal & Waktu -->
                        <fieldset class="fieldset md:col-span-1">
                            <x-form.label label="Batas Waktu Penyelesaian" />
                            <div class="relative" wire:ignore x-data="{
                                fp: null,
                                initFlatpickr() {
                                    if (this.fp) this.fp.destroy();
                                    this.fp = flatpickr(this.$refs.tanggalInput2, {
                                        disableMobile: true,
                                        enableTime: false,
                                        dateFormat: 'd-m-Y',
                                        onChange: (dates, str) => $wire.set('action_due_date', str),
                                    });
                                }
                            }" x-init="initFlatpickr();
                            Livewire.hook('message.processed', () => initFlatpickr());"
                                x-ref="wrapper">
                                <input {{ $isDisabled ? 'disabled' : '' }} name="action_due_date" type="text"
                                    x-ref="tanggalInput2" wire:model.live="action_due_date"
                                    placeholder="{{ __('Pilih Tanggal') }}"
                                    class="input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs {{ $errors->has('action_due_date') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"
                                    readonly />
                            </div>
                            <x-label-error :messages="$errors->get('action_due_date')" />
                        </fieldset>
                        <fieldset class="fieldset md:col-span-1">
                            <x-form.label label="Tanggal Penyelesaian Tindakan" />
                            <div class="relative" wire:ignore x-data="{
                                fp: null,
                                initFlatpickr() {
                                    if (this.fp) this.fp.destroy();
                                    this.fp = flatpickr(this.$refs.tanggalInput3, {
                                        disableMobile: true,
                                        enableTime: false,
                                        dateFormat: 'd-m-Y',
                                        onChange: (dates, str) => $wire.set('action_actual_close_date', str),
                                    });
                                }
                            }" x-init="initFlatpickr();
                            Livewire.hook('message.processed', () => initFlatpickr());"
                                x-ref="wrapper">
                                <input {{ $isDisabled ? 'disabled' : '' }} name="action_actual_close_date"
                                    type="text" x-ref="tanggalInput3" wire:model.live="action_actual_close_date"
                                    placeholder="{{ __('Pilih Tanggal') }}"
                                    class="input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs {{ $errors->has('action_actual_close_date') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"
                                    readonly />
                            </div>
                            <x-label-error :messages="$errors->get('action_actual_close_date')" />
                        </fieldset>
                        {{-- === TAMBAHAN: Final Doc Upload & Preview === --}}
                        <fieldset class="mt-4 fieldset">
                            <x-form.label label="Dokumen Final / Bukti Penyelesaian (Opsional)" />

                            {{-- Menampilkan data yang sudah diupload sebelumnya --}}
                            @if (!empty($existing_final_docs))
                                <div class="flex flex-col gap-2 mb-2 p-3 bg-gray-50 border rounded">
                                    <span
                                        class="text-xs font-semibold text-gray-700">{{ __('Dokumen Tersimpan:') }}</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($existing_final_docs as $file)
                                            <div
                                                class="flex items-center gap-2 p-2 text-xs bg-white border rounded shadow-sm">
                                                <svg class="w-4 h-4 text-blue-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                    </path>
                                                </svg>
                                                <a href="{{ asset('storage/' . $file) }}" target="_blank"
                                                    class="text-blue-600 hover:underline truncate max-w-[200px]">
                                                    {{ basename($file) }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="relative ">
                                {{-- Input untuk upload file baru --}}
                                <input type="file" wire:model.live="edit_action_final_doc"
                                    class="w-full file-input file-input-bordered file-input-xs" />

                                {{-- Loading state --}}
                                <div wire:loading.remove.class="hidden" wire:target="edit_action_final_doc"
                                    class="mt-1 hidden absolute inset-y-0 right-0">
                                    <span class="flex items-center gap-1">
                                        <span class="loading loading-spinner loading-xs text-info"></span>
                                        <span class="text-xs text-info">{{ __('Mengunggah...') }}</span>
                                    </span>
                                </div>
                            </div>
                            <x-label-error :messages="$errors->get('edit_action_final_doc')" />
                        </fieldset>
                        {{-- === END TAMBAHAN === --}}
                        <!-- Dilaporkan Oleh -->
                        <x-form.searchable-select-advanced label="PIC" placeholder="Cari Nama PIC..."
                            modelsearch="searchActResponsibility" modelid="action_responsible_id"
                            {{-- ID asli di DB --}} :options="$pelaporsAct" :showdropdown="$showActPelaporDropdown" {{-- Logic Manual --}}
                            :manualMode="$manualActPelaporMode" manualModelName="manualActPelaporName"
                            enableManualAction="enableManualActPelapor" addManualAction="addActPelaporManual"
                            clickaction="selectActPelapor" :disabled="$isDisabled" />
                    </div>
                    <!-- Tombol Tambah -->
                    <div class="flex justify-end ">
                        <flux:button size="xs" wire:click="addActionHazard"
                            class="{{ $isDisabled ? 'btn btn-disabled cursor-not-allowed' : '' }}"
                            icon:trailing="add-icon" variant="primary">{{ __('Tambah') }}</flux:button>
                    </div>
                    <!-- List Actions -->
                    <div class="my-2 divider">{{ __('Daftar Tindakan') }}</div>
                    <ul class="space-y-2">
                        @forelse($actionHazards as $act)
                            <li class="p-2 border rounded-md shadow-sm bg-base-100">
                                <div class="flex flex-col gap-2 md:flex-row md:justify-between">
                                    {{-- Bagian Kiri: Deskripsi & Lampiran Dokumen Final --}}
                                    <div
                                        class="w-full rounded md:max-w-96 xl:max-w-1/2 bg-base-200 p-2 flex flex-col justify-between gap-2">
                                        <div>
                                            <span class="font-semibold">{!! $act['description'] !!}</span>
                                        </div>

                                        {{-- === TAMPILKAN DATA FINAL DOC YANG DIUPLOAD === --}}
                                        @if (!empty($act['final_doc']))
                                            <div class="mt-1">
                                                <span
                                                    class="text-[10px] font-bold text-gray-500 block mb-1">{{ __('Lampiran Dokumen Final:') }}</span>
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach ($act['final_doc'] as $file)
                                                        <a href="{{ asset('storage/' . $file) }}" target="_blank"
                                                            class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition shadow-sm">
                                                            {{-- Icon Clip Kertas --}}
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12"
                                                                height="12" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2.5"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="lucide lucide-paperclip">
                                                                <path
                                                                    d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                                            </svg>
                                                            <span
                                                                class="truncate max-w-[150px]">{{ basename($file) }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Bagian Kanan: Batas Waktu, Tanggal Selesai, PIC & Tombol Aksi --}}
                                    <div class="flex flex-col gap-1 md:flex-row md:items-center">
                                        <span class="text-[9px] badge badge-primary badge-outline">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-clock-check">
                                                <path d="M12 6v6l4 2" />
                                                <path d="M22 12a10 10 0 1 0-11 9.95" />
                                                <path d="m22 16-5.5 5.5L14 19" />
                                            </svg>
                                            {{ __('Batas Waktu:') }}
                                            {{ $act['due_date'] ? \Carbon\Carbon::parse($act['due_date'])->timezone('Asia/Makassar')->format('d-m-Y') : '' }}
                                        </span>

                                        <span class="text-[9px] badge badge-info badge-outline">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-clock-check">
                                                <path d="M12 6v6l4 2" />
                                                <path d="M22 12a10 10 0 1 0-11 9.95" />
                                                <path d="m22 16-5.5 5.5L14 19" />
                                            </svg>
                                            {{ __('Tgl Selesai:') }}
                                            {{ $act['actual_close_date'] ? \Carbon\Carbon::parse($act['actual_close_date'])->timezone('Asia/Makassar')->format('d-m-Y') : '-' }}
                                        </span>

                                        <span class="text-[9px] badge badge-success badge-outline">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-user-check">
                                                <path d="m16 11 2 2 4-4" />
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                            </svg>
                                            {{ __('PIC') }}:
                                            {{ optional(\App\Models\User::find($act['responsible_id']))->name ?? '-' }}
                                        </span>

                                        <div class="flex gap-2 mt-1 md:mt-0">
                                            <flux:button variant="subtle" size="xs"
                                                class="{{ $isDisabled ? 'btn btn-disabled cursor-not-allowed' : '' }}"
                                                wire:click="loadEditAction({{ $act['id'] }})"
                                                icon="pencil-square">
                                            </flux:button>

                                            <flux:button variant="danger" size="xs"
                                                class="{{ $isDisabled ? 'btn btn-disabled cursor-not-allowed' : '' }}"
                                                wire:click="removeAction({{ $act['id'] }})"
                                                wire:confirm="{{ __('Yakin hapus tindakan ini?') }}" icon="trash">
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="p-2 border rounded-md shadow-sm bg-base-100">
                                <p class="text-sm text-center text-gray-500">
                                    {{ __('Belum ada tindakan yang ditambahkan.') }}</p>
                            </li>
                        @endforelse
                    </ul>

                </fieldset>

                <div class="flex flex-col-reverse gap-2 my-2 md:flex-row">

                    {{-- Kolom Likelihood & Consequence --}}
                    <div class="space-y-4 md:grow">
                        {{-- Consequence --}}
                        <fieldset class="fieldset ">
                            <x-form.label label="Consequence" required />
                            <select {{ $isDisabled ? 'disabled' : '' }} wire:model.live="consequence_id"
                                class="w-full select select-xs md:select-xs select-bordered md:max-w-md focus-within:outline-none focus-within:border-info focus-within:ring-0">
                                <option value="">{{ __('-- Pilih --') }}</option>
                                @foreach ($consequencess as $cons)
                                    <option value="{{ $cons->id }}">{{ __($cons->name) }}</option>
                                @endforeach
                            </select>
                            <x-label-error :messages="$errors->get('consequence_id')" />

                            @if ($consequence_id)
                                @php
                                    $selectedConsequence = $consequencess->firstWhere('id', $consequence_id);
                                @endphp
                                @if ($selectedConsequence)
                                    <div
                                        class="h-20 p-2 mt-1 overflow-y-auto text-sm border rounded text-base-content bg-base-100">
                                        {{ __($selectedConsequence->description) ?? 'Tidak ada deskripsi' }}
                                    </div>
                                @endif
                            @endif
                        </fieldset>
                        {{-- Likelihood --}}
                        <fieldset class="fieldset ">
                            <x-form.label label="Likelihood" required />
                            <select {{ $isDisabled ? 'disabled' : '' }} wire:model.live="likelihood_id"
                                class="w-full select select-xs md:select-xs select-bordered md:max-w-md focus-within:outline-none focus-within:border-info focus-within:ring-0">
                                <option value="">{{ __('-- Pilih --') }}</option>
                                @foreach ($likelihoodss as $like)
                                    <option value="{{ $like->id }}">{{ __($like->name) }}</option>
                                @endforeach
                            </select>
                            <x-label-error :messages="$errors->get('likelihood_id')" />

                            @if ($likelihood_id)
                                @php
                                    $selectedLikelihood = $likelihoodss->firstWhere('id', $likelihood_id);
                                @endphp
                                @if ($selectedLikelihood)
                                    <div
                                        class="h-20 p-2 mt-1 overflow-y-auto text-sm border rounded text-base-content bg-base-100">
                                        {{ __($selectedLikelihood->description) ?? 'Tidak ada deskripsi' }}
                                    </div>
                                @endif
                            @endif
                        </fieldset>


                    </div>

                    {{-- Kolom Risk Matrix --}}
                    <div class="flex-none overflow-x-auto ">
                        <table class="table table-xs w-60">
                            <thead>
                                <tr class="text-center text-[9px]">
                                    {{-- Menterjemahkan Header Statis --}}
                                    <td class=" border-1">{{ __('Level') }}</td>
                                    <td class="rotate_text border-1 bg-emerald-500">{{ __('Rendah') }}</td>
                                    <td class="bg-yellow-500 rotate_text border-1">{{ __('Sedang') }}</td>
                                    <td class="bg-orange-500 rotate_text border-1">{{ __('Tinggi') }}</td>
                                    <td class="rotate_text border-1 bg-rose-500">{{ __('Ekstrem') }}</td>
                                    <td class="bg-gray-100 rotate_text border-1">{{ __('Ditutup') }}</td>
                                </tr>
                                <tr class="text-center text-[9px]">
                                    <th class="border-1">Likelihood ↓ / Consequence →</th>
                                    @foreach ($consequences as $c)
                                        {{-- Menterjemahkan Nama Konsekuensi dari DB --}}
                                        <th class="rotate_text border-1">{{ __($c->name) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($likelihoods as $l)
                                    <tr class="text-center text-[9px]">
                                        {{-- Menterjemahkan Nama Likelihood dari DB --}}
                                        <td class="w-1 font-bold border-1">{{ __($l->name) }}</td>

                                        @foreach ($consequences as $c)
                                            @php
                                                $cell =
                                                    App\Models\RiskMatrixCell::where('likelihood_id', $l->id)
                                                        ->where('risk_consequence_id', $c->id)
                                                        ->first() ?? null;

                                                $severity = $cell?->severity ?? '';

                                                $color = match ($severity) {
                                                    'Rendah' => 'bg-emerald-500',
                                                    'Sedang' => 'bg-yellow-500',
                                                    'Tinggi' => 'bg-orange-500',
                                                    'Ekstrem' => 'bg-rose-500',
                                                    default => 'bg-gray-100',
                                                };
                                            @endphp
                                            <td
                                                class=" cursor-pointer @if ($likelihood_id == $l->id && $consequence_id == $c->id) border-2 bg-primary border-primary-content @endif">
                                                <span wire:click="edit({{ $l->id }}, {{ $c->id }})"
                                                    class="btn btn-square btn-xs {{ $isDisabled ? 'btn btn-disabled' : "$color" }}">
                                                    {{-- Mengambil inisial dari hasil terjemahan (misal: "R" -> "L" untuk Low) --}}
                                                    {{ Str::upper(substr(__($severity), 0, 1)) }}
                                                </span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
                @if ($RiskAssessment != null)
                    <table class="table mb-4 table-xs">

                        <tr>
                            <th class="w-40 text-xs border border-slate-200">Potential Risk Rating</th>
                            <td class="pl-2 text-xs border border-slate-200">
                                {{ __($RiskAssessment->name) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-40 text-xs border border-slate-200">Notify</th>
                            <td class="pl-2 text-xs border border-slate-200">
                                {{ __($RiskAssessment->reporting_obligation) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-40 text-xs border border-slate-200">Deadline</th>
                            <td class="pl-2 text-xs border border-slate-200">{{ __($RiskAssessment->notes) }}</td>
                        </tr>
                        <tr>
                            <th class="w-40 text-xs border border-slate-200">Coordinator</th>
                            <td class="pl-2 text-xs border border-slate-200">
                                {{ __($RiskAssessment->coordinator) }}
                            </td>
                        </tr>


                    </table>
                @endif
                @if ($hazard->isModerator() || !empty($hazard->moderator_comment))
                    @if ($hazard->isModerator())
                        {{-- Mode Edit: Jika dia moderator, tampilkan CKEditor --}}
                        <div x-data="ckeditorHelper('moderator_comment')" wire:ignore>
                            <div x-ref="editorElement" data-placeholder="Masukkan komentar moderator..."></div>
                        </div>
                    @else
                        {{-- Mode Read-Only: Jika bukan moderator tapi komentar ada, tampilkan teks saja --}}
                        <div class="p-3 prose bg-gray-100 rounded-lg max-w-none">
                            {!! $hazard->moderator_comment !!}
                        </div>
                    @endif
                @endif
            </div>
        </x-tab-hazard.layout>
    </form>
    <!-- Modal Edit ActionHazard -->
    <div x-data="{ open: false }" x-on:open-edit-action.window="open = true" x-show="open" x-transition
        class="fixed inset-0 z-50 flex items-center justify-center modal modal-open bg-black/50"
        style="display:none;">
        <div class="w-11/12 max-w-4xl modal-box !overflow-visible">
            <h3 class="mb-4 text-lg font-bold">{{ __('Edit Tindakan Lanjutan') }}</h3>

            {{-- === Form Update === --}}

            <fieldset class="fieldset md:col-span-1" wire:key="field-edit_action">
                <x-form.label label="Deskripsi Tindakan" required />
                <div x-data="ckeditorHelper('edit_action_description')" wire:ignore>
                    <div x-ref="editorElement" data-placeholder="Masukkan deskripsi tindakan..."></div>
                </div>
                <x-label-error :messages="$errors->get('edit_action_description')" />
            </fieldset>
            <div class="grid items-end grid-cols-1 gap-4 mt-4 md:grid-cols-3">
                {{-- Batas Waktu --}}
                <fieldset class="fieldset">
                    <x-form.label label="Batas Waktu Penyelesaian" required />
                    <div class="relative" wire:ignore x-data="{
                        fp: null,
                        initFlatpickr() {
                            if (this.fp) this.fp.destroy();
                            this.fp = flatpickr(this.$refs.dueEdit, {
                                disableMobile: true,
                                dateFormat: 'd-m-Y',
                                onChange: (dates, str) => $wire.set('edit_action_due_date', str),
                            });
                        }
                    }" x-init="initFlatpickr();
                    Livewire.hook('message.processed', () => initFlatpickr());
                    
                    // ==== Tambahan: isi ulang saat modal dibuka ====
                    Livewire.on('open-edit-action', () => {
                        // Ambil value terbaru dari Livewire
                        const val = @this.get('edit_action_due_date');
                        // setDate akan menyesuaikan input + kalender
                        if (val && this.fp) {
                            this.fp.setDate(val, true, 'd-m-Y');
                        }
                    });"
                        x-ref="wrapper">
                        <input type="text" x-ref="dueEdit" wire:model.live="edit_action_due_date"
                            class="w-full input input-bordered input-xs" placeholder="{{ __('Pilih Tanggal') }}"
                            readonly />
                    </div>
                    <x-label-error :messages="$errors->get('edit_action_due_date')" />
                </fieldset>


                {{-- Actual Close Date --}}
                <fieldset class="fieldset">
                    <x-form.label label="Tanggal Penyelesaian Tindakan" required />
                    <div class="relative" wire:ignore x-data="{
                        fp: null,
                        initFlatpickr() {
                            if (this.fp) this.fp.destroy();
                            this.fp = flatpickr(this.$refs.closeEdit, {
                                disableMobile: true,
                                dateFormat: 'd-m-Y',
                    
                                onChange: (dates, str) => $wire.set('edit_action_actual_close_date', str),
                            });
                        }
                    }" x-init="initFlatpickr();
                    Livewire.hook('message.processed', () => initFlatpickr());"
                        x-ref="wrapper">
                        <input type="text" x-ref="closeEdit" wire:model.live="edit_action_actual_close_date"
                            class="w-full input input-bordered input-xs" placeholder="{{ __('Pilih Tanggal') }}"
                            readonly />
                    </div>
                    <x-label-error :messages="$errors->get('edit_action_actual_close_date')" />
                </fieldset>
                {{-- === TAMBAHAN: Final Doc Upload & Preview === --}}
                <fieldset class="mt-4 fieldset">
                    <x-form.label label="Dokumen Final / Bukti Penyelesaian (Opsional)" />

                    {{-- Menampilkan data yang sudah diupload sebelumnya --}}
                    @if (!empty($existing_final_docs))
                        <div class="flex flex-col gap-2 mb-2 p-3 bg-gray-50 border rounded">
                            <span class="text-xs font-semibold text-gray-700">{{ __('Dokumen Tersimpan:') }}</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($existing_final_docs as $file)
                                    <div class="flex items-center gap-2 p-2 text-xs bg-white border rounded shadow-sm">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                            </path>
                                        </svg>
                                        <a href="{{ asset('storage/' . $file) }}" target="_blank"
                                            class="text-blue-600 hover:underline truncate max-w-[200px]">
                                            {{ basename($file) }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="relative ">
                        {{-- Input untuk upload file baru --}}
                        <input type="file" wire:model.live="edit_action_final_doc"
                            class="w-full file-input file-input-bordered file-input-xs" />

                        {{-- Loading state --}}
                        <div wire:loading.remove.class="hidden" wire:target="edit_action_final_doc"
                            class="mt-1 hidden absolute inset-y-0 right-0">
                            <span class="flex items-center gap-1">
                                <span class="loading loading-spinner loading-xs text-info"></span>
                                <span class="text-xs text-info">{{ __('Mengunggah...') }}</span>
                            </span>
                        </div>
                    </div>
                    <x-label-error :messages="$errors->get('edit_action_final_doc')" />
                </fieldset>
                {{-- === END TAMBAHAN === --}}


                {{-- Responsible Person --}}
                <x-form.searchable-select-advanced label="PIC" placeholder="Cari Nama Pelapor..."
                    modelsearch="searchActResponsibilityEdit" modelid="action_responsible_id" {{-- ID asli di DB --}}
                    :options="$pelaporsActEdit" :showdropdown="$showActPelaporDropdownEdit" {{-- Logic Manual --}} :manualMode="$manualActPelaporModeEdit"
                    manualModelName="manualActPelaporNameEdit" enableManualAction="manualActPelaporModeEdit"
                    addManualAction="addActPelaporManualEdit" clickaction="selectActPelaporEdit" :disabled="$isDisabled" />
            </div>

            <!-- Aksi -->
            <div class="flex justify-end gap-2 mt-4 modal-action">
                <!-- Update tidak menutup modal -->
                <flux:button variant="primary" size="xs" type="button" wire:click="updateAction"
                    x-on:click="$wire.call('updateAction').then(() => { open = false })">
                    Update
                </flux:button>
                <!-- Batal -->
                <flux:button variant="outline" size="xs" type="button" x-on:click="open = false">
                    {{ __('Batal') }}
                </flux:button>

            </div>
        </div>
    </div>


    <dialog id="my_modal_5" class="modal" wire:ignore.self>
        <div class="w-11/12 max-w-2xl modal-box">
            <div class="p-2">
                <h3 class="mb-4 text-lg font-bold">Diskusi Hazard</h3>

                @php
                    $isModerator = $hazard->isModerator();
                    // Cek apakah sudah ada pesan dari moderator di percakapan ini
                    $hasModeratorChatted = $hazard->chats->contains(function ($chat) use ($hazard) {
                        return $hazard->isModerator($chat->user_id);
                    });
                @endphp

                {{-- Tambahkan wire:poll agar status centang dan pesan baru terupdate otomatis --}}
                <div class="p-4 mb-6 space-y-4 overflow-y-auto border max-h-96 rounded-xl bg-base-200/50" wire:poll.5s>
                    @forelse($hazard->chats as $chat)
                        @php
                            $isMe = $chat->user_id === auth()->id();
                            $isSenderModerator = $hazard->isModerator($chat->user_id);
                        @endphp

                        <div class="chat {{ $isMe ? 'chat-end' : 'chat-start' }}">
                            <div class="chat-image avatar">
                                <div
                                    class="w-8 rounded-full ring ring-offset-base-100 ring-offset-2 {{ $isSenderModerator ? 'ring-info' : 'ring-primary' }}">
                                    <img
                                        src="https://ui-avatars.com/api/?name={{ urlencode($chat->user->name) }}&background=random" />
                                </div>
                            </div>
                            <div class="chat-header">
                                {{ $chat->user->name }}
                                <time class="text-xs opacity-50 ml-1">{{ $chat->created_at->diffForHumans() }}</time>
                            </div>
                            <div
                                class="chat-bubble {{ $isSenderModerator ? 'chat-bubble-info' : 'chat-bubble-ghost border' }}">
                                {{ $chat->message }}
                            </div>

                            {{-- Implementasi Chat Footer dengan Status Centang --}}
                            <div class="mt-1 text-[10px] opacity-50 chat-footer flex items-center gap-1">
                                {{ $isSenderModerator ? '🛡️ Moderator' : '👤 Pelapor' }}

                                @if ($isMe)
                                    @if ($chat->read_at)
                                        <span class="text-info flex"
                                            title="Dibaca pada {{ $chat->read_at->format('d M H:i') }}">
                                            {{-- Centang Biru --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-check-check-icon lucide-check-check">
                                                <path d="M18 6 7 17l-5-5" />
                                                <path d="m22 10-7.5 7.5L13 16" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="flex" title="Terkirim"> {{-- Centang Abu-abu --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center opacity-50">
                            <p>Belum ada diskusi.</p>
                        </div>
                    @endforelse
                </div>

                @if ($isModerator || $hasModeratorChatted)
                    <div class="flex gap-2">
                        <x-form.text_area
                            label="{{ $isModerator ? 'Mulai diskusi sebagai moderator' : 'Tulis balasan' }}"
                            model="newMessage"
                            placeholder="{{ $isModerator ? 'Mulai diskusi sebagai moderator...' : 'Tulis balasan...' }}"
                            wire:keydown.enter="sendMessage" />
                    </div>
                @else
                    <div class="text-sm italic shadow-sm alert alert-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 stroke-current shrink-0"
                            fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Hanya moderator yang dapat memulai diskusi ini.</span>
                    </div>
                @endif
            </div>

            <div class="modal-action">
                <button wire:click="sendMessage" class="btn btn-xs btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.add.class='hidden' wire:target='sendMessage'>Kirim</span>
                    <span class='hidden loading loading-spinner loading-xs' wire:loading.remove.class="hidden"></span>
                </button>
                <button wire:loading.add.class='btn-hidden' wire:target='sendMessage' onclick="my_modal_5.close()"
                    class="btn btn-xs btn-error">Tutup</button>
            </div>
        </div>
    </dialog>
    @push('scripts')
        <script>
            // 1. Mendengarkan sinyal dari PHP ($this->dispatch)
            window.addEventListener('scroll-bottom', () => {
                scrollToBottom();
            });

            // 2. Fungsi umum untuk scroll
            function scrollToBottom() {
                // Gunakan ID agar lebih spesifik dan tidak salah pilih container lain
                const container = document.querySelector('.overflow-y-auto.max-h-96');
                if (container) {
                    container.scrollTo({
                        top: container.scrollHeight,
                        behavior: 'smooth' // Efek scroll halus seperti WhatsApp
                    });
                }
            }

            // 3. Khusus untuk Modal DaisyUI / HTML5 Dialog
            // Kita gunakan MutationObserver atau cek saat modal terbuka
            const modal = document.getElementById('my_modal_5');

            // Jika menggunakan <dialog>, event-nya adalah 'toggle' atau kita pantau atribut 'open'
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'open' && modal.open) {
                        setTimeout(scrollToBottom, 200);
                    }
                });
            });

            if (modal) {
                observer.observe(modal, {
                    attributes: true
                });
            }
        </script>
    @endpush
</section>
