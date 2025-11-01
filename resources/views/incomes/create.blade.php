<x-app-layout>
<div class="max-w-md mx-auto p-4 space-y-4">

    <h2 class="text-xl font-semibold">Tambah Pemasukan</h2>

    <form method="POST" action="{{ route('incomes.store') }}">
        @csrf

        <label class="block text-sm">Sumber</label>
        <select name="source" class="border rounded w-full px-3 py-1 mb-2"
                x-model="allocation_mode">
            <option value="Gaji dan upah">Gaji dan upah</option>
            <option value="Laba dari bisnis">Laba dari bisnis</option>
            <option value="Penjualan barang dan jasa">Penjualan barang dan jasa</option>
            <option value="Tips dan komisi">Tips dan komisi</option>
            <option value="Pendapatan investasi">Pendapatan investasi</option>
            <option value="Pendapatan sewa">Pendapatan sewa</option>
            <option value="Royalti">Royalti</option>
            <option value="Pendapatan dari platform digital">Pendapatan dari platform digital</option>
            <option value="Bantuan dan hibah">Bantuan dan hibah</option>
        </select>

        <label class="block text-sm hidden">Kategori (opsional)</label>
        <select name="category_id" class="border rounded w-full px-3 py-1 mb-2 hidden">
            <option value="">-</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>

        <label class="block text-sm">Jumlah</label>
        <input type="number" name="amount" step="1000"
               class="border rounded w-full px-3 py-1 mb-2">

        <label class="block text-sm font-medium">Mode Alokasi</label>
        <select name="allocation_mode" class="border rounded px-3 py-1 w-full mb-2" x-model="allocation_mode">
            <option value="tbb">Masuk ke TBB (Buffer)</option>
            <option value="direct">Masuk ke Budget Bulan Ini</option>
        </select>

        <div x-show="allocation_mode==='direct'" class="mt-2">
            <label class="block text-sm">Bulan Tujuan</label>
            <select name="target_month" class="border rounded px-2 py-1 w-full">
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endfor
            </select>

            <label class="block text-sm mt-1">Tahun</label>
            <select name="target_year" class="border rounded px-2 py-1 w-full">
                @for($y=now()->year-1; $y<=now()->year+1; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>

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
