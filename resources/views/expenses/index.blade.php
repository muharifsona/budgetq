<x-app-layout>
<div class="max-w-5xl mx-auto p-4 space-y-4"
    x-data="{ showForm:false }">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Riwayat Pengeluaran</h1>
        <div>
        <a href="import"
            class="bg-black text-white px-4 py-2 me-1 rounded-lg">
            + Import CSV
        </a>
        <a role="button" @click="showForm = !showForm"
            class="bg-black text-white px-4 py-2 rounded-lg">
            + Input Pengeluaran
        </a>
        </div>
    </div>

    {{-- FORM --}}
    <form x-show="showForm" method="POST" action="{{ route('expenses.store') }}"
        class="p-4 bg-white border rounded-xl space-y-3">
        @csrf
        <div>
            <label class="block text-sm">Tanggal</label>
            <input type="date" name="date" required
                class="border rounded px-3 py-1 w-full">
        </div>

        <div>
            <label class="block text-sm">Kategori</label>
            <select name="category_id" required
                    class="border rounded px-3 py-1 w-full">
                <option value="">-- pilih --</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm">Jumlah</label>
            <input type="number" step="1000" name="amount" required
                class="border rounded px-3 py-1 w-full">
        </div>

        <div>
            <label class="block text-sm">Catatan (opsional)</label>
            <input type="text" name="note"
                class="border rounded px-3 py-1 w-full">
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Simpan
        </button>
    </form>

    {{-- LIST HISTORI --}}
    <div class="bg-white border rounded-xl p-4 space-y-2">
        @foreach($expenses as $e)
        <div class="flex items-center justify-between border-b pb-2">
            <div>
                <div class="text-sm font-medium">{{ $e->category->name }}</div>
                <div class="text-xs text-gray-500">{{ $e->note }}</div>
            </div>
            <div class="text-sm text-right">
                <div>{{ number_format($e->amount,0,',','.') }}</div>
                <div class="text-xs text-gray-400">{{ $e->date }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $expenses->links() }}
</div>
</x-app-layout>
