<x-app-layout>
<div class="max-w-4xl mx-auto p-6 space-y-6"
     x-data="{ showForm:false }">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Saving Goals 🎯</h1>
        <button @click="showForm = !showForm"
                class="bg-black text-white px-4 py-2 rounded-lg">
            + Tambah Target
        </button>
    </div>

    {{-- FORM --}}
    <form x-show="showForm"
          method="POST"
          action="{{ route('saving.goals.store') }}"
          class="bg-white border rounded-xl p-4 space-y-3">
        @csrf
        <div>
            <label class="text-sm block">Nama Target</label>
            <input type="text" name="name" class="border rounded w-full px-3 py-1">
        </div>

        <div>
            <label class="text-sm block">Nominal Target</label>
            <input type="number" step="1000" name="target_amount" class="border rounded w-full px-3 py-1">
        </div>

        <div>
            <label class="text-sm block">Deadline (opsional)</label>
            <input type="date" name="deadline" class="border rounded w-full px-3 py-1">
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Simpan
        </button>
    </form>


    {{-- LIST --}}
    @foreach($goals as $goal)
    <div class="bg-white rounded-xl border shadow p-4 relative"
         x-data="{ amount: {{ $goal->current_amount }} }">

        <div class="flex justify-between items-center mb-1">
            <div class="font-medium text-lg">{{ $goal->name }}</div>
            <div class="text-xs text-gray-500">
                {{ $goal->deadline ?? '' }}
            </div>
        </div>

        {{-- PROGRESS BAR --}}
        <div class="w-full bg-gray-200 rounded-full h-3 mb-2 overflow-hidden">
            <div class="h-full bg-green-500 transition-all duration-200"
                 style="width: {{ $goal->percentage }}%">
            </div>
        </div>

        <div class="flex justify-between text-sm mb-2">
            <span>Progress: {{ number_format($goal->current_amount,0,',','.') }}</span>
            <span>Target: {{ number_format($goal->target_amount,0,',','.') }}</span>
        </div>

        {{-- SLIDER UPDATE --}}
        <form method="POST" action="{{ route('saving.goals.update',$goal) }}">
            @csrf
            <input type="range"
                   min="0"
                   max="{{ $goal->target_amount }}"
                   step="10000"
                   name="current_amount"
                   x-model.number="amount"
                   class="w-full">

            <div class="text-right text-sm mt-1">
                <span x-text="new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(amount)"></span>
            </div>

            <div class="flex justify-end mt-2">
                <button class="bg-black text-white px-4 py-1 rounded-lg">
                    Update
                </button>
            </div>
        </form>

        {{-- BADGE STATUS --}}
        @if($goal->percentage >= 100)
            <span class="absolute top-2 right-2 bg-indigo-600 text-white text-xs px-2 py-1 rounded">
                Selesai ✅
            </span>
        @elseif($goal->percentage >= 80)
            <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded">
                Hampir 🎉
            </span>
        @endif

    </div>
    @endforeach

</div>
</x-app-layout>
