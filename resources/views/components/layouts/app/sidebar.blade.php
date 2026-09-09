<!DOCTYPE html>
<html data-theme="{{ session('theme', 'sentry-interlock') }}"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- @laravelPWA --}}
    @include('partials.head')
</head>

<body class="min-h-screen font-sans antialiased" x-data="{ sidebarHidden: false }">
    <flux:sidebar sticky stashable
        x-bind:class="sidebarHidden ? 'border-e border-zinc-200 bg-base-300 hidden z-50' : 'border-e border-base-100 bg-base-300 z-50'">

        <div class="flex items-center justify-between ">
            <!-- Logo -->
            @auth
            <a href="{{ route('dashboard') }}"
                class="flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>
            @else
            <a href="{{ url('/') }}" class="flex items-center space-x-2 rtl:space-x-reverse">
                <x-app-logo />
            </a>
            @endauth

            <!-- Toggle button di lingkaran kuning -->
            <flux:sidebar.toggle class="lg:hidden" icon="chevron-left" />
        </div>

        {{-- Navigation dari Livewire (menu dinamis) --}}
        <livewire:administration.menu.navlist />

        <flux:spacer />

        {{-- Desktop User Menu: hanya tampil kalau user login --}}
        @auth
        <div wire:ignore.self>
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down" />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex w-8 h-8 overflow-hidden rounded-lg shrink-0">
                                    <span
                                        class="flex items-center justify-center w-full h-full text-base-content rounded-lg bg-base-200 ">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-sm leading-tight text-start">
                                    <span class="font-semibold truncate">{{ auth()->user()->name }}</span>
                                    <span class="text-xs truncate">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>

        </div>
        @else
        {{-- Desktop guest actions (login/register) --}}
        <div class="items-center hidden gap-2 lg:flex">
            <a href="{{ route('login') }}"
                class="btn btn-outline btn-xs">{{ __('Login') }}</a>
            @if(Route::has('register'))
            <a href="{{ route('register') }}"
                class="btn btn-outline btn-xs">{{ __('Register') }}</a>
            @endif
        </div>
        @endauth

    </flux:sidebar>

    <flux:header sticky class="flex justify-between shadow-md max-lg:hidden bg-base-300">
        <div class="block px-2 rounded-lg cursor-pointer group hover:bg-zinc-700 lg:h-0 lg:hidden"
            x-on:click="sidebarHidden = !sidebarHidden">
            <svg class="w-4 h-4 stroke-gray-400 group-hover:stroke-white" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg" fill="none" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                <line x1="9" y1="3" x2="9" y2="21" />
            </svg>
        </div>
        <label class="btn btn-circle btn-xs btn-ghost swap swap-rotate ">
            <!-- this hidden checkbox controls the state -->
            <input type="checkbox" x-on:click="sidebarHidden = !sidebarHidden" />

            <!-- hamburger icon -->

            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                class="hidden fill-current size-4 lg:block swap-off">
                <path fill-rule="evenodd"
                    d="M2 3.75A.75.75 0 0 1 2.75 3h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 3.75ZM2 8a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 8Zm0 4.25a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z"
                    clip-rule="evenodd" />
            </svg>

            <!-- close icon -->

            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                class="hidden fill-current size-4 lg:block swap-on">
                <path fill-rule="evenodd"
                    d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd" />
            </svg>
        </label>
        <livewire:language-switcher />

    </flux:header>

    <!-- Mobile User Menu -->
    <flux:header sticky class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />

        <flux:spacer />

        <livewire:language-switcher />
        @auth
        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex w-8 h-8 overflow-hidden rounded-lg shrink-0">
                                <span
                                    class="flex items-center justify-center w-full h-full text-black rounded-lg bg-neutral-200 dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-sm leading-tight text-start">
                                <span class="font-semibold truncate">{{ auth()->user()->name }}</span>
                                <span class="text-xs truncate">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
        @else
        <div class="flex items-center gap-2 px-2">
            <a href="{{ route('login') }}"
                class="btn btn-outline btn-xs">{{ __('Login') }}</a>
            @if(Route::has('register'))
            <a href="{{ route('register') }}"
                class="btn btn-outline btn-xs">{{ __('Register') }}</a>
            @endif
        </div>
        @endauth
    </flux:header>

    {{ $slot }}
    <livewire:theme-switcher />
    @fluxScripts
    @livewireScripts
    <script>
        document.addEventListener('alpine:init', () => {
            // Tambahkan parameter isReadOnly pada helper
            Alpine.data('ckeditorHelper', (modelName, isReadOnly = false) => {
                let editorInstance = null;
                let listeners = [];

                return {
                    init() {
                        if (this.$refs.editorElement.querySelector('.ck-editor')) return;

                        window.ClassicEditor
                            .create(this.$refs.editorElement, {
                                placeholder: this.$refs.editorElement.getAttribute('data-placeholder') || 'Tulis sesuatu...',
                                toolbar: isReadOnly ? [] : ['bold', 'italic', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                                removePlugins: ['ImageUpload', 'EasyImage']
                            })
                            .then(editor => {
                                editorInstance = editor;

                                // Terapkan mode ReadOnly jika isReadOnly true
                                if (isReadOnly) {
                                    editor.enableReadOnlyMode('policy-lock');
                                }

                                const initialData = this.$wire.get(modelName) || '';
                                editorInstance.setData(initialData);

                                // Jangan jalankan event listener change jika mode ReadOnly
                                if (!isReadOnly) {
                                    editorInstance.model.document.on('change:data', () => {
                                        const data = editorInstance.getData();
                                        this.$wire.set(modelName, data);

                                        const plainText = data.replace(/<[^>]*>/g, '').trim();
                                        if (plainText !== '') {
                                            const el = editorInstance.ui.view.editable.element;
                                            if (el) el.classList.remove('error');
                                        }
                                    });
                                }
                            })
                            .catch(error => console.error('CKEditor Error:', error));

                        // --- LOGIK APPLY ERROR (Hanya jika bisa edit) ---
                        const applyError = () => {
                            if (editorInstance && !isReadOnly) {
                                const data = editorInstance.getData().replace(/<[^>]*>/g, '').trim();
                                if (data === '') {
                                    const el = editorInstance.ui.view.editable.element;
                                    if (el) {
                                        el.classList.add('error');
                                        el.scrollIntoView({
                                            behavior: 'smooth',
                                            block: 'center'
                                        });
                                    }
                                }
                            }
                        };

                        listeners.push(Livewire.on('update-editor-data', (event) => {
                            const payload = Array.isArray(event) ? event[0] : event;
                            if (editorInstance && payload.name === modelName) {
                                editorInstance.setData(payload.value || '');
                            }
                        }));

                        listeners.push(Livewire.on(`validate-${modelName}`, applyError));
                        listeners.push(Livewire.on('validate-all-editors', applyError));

                        listeners.push(Livewire.on('reset-all-editors', () => {
                            if (editorInstance && !isReadOnly) {
                                editorInstance.setData('');
                                const el = editorInstance.ui.view.editable.element;
                                if (el) el.classList.remove('error');
                            }
                        }));
                        listeners.push(Livewire.on(`reset-editor-${modelName}`, () => {
                            if (editorInstance && !isReadOnly) {
                                editorInstance.setData('');
                                const el = editorInstance.ui.view.editable.element;
                                if (el) el.classList.remove('error');
                            }
                        }));
                    },

                    destroy() {
                        listeners.forEach(unsubscribe => {
                            if (typeof unsubscribe === 'function') unsubscribe();
                            else if (unsubscribe && unsubscribe.unsubscribe) unsubscribe.unsubscribe();
                        });
                        listeners = [];

                        if (editorInstance) {
                            editorInstance.destroy()
                                .then(() => {
                                    editorInstance = null;
                                })
                                .catch(err => console.error('Destroy Error:', err));
                        }
                    }
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
