<section class="w-full">
    @include('partials.translation')
    <div class="flex items-center justify-end pb-2 border-b ">
        <div class="w-full md:max-w-xs">
            <x-form.input-text label="Cari Kata" model="search" required />
        </div>
    </div>
    <x-manhours.layout>
        <div class="p-6">


            @if (session()->has('message'))
            <div class="alert alert-success mb-4 shadow-lg">
                <span>{{ session('message') }}</span>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="card bg-base-100 shadow-xl border border-base-200">
                    <div class="card-body">
                        <h3 class="card-title mb-4">{{ $isEditing ? 'Edit Kata' : 'Tambah Kata Baru' }}</h3>
                        <form wire:submit.prevent="store">

                            <x-form.text_area label="Key (Teks Asli di Kodingan)" model="key" placeholder="{{ __('Contoh: Nama Perusahaan') }}" required />
                            <x-form.text_area label="English (en)" model="en" placeholder="{{ __('Company Name') }}" required />
                            <x-form.text_area label="Indonesia (id_text)" model="id_text" placeholder="{{ __('Nama Perusahaan') }}" required />

                            <div class="card-actions justify-end mt-6">
                                <button type="button" wire:click="resetFields" class="btn btn-error btn-xs">{{ __('Batal') }}</button>
                                <button type="submit" class="btn btn-primary shadow-md btn-xs">
                                    <span wire:loading.remove.class="hidden" wire:target="store" class="loading loading-spinner hidden"></span>
                                    {{ __('Simpan') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2 card bg-base-100 shadow-xl border border-base-200">
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>Key</th>
                                    <th>English</th>
                                    <th>Indonesia</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($translations as $item)
                                <tr>
                                    <td class="max-w-xs truncate font-mono text-xs opacity-70">{{ $item->key }}</td>
                                    <td>{{ Str::limit($item->en, 50) }}</td>
                                    <td>{{ Str::limit($item->id_text, 50) }}</td>
                                    <td class="text-center">
                                        <div class="join">
                                            <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-square btn-ghost join-item text-info" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus data ini?" class="btn btn-sm btn-square btn-ghost join-item text-error" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4">
                        {{ $translations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </x-manhours.layout>
</section>