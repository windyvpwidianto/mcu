<section class="w-full">
    <x-toast />

    <x-tabs-wpi.layout heading="Daftar Laporan WPI" subheading="Work Permit Inspection - KPLH Site Tokatindung">

        <div class="overflow-x-auto">
            <table class="table w-full border border-collapse border-gray-200 table-xs">
                <thead class="bg-gray-800 text-white italic uppercase text-[10px]">
                    <tr>
                        <th class="p-3 text-center border border-gray-700">Tanggal</th>
                        <th class="p-3 text-center border border-gray-700">No Referensi</th>
                        <th class="p-3 text-center border border-gray-700">Status</th>
                        <th class="text-center border border-gray-700">Lokasi</th>
                        <th class="text-center border border-gray-700">Departemen / Kontraktor</th>
                        <th class="text-center border border-gray-700">Petugas</th>
                        <th class="text-center border border-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse ($reports as $report)
                        <tr class="transition-colors border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">
                                {{ date('d-m-Y', strtotime($report->report_date)) }}
                                <span class="block text-[10px] text-gray-400">{{ $report->report_time }}</span>
                            </td>
                            <td class="font-bold border">{{ $report->no_referensi }}</td>
                            <td class="border">
                                <span
                                    class="badge badge-xs badge-soft {{ $this->getRandomBadgeColor($report->status) }} uppercase px-2">
                                    {{ str_replace('_', ' ', $report->status) }}
                                </span>
                            </td>
                            <td>{{ $report->location }}</td>
                            <td class="font-bold uppercase border text-info">{{ $report->department }}</td>
                            <td class="text-center border">
                                <span class="font-normal badge badge-ghost badge-sm">{{ count($report->inspectors) }}
                                    Orang</span>
                            </td>
                            <td class="p-3 border border-gray-200">
                                <div class="flex flex-row justify-center gap-2" wire:key="actions-{{ $report->id }}">
                                    {{-- Tombol Edit --}}
                                    @can('view', $report)
                                        <flux:tooltip content="edit" position="top">
                                            <flux:button href="{{ route('wpi.edit', $report->id) }}" size="xs"
                                                icon="pencil-square" variant="subtle">
                                            </flux:button>
                                        </flux:tooltip>

                                        {{-- Tombol Hapus --}}
                                        <flux:tooltip content="hapus" position="top">
                                            <flux:button wire:click="deleteReport({{ $report->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus laporan ini beserta seluruh lampiran fotonya?"
                                                size="xs" icon="trash" variant="danger">
                                            </flux:button>
                                        </flux:tooltip>
                                        {{-- Tombol Download PDF --}}
                                        <flux:tooltip content="Download PDF" position="top">
                                            <flux:button wire:click="exportPDF({{ $report->id }})" size="xs"
                                                icon="document-arrow-down" variant="primary" color="blue">
                                            </flux:button>
                                        </flux:tooltip>
                                    @else
                                        <span class="italic text-gray-400">No Actions Available</span>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 italic text-center text-gray-400">Data laporan tidak
                                ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    </x-tabs-wpi.layout>
</section>
