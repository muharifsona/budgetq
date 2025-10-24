<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MutationImportController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        return view('import.index', compact('categories'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv');

        // read csv
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        // Remove header row
        $headers = array_shift($rows);

        // Prepare preview
        $preview = [];
        foreach ($rows as $r) {
            // adjust these to match your bank structure:
            $preview[] = [
                'date' => $r[0],       // contoh kolom 1
                'note' => $r[1],       // kolom 2
                'amount' => abs((float)$r[2]) // kolom 3
            ];
        }

        $categories = Category::orderBy('name')->get();

        return view('import.preview', compact('preview','categories'));
    }

    public function commit(Request $request)
    {
        $rows = $request->input('rows');

        foreach ($rows as $row)
        {
            $hash = sha1($row['date'].$row['amount'].$row['note']);

            // skip duplicates
            if (Expense::where('txn_hash',$hash)->exists()) continue;

            Expense::create([
                'user_id' => auth()->id(),
                'category_id' => $row['category_id'],
                'date' => $row['date'],
                'amount' => $row['amount'],
                'note' => $row['note'],
                'txn_hash' => $hash
            ]);
        }

        return redirect()->route('expenses.index')->with('status','Mutasi berhasil di-import!');
    }
}
