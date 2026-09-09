{{-- SECTION DOKUMENTASI --}}
<fieldset class="p-3 mt-4 border shadow-md border-base-300 fieldset card bg-base-100">
    <legend class="text-sm font-semibold card-title">{{ __('Dokumentasi') }}</legend>
    <div class="grid grid-cols-1 gap-6 mb-4 md:grid-cols-2">

        {{-- Bukti Visual --}}
        <div class="space-y-2">
            <x-form.upload label="Lampirkan Bukti Visual" model="visual_evidence" title="Pilih Gambar" required
                keterangan="Bisa pilih > 1 foto (JPG, PNG)" :file="$visual_evidence" multiple />
            {{-- TAMPILKAN PESAN ERROR DI SINI --}}

            <div wire:loading.remove wire:target="visual_evidence" class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-5">
                @if($visual_evidence)
                @foreach($visual_evidence as $index => $image)
                <div class="relative aspect-square group">
                    @php
                    $extension = strtolower($image->getClientOriginalExtension());
                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    @endphp

                    @if($isImage)
                    <img src="{{ $image->temporaryUrl() }}" class="object-cover w-full h-full border rounded-lg shadow-sm" />
                    @else
                    <div class="flex flex-col items-center justify-center w-full h-full border rounded-lg bg-base-200">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6z" />
                        </svg>
                        <span class="text-[8px] px-1 truncate w-full text-center">{{ $image->getClientOriginalName() }}</span>
                    </div>
                    @endif

                    <button type="button" wire:click="removeFile('visual_evidence', {{ $index }})"
                        class="absolute flex items-center justify-center w-6 h-6 font-bold text-white rounded-full shadow-md -top-2 -right-2 bg-error">✕</button>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        {{-- Dokumen Pendukung --}}
        <div class="space-y-2">
            <x-form.upload label="Lampirkan Dokumen Pendukung" model="supporting_documents" title="Pilih Dokumen" required
                keterangan="PDF atau Word" :file="$supporting_documents" multiple />

            <div wire:loading.remove wire:target="supporting_documents" class="space-y-2">
                @if($supporting_documents)
                @foreach($supporting_documents as $index => $doc)
                @php $docExt = strtolower($doc->getClientOriginalExtension()); @endphp
                <div class="flex items-center justify-between p-2 border border-dashed rounded-lg bg-base-50 border-base-300">
                    <div class="flex items-center gap-2 overflow-hidden">
                        @if($docExt == 'pdf') <x-icon.pdf class="flex-shrink-0 w-5 h-5" />
                        @elseif(in_array($docExt, ['doc', 'docx'])) <x-icon.word class="flex-shrink-0 w-5 h-5" />
                        @else <x-icon.document class="flex-shrink-0 w-5 h-5" /> @endif
                        <span class="text-xs font-medium truncate">{{ $doc->getClientOriginalName() }}</span>
                    </div>
                    <button type="button" wire:click="removeFile('supporting_documents', {{ $index }})" class="btn btn-ghost btn-xs text-error">✕</button>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
</fieldset>

{{-- SECTION TINDAKAN PERBAIKAN --}}
<fieldset class="p-3 my-4 border shadow-md border-base-300 fieldset card bg-base-100">
    <legend class="text-sm font-semibold card-title">{{ __('Tindakan Perbaikan') }}</legend>

    <div class="flex items-center justify-between pb-2 mb-4 border-b">
        <h3 class="text-xs font-bold uppercase md:text-sm text-primary">{{ __('Rencana Perbaikan Jangka Panjang') }}</h3>
        <button type="button" wire:click="addCorrectiveRow" class="btn btn-primary btn-xs sm:btn-sm">
            + {{ __('Tambah') }}
        </button>
    </div>

    {{-- VIEW MOBILE: Tampil di HP (Card Mode) --}}
    <div class="grid grid-cols-1 gap-4 md:hidden">
        @foreach($corrective_actions as $index => $action)
        {{-- Key Utama untuk baris --}}
        <div wire:key="corrective-row-{{ $index }}-{{ count($corrective_actions) }}" class="relative p-4 border rounded-xl bg-base-50">

            @if(count($corrective_actions) > 1)
            <button type="button" wire:click="removeCorrectiveRow({{ $index }})"
                class="absolute top-2 right-2 btn btn-circle btn-ghost btn-xs text-error">✕</button>
            @endif

            <div class="flex flex-col gap-3 mt-2">
                {{-- Tambahkan wire:key di tiap komponen input agar state teks & select tidak hilang --}}
                <div wire:key="field-desc-{{ $index }}">
                    <x-form.text_area label="Rencana Perbaikan"
                        model="corrective_actions.{{ $index }}.action_description" rows="2" />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div wire:key="field-hierarchy-{{ $index }}">
                        <x-form.select label="Kontrol Hirarki"
                            model="corrective_actions.{{ $index }}.control_hierarchy"
                            :options="[['id'=>'Eliminasi','name'=>'Eliminasi'],['id'=>'Substitusi','name'=>'Substitusi'],['id'=>'Engineering','name'=>'Rekayasa'],['id'=>'Administrasi','name'=>'Admin'],['id'=>'APD','name'=>'APD']]" />
                    </div>

                    <div wire:key="field-deadline-{{ $index }}">
                        <x-form.tgl-waktu label="Deadline"
                            model="corrective_actions.{{ $index }}.due_date" />
                    </div>
                </div>

                {{-- PIC Searchable Select (Sangat Krusial memiliki wire:key) --}}
                <div wire:key="field-pic-search-{{ $index }}">
                    <x-form.searchable-select-advanced
                        label="Person In Charge (PIC)"
                        modelsearch="searchPetugas.{{ $index }}"
                        modelid="corrective_actions.{{ $index }}.pic_user_id"
                        :options="$pelaporsAct"
                        :showdropdown="$showDropdownPetugas[$index] ?? false"
                        clickaction="selectActPelapor" />
                </div>

                <div class="p-3 border rounded-lg bg-white/50" wire:key="realization-box-{{ $index }}">
                    <x-form.tgl-waktu label="Tanggal Realisasi Selesai"
                        model="corrective_actions.{{ $index }}.actual_completion_date" />

                    {{-- Mobile Status Indicators --}}
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        @if(!empty($action['due_date']) && !empty($action['actual_completion_date']))
                        @php
                        $isOverdue = \Carbon\Carbon::parse($action['actual_completion_date'])
                        ->greaterThan(\Carbon\Carbon::parse($action['due_date']));
                        @endphp
                        <span class="badge {{ $isOverdue ? 'badge-error' : 'badge-success' }} badge-sm font-bold">
                            {{ $isOverdue ? 'OVERDUE' : 'ON TIME' }}
                        </span>
                        @endif

                        @if(!empty($action['actual_completion_date']))
                        <span class="font-bold badge badge-info badge-outline badge-sm">100% DONE</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- VIEW DESKTOP: Tampil di Tablet/Laptop (Table Mode) --}}
    <div class="hidden overflow-x-auto md:block">
        <table class="table w-full table-compact">
            <thead>
                <tr class="text-[11px] uppercase bg-base-200">
                    <th class="rounded-l-lg">Rencana Perbaikan</th>
                    <th>Hirarki</th>
                    <th>PIC</th>
                    <th>Batas Waktu</th>
                    <th>Tgl. Selesai</th>
                    <th class="rounded-r-lg"></th>
                </tr>
            </thead>
            <tbody class="text-xs">
                @foreach($corrective_actions as $index => $action)
                <tr wire:key="corrective-desktop-{{ $index }}-{{ count($corrective_actions) }}" class="hover:bg-base-50">
                    <td class="w-1/4 align-top">
                        <x-form.text_area model="corrective_actions.{{ $index }}.action_description" rows="2" />
                    </td>
                    <td class="w-1/5 align-top">
                        <x-form.select model="corrective_actions.{{ $index }}.control_hierarchy"
                            :options="[['id'=>'Eliminasi','name'=>'Eliminasi'],['id'=>'Substitusi','name'=>'Substitusi'],['id'=>'Engineering','name'=>'Rekayasa'],['id'=>'Administrasi','name'=>'Admin'],['id'=>'APD','name'=>'APD']]" />
                    </td>
                    <td class="w-1/5 align-top">
                        <x-form.searchable-select-advanced modelsearch="searchPetugas.{{ $index }}"
                            modelid="corrective_actions.{{ $index }}.pic_user_id" :options="$pelaporsAct"
                            :showdropdown="$showDropdownPetugas[$index] ?? false" clickaction="selectActPelapor" />
                    </td>
                    <td class="align-top">
                        <x-form.tgl-waktu model="corrective_actions.{{ $index }}.due_date" />
                    </td>
                    <td class="align-top">
                        <x-form.tgl-waktu model="corrective_actions.{{ $index }}.actual_completion_date" />
                        {{-- Desktop Indicators (Icon-only to save space) --}}
                        <div class="flex gap-1 mt-1">
                            @if(!empty($action['due_date']) && !empty($action['actual_completion_date']))
                            @php $isOverdue = \Carbon\Carbon::parse($action['actual_completion_date'])->greaterThan(\Carbon\Carbon::parse($action['due_date'])); @endphp
                            <div class="tooltip" data-tip="{{ $isOverdue ? 'Overdue' : 'Tepat Waktu' }}">
                                <span class="{{ $isOverdue ? 'text-error' : 'text-success' }}">
                                    @if($isOverdue) ⚠️ @else ✅ @endif
                                </span>
                            </div>
                            @endif
                            @if(!empty($action['actual_completion_date']))
                            <span class="badge badge-success badge-outline text-[8px] h-3 px-1">DONE</span>
                            @endif
                        </div>
                    </td>
                    <td class="align-top">
                        @if(count($corrective_actions) > 1)
                        <button type="button" wire:click="removeCorrectiveRow({{ $index }})" class="btn btn-ghost btn-xs text-error">✕</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</fieldset>