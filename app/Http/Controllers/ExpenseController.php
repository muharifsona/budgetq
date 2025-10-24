<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = \App\Models\Expense::with('category')
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->paginate(10);

        $categories = \App\Models\Category::orderBy('name')->get();

        return view('expenses.index', compact('expenses','categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required','date'],
            'category_id' => ['required','exists:categories,id'],
            'amount' => ['required','numeric','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        $data['user_id'] = auth()->id();

        \App\Models\Expense::create($data);

        return back()->with('status','Pengeluaran tersimpan!');
    }

}
