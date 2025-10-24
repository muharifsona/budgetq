<x-app-layout>
<div class="max-w-xl mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-bold">Import Mutasi Bank (CSV)</h1>

    <form method="POST" action="{{ route('import.preview') }}" enctype="multipart/form-data"
        class="bg-white border rounded-xl p-4 space-y-3">
        @csrf
        <div>
            <label class="block text-sm font-medium">Upload CSV</label>
            <input type="file" name="csv" class="border rounded w-full px-3 py-2">
        </div>
        <button class="bg-black text-white px-4 py-2 rounded">
            Preview
        </button>
    </form>
</div>
</x-app-layout>
