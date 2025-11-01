<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Category;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $incomes = Income::where('user_id', auth()->id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('incomes.index', compact('incomes','month','year'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('incomes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'note' => 'nullable|string',
            'allocation_mode' => 'required|in:tbb,direct',
            'target_month' => 'nullable|integer|min:1|max:12',
            'target_year' => 'nullable|integer',
        ]);

        $income = Income::create($data);

        if($income->allocation_mode === 'direct') {
            $budget = Budget::firstOrCreate([
                'year' => $income->target_year,
                'month' => $income->target_month,
                'user_id' => auth()->id(),
            ]);

            $budget->increment('total_amount', $income->amount);
        }

        return redirect()->route('incomes.index')
            ->with('status', 'Pemasukan ditambahkan.');
    }

    public function edit(Income $income)
    {
        abort_unless($income->user_id === auth()->id(), 403);
        $categories = Category::orderBy('name')->get();
        return view('incomes.edit', compact('income','categories'));
    }

    public function update(Request $request, Income $income)
    {
        abort_unless($income->user_id === auth()->id(), 403);

        $data = $request->validate([
            'source' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'note' => 'nullable|string',
        ]);

        $income->update($data);

        return redirect()->route('incomes.index')
            ->with('status', 'Pemasukan diperbarui.');
    }

    public function destroy(Income $income)
    {
        abort_unless($income->user_id === auth()->id(), 403);
        $income->delete();

        return back()->with('status', 'Pemasukan dihapus.');
    }
}
