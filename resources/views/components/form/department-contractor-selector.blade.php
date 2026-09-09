@props([
'deptCont' => null, // wire:model untuk radio button (misal: $deptCont)
'model_dept' => 'department_id', // nama property untuk ID department
'model_cont' => 'contractor_id', // nama property untuk ID contractor
'departments' => [],
'contractors' => [],
'label_dept' => 'Departemen Terkait',
'label_contractor' => 'Kontraktor Terkait',
'showDropdown' => false,
'showContractorDropdown' => false,
'required' => false,
])

<fieldset {{ $attributes->merge(['class' => 'fieldset']) }}>
    <div class="flex items-center gap-4">
        {{-- Radio Department --}}
        <div class="flex items-center gap-1">
            <input id="dept" value="dept" wire:model.live="deptCont"
                class="peer/dept radio radio-xs radio-accent" type="radio" name="dept_cont_toggle" />

            <x-form.label for="dept" class="peer-checked/dept:text-accent text-[10px] cursor-pointer"
                label="{{ $label_dept }}"
                :required="$deptCont === 'dept' && $required" />
        </div>

        {{-- Radio Company/Contractor --}}
        <div class="flex items-center gap-1">
            <input id="cont" value="cont" wire:model.live="deptCont"
                class="peer/cont radio radio-xs radio-primary" type="radio" name="dept_cont_toggle" />

            <x-form.label for="cont" class="peer-checked/cont:text-primary text-[10px] cursor-pointer"
                label="{{ $label_contractor }}"
                :required="$deptCont === 'cont' && $required" />
        </div>
    </div>

    {{-- Dropdown Department --}}
    <div wire:key="dropdown-dept-{{ $model_dept }}" class="{{ $deptCont === 'dept' ? 'block' : 'hidden' }} mt-0.5">
        <div>
            <x-form.searchable-dropdown-without-label
                modelsearch="search"
                :modelid="$model_dept"
                placeholder="Cari Departemen..."
                :options="$departments"
                :showdropdown="$showDropdown"
                clickaction="selectDepartment"
                namedb="department_name" />
        </div>
        {{-- Menampilkan error untuk department_id --}}

    </div>

    {{-- Dropdown Contractor --}}
    <div wire:key="dropdown-cont-{{ $model_cont }}" class="{{ $deptCont === 'cont' ? 'block' : 'hidden' }} mt-0.5">
        <div>
            <x-form.searchable-dropdown-without-label
                modelsearch="searchContractor"
                placeholder="Cari Kontraktor..."
                :modelid="$model_cont"
                :options="$contractors"
                :showdropdown="$showContractorDropdown"
                clickaction="selectContractor"
                namedb="contractor_name" />
        </div>
        {{-- Menampilkan error untuk contractor_id --}}
    </div>

</fieldset>