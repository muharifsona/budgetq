<x-app-layout>
<div class="max-w-5xl mx-auto p-6 space-y-6">

    <h1 class="text-2xl font-bold">Preview Import</h1>

    <form method="POST" action="{{ route('import.commit') }}">
        @csrf

        <table class="w-full text-sm bg-white rounded-xl border">
            <thead>
                <tr class="border-b bg-gray-100">
                    <th class="p-2 text-left">Tanggal</th>
                    <th class="text-left">Catatan</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-left">Kategori</th>
                </tr>
            </thead>
            <tbody>
                @foreach($preview as $i => $row)
                <tr class="border-b">
                    <td class="px-4">{{ $row['date'] }}</td>
                    <td>{{ $row['note'] }}</td>
                    <td class="text-right">{{ number_format($row['amount'],0,',','.') }}</td>
                    <td class="text-center">
                        <select name="rows[{{ $i }}][category_id]" class="border rounded px-2 py-1">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </td>

                    {{-- hidden --}}
                    <input type="hidden" name="rows[{{ $i }}][date]" value="{{ $row['date'] }}">
                    <input type="hidden" name="rows[{{ $i }}][note]" value="{{ $row['note'] }}">
                    <input type="hidden" name="rows[{{ $i }}][amount]" value="{{ $row['amount'] }}">
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            <button class="bg-green-500 text-white px-4 py-2 rounded">
                Commit Import
            </button>
        </div>
    </form>

</div>
</x-app-layout>
