<x-app-layout>
<div class="max-w-md mx-auto p-4 space-y-4">

    <h2 class="text-xl font-semibold">Tambah Pemasukan</h2>

    <form method="PUT" action="{{ route('incomes.store') }}">
        @csrf

        <label class="block text-sm">Sumber</label>
        <input name="source" class="border rounded w-full px-3 py-1 mb-2">

        <label class="block text-sm">Kategori (opsional)</label>
        <select name="category_id" class="border rounded w-full px-3 py-1 mb-2">
            <option value="">-</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>

        <label class="block text-sm">Jumlah</label>
        <input type="number" name="amount" step="1000"
               class="border rounded w-full px-3 py-1 mb-2">

        <label class="block text-sm">Tanggal</label>
        <input type="date" name="date" class="border rounded w-full px-3 py-1 mb-2">

        <label class="block text-sm">Catatan</label>
        <textarea name="note" class="border rounded w-full px-3 py-1 mb-4"></textarea>

        <button class="bg-black text-white px-4 py-2 rounded">
            Simpan
        </button>
    </form>

</div>
</x-app-layout>
