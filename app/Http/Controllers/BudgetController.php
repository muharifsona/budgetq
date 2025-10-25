<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function show(int $year, int $month)
    {
        $user = auth()->user();
        $budget = \App\Models\Budget::firstOrCreate(
            ['year'=>$year,'month'=>$month,'user_id'=>$user->id],
            ['total_amount'=>0]
        );

        $allocations = $budget->allocations()->with('category')->orderBy('sort_order')->get();
        $allocatedCategoryIds = $allocations->pluck('category_id')->all();

        $availableCategories = \App\Models\Category::whereNotIn('id',$allocatedCategoryIds)->orderBy('name')->get();

        // hitung pengeluaran bulan ini
        $monthStart = now()->setYear($year)->setMonth($month)->startOfMonth();
        $monthEnd = now()->setYear($year)->setMonth($month)->endOfMonth();

        $expensesByCategory = \App\Models\Expense::where('user_id',auth()->id())
            ->whereBetween('date', [$monthStart,$monthEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total','category_id');

        // prev & next month
        $prev = \Carbon\Carbon::create($year, $month)->subMonth();
        $next = \Carbon\Carbon::create($year, $month)->addMonth();

        $prevYear = $prev->year;
        $prevMonth = $prev->month;

        $nextYear = $next->year;
        $nextMonth = $next->month;

        // return view('budgets.planner', compact('budget','allocations', 'availableCategories', 'expensesByCategory'));
        return view('budgets.planner', compact(
            'budget','allocations', 'availableCategories',
            'expensesByCategory','year','month',
            'prevYear','prevMonth','nextYear','nextMonth'
        ));

    }

    public function storeOrUpdate(Request $request, int $year, int $month)
    {
        $data = $request->validate([
            'total_amount' => ['required','numeric','min:0'],
        ]);
        $budget = \App\Models\Budget::where([
            'year'=>$year,'month'=>$month,'user_id'=>auth()->id()
        ])->firstOrFail();

        $budget->update($data);

        return back()->with('status','Total budget diperbarui');
    }

    public function saveAllocations(Request $request, int $year, int $month)
    {
        $payload = $request->validate([
            'items' => ['required','array'], // [{category_id, amount, sort_order}]
            'items.*.category_id' => ['required','integer','exists:categories,id'],
            'items.*.amount' => ['required','numeric','min:0'],
            'items.*.sort_order' => ['required','integer','min:0'],
        ]);

        $budget = \App\Models\Budget::where([
            'year'=>$year,'month'=>$month,'user_id'=>auth()->id()
        ])->firstOrFail();

        // sinkronisasi sederhana:
        $idsInPayload = collect($payload['items'])->pluck('category_id')->all();
        $budget->allocations()->whereNotIn('category_id', $idsInPayload)->delete();

        foreach ($payload['items'] as $item) {
            $budget->allocations()->updateOrCreate(
                ['category_id'=>$item['category_id']],
                ['amount'=>$item['amount'],'sort_order'=>$item['sort_order']]
            );
        }

        return response()->json(['ok'=>true]);
    }

}
