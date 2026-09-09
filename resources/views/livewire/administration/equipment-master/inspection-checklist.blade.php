<section class="w-full">

    <x-toast />

    <x-tabs-equipment.layout heading="Inspection Checklist"

        subheading="Manage inspection checklists for various equipment types and locations.">

        @if (session()->has('message'))

        <div class="p-2 mb-4 bg-green-200">{{ session('message') }}</div>

        @endif

        <div

            class="md:shadow-md md:px-4 md:mb-10 md:absolute md:inset-x-0 md:top-0 md:z-20 md:flex-row bg-base-100 md:inset-shadow-sm">

            <button class=" btn btn-primary btn-soft btn-xs" onclick="checklist_modal.showModal()"

                wire:click="resetForm">

                + Tambah Checklist

            </button>

        </div>



        <table class="table mt-4 table-zebra table-xs">

            <thead>

                <tr class="">

                    <th class="">Type</th>

                    <th class="">Location</th>
                    <th class="">Location Specific</th>

                    <th class="">Inputs/Checks</th>

                    <th class="">Action</th>

                </tr>

            </thead>

            <tbody>

                @foreach ($checklists as $item)

                <tr>

                    <td class="">{{ $item->equipment_type }}</td>

                    <td class="">{{ $item->location_keyword }}</td>
                    <td class="">{{ $item->location_specific }}</td>

                    <td class="text-xs ">

                        <strong>Inputs:</strong> {{ implode(', ', $item->inputs) }} <br>

                        <strong>Checks:</strong>
                        @foreach($item->checks as $c)
                        @if(is_array($c))
                        <span class="badge badge-ghost badge-xs">{{ $c['name'] }} ({{ $c['type'] }})</span>
                        @else
                        <span class="badge badge-ghost badge-xs">{{ $c }} (checkbox)</span>
                        @endif
                        @endforeach

                    </td>

                    <td class="">

                        <button onclick="checklist_modal.showModal()" wire:click="edit({{ $item->id }})"

                            class=" btn btn-xs btn-soft btn-warning">Edit</button> |

                        <button wire:confirm="Yakin hapus?" wire:click="delete({{ $item->id }})"

                            class="btn btn-xs btn-soft btn-error">Hapus</button>

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>



        <dialog id="checklist_modal" class="modal" wire:ignore.self>

            <div class="flex flex-col w-11/12 max-w-3xl p-0 modal-box">

                {{-- p-0 digunakan agar scrollbar rapat ke pinggir --}}



                <div class="p-6 pb-2">

                    <h3 class="text-lg font-bold">{{ $checklist_id ? 'Edit' : 'Tambah' }} Checklist</h3>

                    <p class="text-sm text-gray-500">Kelola input dan poin pemeriksaan peralatan.</p>

                </div>



                <div class="p-6 pt-2 overflow-y-auto max-h-[70vh]">

                    <div class="grid grid-cols-3 gap-4 mb-4">


                        <x-form.input-text label="Equipment Type" model="equipment_type" placeholder="Equipment Type" required />
                        <x-form.searchable-dropdown label="Location Keyword" required modelsearch="searchLocation"
                            modelid="location_id" :options="$locations" :showdropdown="$show_location" clickaction="selectLocation"
                            namedb="name" />
                        <x-form.input-text label="Location Specific" model="location_specific" placeholder="Location Specific" required />


                    </div>



                    <hr class="my-4 border-gray-100">



                    <div class="grid grid-cols-2 gap-8">

                        <div class="space-y-2">

                            <label class="text-sm font-bold tracking-wider text-gray-600 uppercase">Inputs Field</label>

                            @foreach ($inputs as $index => $value)

                            <div class="flex items-center gap-2 group">

                                <x-form.input-floating label="Input Field {{ $index + 1 }}" type='text'

                                    model="inputs.{{ $index }}"

                                    placeholder="Input Field {{ $index + 1 }}" />

                                <button wire:click="removeInput({{ $index }})"

                                    class="transition-opacity opacity-50 btn btn-square btn-xs btn-error btn-outline group-hover:opacity-100">×</button>

                            </div>

                            @endforeach

                            <button wire:click="addInput"

                                class="no-underline btn btn-ghost btn-xs text-primary hover:bg-primary/10">+ Add New

                                Input</button>

                        </div>



                        <div class="space-y-2">

                            <label class="text-sm font-bold tracking-wider text-gray-600 uppercase">Checkpoints</label>

                            @foreach ($checks as $index => $check)
                            <div class="flex flex-col gap-1 p-2 border border-gray-100 rounded-lg bg-gray-50/50 group">
                                <div class="grid items-center grid-cols-3 gap-2">
                                    <div class="col-span-2">
                                        <x-form.input-floating
                                            label="Item Name {{ $index + 1 }}"
                                            type='text'
                                            model="checks.{{ $index }}.name"
                                            placeholder="e.g. SCBA or Nozzle" />
                                    </div>

                                    <div>
                                        <select wire:model="checks.{{ $index }}.type" class="select select-bordered select-xs">
                                            <option value="checkbox">Centang (V)</option>
                                            <option value="text">Input (Nilai/Angka)</option>
                                        </select>
                                    </div>

                                    <button wire:click="removeCheck({{ $index }})"
                                        class="btn btn-square btn-xs btn-error btn-outline">×</button>
                                </div>
                            </div>
                            @endforeach

                            <button wire:click="addCheck"

                                class="no-underline btn btn-ghost btn-xs text-primary hover:bg-primary/10">+ Add New

                                Check</button>

                        </div>

                    </div>

                </div>



                <div class="p-4 modal-action bg-gray-50 rounded-b-2xl">

                    <button wire:click="save" class="px-8 btn btn-primary btn-soft btn-xs" wire:loading.attr="disabled">

                        <span wire:loading.remove.class='hidden' wire:target="save"

                            class="hidden loading loading-spinner loading-xs"></span>

                        Save Changes

                    </button>

                    <form method="dialog">

                        <button class="btn btn-ghost btn-xs btn-soft" wire:click="resetForm">Cancel</button>

                    </form>

                </div>

            </div>



            <form method="dialog" class="modal-backdrop bg-black/40">

                <button wire:click="resetForm" class="btn btn-soft btn-xs btn-error">close</button>

            </form>

        </dialog>

    </x-tabs-equipment.layout>

    <script>
        window.addEventListener('close-checklist-modal', event => {

            checklist_modal.close();

        });
    </script>

</section>
