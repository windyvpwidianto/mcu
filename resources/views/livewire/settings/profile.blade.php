<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your data account profile information and email address.')">
        <form wire:submit="updateProfileInformation" class="w-full my-6 space-y-6">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <x-form.input-text label="Nama Lengkap" model="name" placeholder="Nama Lengkap..." required />
                <x-form.input-text label="username" model="username" placeholder="username..." required />
                <x-form.input-text label="employee id" model="employee_id" placeholder="employee id..." required />
                <fieldset>
                    <input id="department" value="department" wire:model="deptCont"
                        class="peer/department radio radio-xs radio-accent" type="radio" name="deptCont"
                        checked />
                    <x-form.label for="department" class="peer-checked/department:text-accent text-[10px]"
                        label="PT. MSM & PT. TTN" required />
                    <input id="company" value="company" wire:model="deptCont"
                        class="peer/company radio radio-xs radio-primary" type="radio" name="deptCont" />
                    <x-form.label for="company" class="peer-checked/company:text-primary" label="Kontraktor"
                        required />

                    <div class="hidden peer-checked/department:block">
                        {{-- Department --}}
                        <div class="relative mb-1">
                            <x-form.searchable-dropdown-without-label modelsearch="search" modelid="department_id"
                                placeholder="Cari Departemen..." :options="$departments" :showdropdown="$showDropdown"
                                clickaction="selectDepartment" namedb="department_name" />
                        </div>
                    </div>
                    <div class="hidden peer-checked/company:block">
                        {{-- Contractor --}}
                        <div class="relative mb-1">
                            <x-form.searchable-dropdown-without-label modelsearch="searchContractor"
                                placeholder="Cari Kontraktor..." modelid="contractor_id" :options="$contractors"
                                :showdropdown="$showContractorDropdown" clickaction="selectContractor" namedb="contractor_name" />
                        </div>
                    </div>
                </fieldset>
                <div>

                    <x-form.input-text label="Email" type='email' model="email" placeholder="email..." required />

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer"
                                wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                        <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </flux:text>
                        @endif
                    </div>
                    @endif
                </div>

                <x-form.tgl  label="Date of Birth" model="date_birth" dateFormat="d-m-Y" />
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" class="btn btn-soft btn-success btn-xs">{{ __('Save') }}</button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
