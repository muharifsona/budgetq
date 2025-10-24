<x-app-layout>
    <div class="py-6">

        <div class="max-w-6xl mx-auto p-4 space-y-6"
            x-data="budgetPlanner({
                initialAvailable: @js($availableCategories->map(fn($c)=>['id'=>$c->id,'name'=>$c->name,'color'=>$c->color])),
                initialAllocated: @js($allocations->map(fn($a)=>[
                    'category_id'=>$a->category_id,
                    'name'=>$a->category->name,
                    'color'=>$a->category->color,
                    'amount'=>(float)$a->amount,
                ])),
                postUrl: '{{ route('budgets.allocations.save', [$budget->year, $budget->month]) }}',
                csrf: '{{ csrf_token() }}',
                totalAmount: {{ (float)$budget->total_amount }},
            })">

            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Budget Planner — {{ sprintf('%02d', $budget->month) }}/{{ $budget->year }}</h1>

                <form method="POST" action="{{ route('budgets.store', [$budget->year, $budget->month]) }}" class="flex items-center gap-2">
                    @csrf
                    <label class="text-sm">Total Budget (bulan ini)</label>
                    <input name="total_amount" type="number" min="0" step="1000"
                        value="{{ $budget->total_amount }}"
                        class="border rounded px-3 py-1 w-48">
                    <button class="bg-black text-white px-4 py-2 rounded">Simpan Total</button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Kategori Tersedia --}}
                <div class="border rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold">Kategori Tersedia</h2>
                        <span class="text-xs text-gray-500" x-text="available.length + ' item'"></span>
                    </div>

                    <div id="availableList" class="min-h-48 border rounded-xl p-3 space-y-2 bg-gray-50"
                        x-init="makeSortable($el, {
                            group: { name: 'cats', pull: true, put: false },
                            sort: true,
                            animation: 150
                        })">
                        <template x-for="c in available" :key="c.id">
                            <div class="flex items-center justify-between bg-white rounded-xl px-3 py-2 shadow-sm border">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full" :style="`background:${c.color||'#e5e7eb'}`"></span>
                                    <span x-text="c.name"></span>
                                </div>
                                <span class="text-xs text-gray-400">drag ➜</span>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Tarik kategori ke panel kanan untuk dialokasikan.</p>
                </div>

                {{-- Alokasi Bulan Ini --}}
                <div class="border rounded-2xl p-4 md:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold">Alokasi Bulan Ini</h2>
                        <div class="text-right">
                            <div class="text-sm">Teralokasi: <span class="font-semibold" x-text="formatMoney(totalAllocated())"></span></div>
                            <div class="text-sm"
                                :class="remaining() < 0 ? 'text-red-600 font-semibold' : 'text-gray-700'">
                                Sisa: <span x-text="formatMoney(remaining())"></span>
                            </div>
                        </div>
                    </div>

                    <div id="allocatedList" class="min-h-48 border rounded-xl p-3 space-y-3 bg-white"
                        x-init="makeSortable($el, {
                            group: { name: 'cats', pull: true, put: true },
                            sort: true,
                            animation: 150,
                            handle: '.drag-handle',
                            onAdd: (evt) => onAddFromAvailable(evt),
                            onUpdate: (evt) => reorder(),
                        })">

                        <template x-for="(item, idx) in allocated" :key="item.category_id">
                            <div class="alloc-item rounded-xl shadow-sm p-3 relative transition-all duration-200 ease-out" x-transition.opacity.scale.70
                                :class="{
                                    'border-red-500 bg-red-50': remaining() < 0 || ((expenses[item.category_id] ?? 0) > item.amount),
                                    'border-gray-200 bg-white': remaining() >= 0
                                }">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="drag-handle absolutess right-2 top-2 text-gray-400 cursor-move">☰</div>
                                        <div class="w-3 h-3 rounded-full" :style="`background:${item.color||'#e5e7eb'}`"></div>
                                        <span class="font-medium" x-text="item.name"></span>
                                    </div>
                                    <button class="text-xs text-red-600" @click="remove(idx)">Hapus</button>
                                </div>

                                <div class="mt-3 flex items-center gap-3">
                                    <input type="range" min="0" :max="maxSlider()"
                                        step="10000" x-model.number="item.amount" class="w-full">
                                    <span class="text-xs ml-2"
                                        :class="item.amount / totalAmount > 0.9 ? 'text-red-600 font-semibold' : 'text-gray-500'">
                                        <span x-text="((item.amount / totalAmount * 100).toFixed(0)) + '%'"></span>
                                    </span>
                                    <input type="number" step="1000" min="0"
                                        class="border rounded px-2 py-1 w-36 hiddenxx"
                                        x-model.number="item.amount" @change="item.amount = Number(item.amount)">
                                </div>
                                {{-- <div class="w-full h-1.5 rounded-full bg-gray-200 overflow-hidden mt-2">
                                    <div class="h-full transition-all"
                                        :style="`width: ${(item.amount / totalAmount * 100).toFixed(1)}%; background: ${item.color};`">
                                    </div>
                                </div> --}}
                                <div class="text-rightxx text-sm mt-1"
                                    :class="remaining() < 0 ? 'text-red-600 font-semibold' : 'text-gray-700'">
                                    Budget: <span x-text="formatMoney(item.amount)"></span>
                                </div>
                                <div class="text-sm text-gray-500 mt-1">
                                    Real:
                                    <span x-text="formatMoney((expenses[item.category_id] ?? 0))"></span>
                                </div>
                                <div class="text-sm mt-1"
                                    :class="realRemaining(item.category_id) > 0 ? 'text-red-600' : 'text-green-600'">
                                    <span x-text="realRemaining(item.category_id) > 0
                                        ? '+' + formatMoney(realRemaining(item.category_id))
                                        : formatMoney(realRemaining(item.category_id))"></span>
                                </div>

                            </div>
                        </template>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <button @click="autoSpread()" class="text-sm underline">Bagi Rata Sisa ke Semua</button>
                        <button @click="save()" class="bg-black text-white px-4 py-2 rounded-lg disabled:opacity-50"
                                :disabled="isSaving">
                            <span x-show="!isSaving">Simpan Alokasi</span>
                            <span x-show="isSaving">Menyimpan…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('budgetPlanner', (opts) => ({
                available: opts.initialAvailable || [],
                allocated: (opts.initialAllocated || []).map(a => ({...a})),
                totalAmount: Number(opts.totalAmount || 0),
                isSaving: false,
                expenses: @js($expensesByCategory),
                formatMoney(n){ return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n||0); },
                totalAllocated(){ return this.allocated.reduce((s,x)=>s+Number(x.amount||0),0); },
                remaining(){ return this.totalAmount - this.totalAllocated(); },
                maxSlider(){ return Math.max(0, this.totalAmount); },

                // dipanggil saat item diseret dari kiri -> kanan
                onAddFromAvailable(evt){
                const name = evt.item?.querySelector('span:nth-child(2)')?.textContent?.trim();
                // cari id berdasar posisi index sumber
                const srcIndex = evt.oldIndex ?? 0;
                const srcItem = this.available[srcIndex];
                if (!srcItem) return;

                // jika sudah ada, abaikan (unik per kategori)
                if (this.allocated.find(x => x.category_id === srcItem.id)) {
                    evt.from.removeChild(evt.item);
                    return;
                }

                // inject ke allocated di posisi target (evt.newIndex)
                this.allocated.splice(evt.newIndex ?? this.allocated.length, 0, {
                    category_id: srcItem.id,
                    name: srcItem.name,
                    color: srcItem.color,
                    amount: 0,
                });

                // hapus elemen DOM kloningan (karena kita render via Alpine)
                evt.item.parentNode.removeChild(evt.item);
                },

                reorder(){
                    // Sortable sudah mengubah urutan DOM; samakan array allocated mengikuti DOM
                    const nodes = Array.from(document.querySelectorAll('#allocatedList > div'));
                    const mapNameToIndex = new Map();
                    nodes.forEach((n, i) => {
                        const label = n.querySelector('.font-medium')?.textContent?.trim();
                        if (label) mapNameToIndex.set(label, i);
                    });

                    this.allocated.sort((a,b) => (mapNameToIndex.get(a.name) ?? 0) - (mapNameToIndex.get(b.name) ?? 0));
                },

                remove(idx){
                    this.allocated.splice(idx,1);
                },

                autoSpread(){
                    const sisa = this.remaining();
                    if (sisa <= 0 || this.allocated.length === 0) return;
                    const add = Math.floor(sisa / this.allocated.length);
                    this.allocated = this.allocated.map(x => ({...x, amount: Number(x.amount||0) + add}));
                },

                topCategory(){
                    if (this.allocated.length === 0) return null;
                    return Math.max(...this.allocated.map(a => a.amount));
                },

                realRemaining(category_id){
                    const use = this.expenses[category_id] ?? 0;
                    return Number(use) - Number(this.allocated.find(a => a.category_id === category_id)?.amount ?? 0);
                },

                async save(){
                    this.isSaving = true;
                    const payload = {
                        items: this.allocated.map((x,i)=>({
                            category_id: x.category_id,
                            amount: Number(x.amount||0),
                            sort_order: i
                        }))
                    };
                    try{
                        const res = await fetch(opts.postUrl, {
                            method: 'POST',
                            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': opts.csrf},
                            body: JSON.stringify(payload)
                        });
                        if(!res.ok) throw new Error('Gagal menyimpan');
                        // optional: toast
                        alert('Alokasi tersimpan');
                    }catch(e){
                        alert(e.message || 'Error');
                    }finally{
                        this.isSaving = false;
                    }
                }
            }))
        })
        </script>
    </div>

</x-app-layout>
