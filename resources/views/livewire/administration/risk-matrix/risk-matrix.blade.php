<section class="w-full">
    <x-toast />
    @include('partials.matrix')
    <x-tabs-manajemen-resiko.layout>

        <div class="overflow-x-auto">
            <table class="table-fixed border-collapse w-full text-center">
                <thead>
                    <tr>
                        <th class="w-24 border p-2 bg-gray-100">Likelihood ↓ / Consequence →</th>
                        @foreach ($consequences as $c)
                        <th class="border p-2 bg-blue-100">{{ $c->name }}<br><small>(C{{ $c->level }})</small></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($likelihoods as $l)
                    <tr>
                        <td class="border p-2 font-bold bg-blue-100">
                            {{ $l->name }}<br><small>(L{{ $l->level }})</small>
                        </td>
                        @foreach ($consequences as $c)
                        @php
                        $score = $l->level * $c->level;
                        $color = match(true) {
                        $score <= 3 => 'bg-green-200',
                            $score <= 4=> 'bg-yellow-200',
                                $score <= 16=> 'bg-orange-300',
                                    default => 'bg-red-300',
                                    };
                                    @endphp
                                    <td class="border p-4 {{ $color }}">
                                        <div class="text-sm font-semibold">{{ $score }}</div>
                                    </td>
                                    @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </x-tabs-manajemen-resiko.layout>
</section>
