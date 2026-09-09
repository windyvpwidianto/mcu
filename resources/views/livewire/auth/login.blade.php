<div class="flex flex-col gap-6 md:max-w-sm">
    <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
        <div class="avatar">
            <div class="w-24 rounded-full">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
            </div>
        </div>
        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
    </a>
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your username or email and password below to log in')" />
    {{-- ^ Deskripsi diubah --}}

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <flux:input size="sm" wire:model="credential" {{-- <-- DIUBAH dari wire:model="email" --}} :label="__('Username or Email')"
            {{-- <-- Label diubah --}} type="text" {{-- <-- type diubah ke text (bukan email) --}} required autofocus autocomplete="username"
            {{-- <-- autocomplete diubah --}} placeholder="username atau email@example.com" />

        <div class="relative ">
            <flux:input size="sm" wire:model="password" :label="__('Password')" type="password" required
                autocomplete="current-password" :placeholder="__('Password')" viewable />
            @if (Route::has('password.request'))
            <flux:link class="absolute top-0 text-sm end-0 " :href="route('password.request')" wire:navigate>
                {{ __('Forgot your password?') }}
            </flux:link>
            @endif
        </div>

        <label class="label">
            <input type="checkbox" wire:model="remember" checked="checked" class="checkbox checkbox-sm  border-[var(--color-base-300)] bg-[var(--color-base-200)] checked:border-[var(--color-primary)] checked:bg-[var(--color-primary)] checked:text-[var(--color-primary-content)]" />
            <span class="label-text text-[var(--color-primary)]/50 hover:text-[var(--color-primary)] transition-colors cursor-pointer">
                {{ __('Remember me') }}
            </span>
        </label>
        <div class="flex items-center justify-end">
            <flux:button variant="primary" size="sm" type="submit" class="w-full">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    {{-- ... (bagian register) ... --}}
    @if (Route::has('register'))
    <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
        {{ __('Belum terdaftar,silahkan daftarkan diri Anda') }}
        <flux:link :href="route('register')" wire:navigate>{{ __('Mendaftar') }}</flux:link>
    </div>
    @endif
</div>
