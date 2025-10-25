<x-app-layout>
<div class="max-w-4xl mx-auto p-6 space-y-6">

    <h1 class="text-2xl font-bold">Spending Insight 🔍</h1>

    <div class="bg-white border rounded-xl p-4">
        <div class="text-sm text-gray-500">Financial Health Score</div>
        <div class="text-4xl font-bold text-indigo-600">{{ $score }}</div>

        <div class="w-full h-2 bg-gray-200 rounded mt-2">
            <div class="h-full bg-indigo-600"
                style="width: {{ $score }}%"></div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white border rounded-xl p-4">
            <div class="text-sm text-gray-500">Bulan ini</div>
            <div class="text-xl font-semibold text-red-600">
                {{ number_format($thisMonth,0,',','.') }}
            </div>
        </div>
        <div class="bg-white border rounded-xl p-4">
            <div class="text-sm text-gray-500">Bulan lalu</div>
            <div class="text-xl font-semibold text-blue-600">
                {{ number_format($lastMonth,0,',','.') }}
            </div>
        </div>
    </div>

    {{-- Insight --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white border rounded-xl p-4 space-y-2">
            <h2 class="text-lg font-semibold">Insight</h2>
            @forelse($insights as $i)
                <div class="text-sm">• {!! $i !!}</div>
            @empty
                <div class="text-sm text-gray-400">Belum cukup data untuk analisa :(</div>
            @endforelse

            {{-- Suggestions --}}
            <div class="bg-white border rounded-xl p-4 space-y-2">
                <h2 class="text-lg font-semibold">Saran</h2>
                @forelse($suggestions as $s)
                    <div class="text-sm">→ {!! $s !!}</div>
                @empty
                    <div class="text-sm text-gray-400">Datamu cukup sehat 👍</div>
                @endforelse
            </div>

            <div class="bg-white border rounded-xl p-4 space-y-2">
                <h2>Aksi yang bisa kamu coba minggu ini:</h2>
                @foreach($actions as $a)
                <div>✅ {{ $a }}</div>
                @endforeach
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow border">
            <h2 class="text-lg font-medium mb-2">Behavior Profile</h2>
            <canvas id="radarChart"></canvas>

            <div class="bg-white border rounded-xl p-4 space-y-2">
                @foreach($badges as $b)
                <span class="bg-indigo-200 text-indigo-700 text-xs px-2 py-1 rounded">{{$b}}</span>
                @endforeach
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {

        const radarCtx = document.getElementById('radarChart');

        new window.Chart(radarCtx, {
            type: 'radar',
            data: {
                labels: ['Necessity','Wants','Burn Rate','Frequency','Dependency'],
                datasets: [{
                    label: 'Behavior Profile',
                    data: @json(array_values($radar)),
                    fill: true
                }]
            },
            options: {
                scales: {
                    r: {
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                }
            }
        });
    });
    </script>

</div>
</x-app-layout>
