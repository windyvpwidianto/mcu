 <dialog id="action_modal" class="modal {{ $showActionModal === 'open' ? 'modal-open' : '' }}">
     <div class="w-11/12 max-w-5xl modal-box">
         <form method="dialog">
             <button wire:click="$set('showActionModal', 'close')"
                 class="absolute btn btn-sm btn-circle btn-ghost right-2 top-2">✕</button>
         </form>

         <h3 class="mb-4 text-lg font-bold">{{ __('Manajemen Tindakan Lanjutan') }}</h3>

         <fieldset class="p-3 border rounded-xl border-base-300 bg-base-100">
             <fieldset class="fieldset md:col-span-1" wire:key="field-action">
                 <x-form.label label="Deskripsi Tindakan" required />
                 <div x-data="ckeditorHelper('action_description')" wire:ignore>
                     <div x-ref="editorElement" data-placeholder="{{ __('Masukkan deskripsi tindakan...') }}"></div>
                 </div>

                 <x-label-error :messages="$errors->get('action_description')" />
             </fieldset>

             <div class="grid items-end grid-cols-1 gap-4 mt-4 md:grid-cols-3">
                 <x-form.tgl label="Batas Waktu" format="d-m-Y" model="action_due_date" :required="true"
                     placeholder="{{ __('Pilih Tanggal') }}" />

                 <x-form.tgl label="Tanggal Selesai" format="d-m-Y" model="actual_close_date" :required="true"
                     placeholder="{{ __('Pilih Tanggal') }}" />

                 <x-form.searchable-select-advanced label="{{ __('PIC') }}" placeholder="Cari Nama PIC..."
                     modelsearch="searchActResponsibility" modelid="action_responsible_id" {{-- ID asli di DB --}}
                     :options="$pelaporsAct" :showdropdown="$showActPelaporDropdown" {{-- Logic Manual --}} :manualMode="$manualActPelaporMode"
                     manualModelName="manualActPelaporName" enableManualAction="enableManualActPelapor"
                     addManualAction="addActPelaporManual" clickaction="selectActPelapor" />
             </div>
             {{-- === TAMBAHAN: Final Doc Upload === --}}
             <fieldset class="mt-4 fieldset">
                 <x-form.label label="Dokumen Final / Bukti Penyelesaian (Opsional)" />
                 <div class="relative ">
                     <input type="file" wire:model="action_final_doc"
                         class="w-full file-input file-input-bordered file-input-xs" />
                     {{-- Loading state --}}
                     <div wire:loading.remove.class="hidden" wire:target="action_final_doc"
                         class="mt-1 absolute inset-y-0 right-0 hidden">
                         <span class="flex items-center gap-1">
                             <span class="loading loading-spinner loading-xs text-success"></span>
                             <span class="text-xs text-success">{{ __('Mengunggah file...') }}</span>
                         </span>
                     </div>
                 </div>

                 <x-label-error :messages="$errors->get('action_final_doc')" />
             </fieldset>
             {{-- === END TAMBAHAN === --}}

             <div class="flex justify-end mt-4">
                 <label wire:click='addAction' wire:loading.add.class='btn-disable' wire:target="addAction"
                     class="btn btn-xs btn-success">
                     <span wire:loading.remove.class="hidden" wire:target="addAction"
                         class="hidden loading loading-spinner loading-xs"></span>
                     {{ __('Tambah ke Daftar') }}
                 </label>
             </div>
         </fieldset>

     </div>
 </dialog>
