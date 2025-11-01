<x-app-layout>
<div class="max-w-5xl mx-auto p-4 space-y-4">

    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Daftar Pemasukan</h1>
        <a href="{{ route('incomes.create') }}"
           class="bg-black text-white px-4 py-2 me-1 rounded-lg">
           + Tambah
        </a>
    </div>

    @foreach($incomes as $inc)
    <div class="border rounded-xl p-3 bg-white shadow-sm">
        <div class="flex justify-between">
            <div>
                <div class="font-medium">{{ $inc->source }}</div>
                <div class="text-sm text-gray-500">{{ $inc->date }}</div>
                <div class="text-xs text-gray-400">{{ $inc->note }}</div>
            </div>
            <div class="text-right">
                <div class="font-semibold text-green-600">
                    Rp {{ number_format($inc->amount,0,',','.') }}
                </div>
                <a href="{{ route('incomes.edit', $inc) }}"
                   class="text-xs underline">edit</a>
            </div>
        </div>
    </div>
    @endforeach

    {{ $incomes->links() }}

</div>
</x-app-layout>
