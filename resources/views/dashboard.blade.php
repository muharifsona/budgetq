<x-app-layout>
<div class="max-w-5xl mx-auto p-6 space-y-8"
    x-data="{ }">

    <h1 class="text-2xl font-bold mb-2">Dashboard Keuangan — {{ now()->format('M Y') }}</h1>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow border">
            <div class="text-gray-500 text-sm">Total Budget</div>
            <div class="text-xl font-semibold">
                {{ number_format(optional($budget)->total_amount ?? 0,0,',','.') }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow border">
            <div class="text-gray-500 text-sm">Total Expense</div>
            <div class="text-xl font-semibold text-red-600">
                @php $totalExpense = array_sum($expensesByCategory->toArray()); @endphp
                {{ number_format($totalExpense,0,',','.') }}
                @if($totalExpense > ($budget->total_amount ?? 0))
                (Over Budget!)
                @elseif($totalExpense > (($budget->total_amount ?? 0) * 0.8))
                (Warning)
                @else
                (Safe)
                @endif
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow border">
            <div class="text-gray-500 text-sm">Sisa</div>
            <div class="text-xl font-semibold {{ $totalExpense > ($budget->total_amount ?? 0) ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format(($budget->total_amount ?? 0) - $totalExpense,0,',','.') }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow border">
            <div class="text-gray-500 text-sm">Total Tabungan</div>
            <div class="text-xl font-semibold text-blue-600">
                @php
                    $tg = \App\Models\SavingGoal::where('user_id',auth()->id())->sum('current_amount');
                @endphp
                {{ number_format($tg,0,',','.') }}
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- PIE CHART --}}
        <div class="bg-white p-4 rounded-2xl shadow border">
            <h2 class="text-lg font-medium mb-2">Pengeluaran per Kategori</h2>
            <canvas id="pieChart"></canvas>
        </div>

        {{-- TREND CHART --}}
        <div class="bg-white p-4 rounded-2xl shadow border">
            <h2 class="text-lg font-medium mb-2">Trend 6 Bulan Terakhir</h2>
            <canvas id="trendChart"></canvas>
        </div>
    </div>

</div>

{{-- CHART SCRIPTS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    const labels = @json(optional($budget)->allocations?->pluck('category.name') ?? []);
    const values = @json(optional($budget)->allocations?->pluck('amount') ?? []);

    if (labels.length && values.length) {
        new window.Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                }],
            },
        });
    }

    const trendLabels = @json(array_keys($trendFinal->toArray()));
    const trendValues = @json(array_values($trendFinal->toArray()));

    if (trendLabels.length && trendValues.length) {
        new window.Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    data: trendValues,
                }],
            },
        });
    }

});
</script>


</x-app-layout>
