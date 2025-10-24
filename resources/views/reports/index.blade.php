<x-app-layout>
<div class="max-w-6xl mx-auto p-6 space-y-6">

    <h1 class="text-2xl font-bold">Laporan Pengeluaran</h1>

    {{-- FILTER --}}
    <form method="GET" class="bg-white border rounded-xl p-4 grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <label class="text-sm">Bulan</label>
            <select name="month" class="border rounded w-full px-2 py-1">
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" @selected($m==$month)>{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="text-sm">Tahun</label>
            <input type="number" name="year" value="{{ $year }}" class="border rounded w-full px-2 py-1">
        </div>
        <div class="md:col-span-2">
            <label class="text-sm">Kategori (opsional)</label>
            <select name="category_id" class="border rounded w-full px-2 py-1">
                <option value="">Semua</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(($categoryId??null)==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end md:col-span-2">
            <button class="bg-black text-white px-4 py-2 rounded w-full">Terapkan</button>
        </div>
    </form>

    {{-- ACTIONS --}}
    <div class="flex gap-3">
        <a href="{{ route('reports.export.pdf', request()->only('month','year','category_id')) }}"
        class="px-4 py-2 bg-red-600 text-white rounded">Export PDF</a>

        <a href="{{ route('reports.export.excel', request()->only('month','year','category_id')) }}"
        class="px-4 py-2 bg-green-500 text-white rounded">Export Excel</a>
    </div>

    {{-- RINGKASAN --}}
    <div class="bg-white border rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>Total Pengeluaran</div>
            <div class="text-xl font-semibold text-red-600">
                {{ number_format($total,0,',','.') }}
            </div>
        </div>
        @if($budget)
        <div class="mt-2 text-sm text-gray-600">
            Budget Bulan Ini: {{ number_format($budget->total_amount,0,',','.') }}
            @php $status = $total > $budget->total_amount ? 'Over' : 'OK'; @endphp
            <span class="ml-2 px-2 py-0.5 rounded text-white
                {{ $status==='Over' ? 'bg-red-500' : 'bg-green-600' }}">{{ $status }}</span>
        </div>
        @endif
    </div>

    {{-- TABEL --}}
    <div class="bg-white border rounded-xl p-4">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b">
                <th class="text-left py-2">Tanggal</th>
                <th class="text-left">Kategori</th>
                <th class="text-left">Catatan</th>
                <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $e)
                <tr class="border-b">
                    <td class="py-2">{{ \Illuminate\Support\Carbon::parse($e->date)->format('Y-m-d') }}</td>
                    <td>{{ $e->category->name ?? '-' }}</td>
                    <td>{{ $e->note }}</td>
                    <td class="text-right">{{ number_format($e->amount,0,',','.') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-4 text-center text-gray-400">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>
