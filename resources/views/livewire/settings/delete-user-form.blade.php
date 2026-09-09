<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Delete account') }}</flux:heading>
        <flux:subheading>{{ __('Delete your account and all of its resources') }}</flux:subheading>
    </div>

    <button class="btn btn-soft btn-error btn-xs" onclick="delete_account_modal.showModal()">
        {{ __('Delete account') }}
    </button>

    <dialog id="delete_account_modal" class="modal" wire:ignore.self>
        <div class="modal-box">
            <div class="mb-5">
                <flux:heading size="lg">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

                <flux:subheading class="mt-2">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </flux:subheading>
            </div>

            <div class="mb-6">
                <x-form.input-text
                    label="Password"
                    type="password"
                    model="password"
                    placeholder="Masukkan password Anda..."
                    required
                />
            </div>

            <div class="modal-action">
                <button
                    wire:click="deleteUser"
                    wire:loading.attr="disabled"
                    class="btn btn-error btn-xs"
                >
                    <span wire:loading.remove.class='hidden' wire:target="deleteUser" class="hidden loading loading-spinner loading-xs"></span>
                    {{ __('Delete account') }}
                </button>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</section>
