<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExpensesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = (int)($request->input('month', now()->month));
        $year  = (int)($request->input('year',  now()->year));
        $categoryId = $request->input('category_id');

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $q = Expense::with('category')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$start, $end]);

        if ($categoryId) $q->where('category_id', $categoryId);

        $rows = $q->orderBy('date','desc')->get();

        $total = $rows->sum('amount');
        $byCategory = $rows->groupBy('category_id')->map->sum('amount');

        $categories = Category::orderBy('name')->get();

        $budget = Budget::where('user_id', auth()->id())
            ->where('year', $year)->where('month', $month)
            ->with('allocations.category')->first();

        return view('reports.index', compact(
            'rows','total','byCategory','categories','month','year','categoryId','budget'
        ));
    }

    public function exportPdf(Request $request)
    {
        [$month,$year,$categoryId,$rows,$total,$budget] = $this->collectData($request);

        $pdf = Pdf::loadView('reports.pdf', [
            'rows'=>$rows,'total'=>$total,'month'=>$month,'year'=>$year,'budget'=>$budget
        ])->setPaper('a4', 'portrait');

        $filename = "Laporan-Pengeluaran-{$year}-".str_pad($month,2,'0',STR_PAD_LEFT).".pdf";
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $month = (int)$request->input('month', now()->month);
        $year  = (int)$request->input('year',  now()->year);
        $categoryId = $request->input('category_id');

        $filename = "Laporan-Pengeluaran-{$year}-".str_pad($month,2,'0',STR_PAD_LEFT).".xlsx";
        return Excel::download(new ExpensesExport(auth()->id(), $year, $month, $categoryId), $filename);
    }

    private function collectData(Request $request)
    {
        $month = (int)$request->input('month', now()->month);
        $year  = (int)$request->input('year',  now()->year);
        $categoryId = $request->input('category_id');

        $start = \Carbon\Carbon::create($year,$month,1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $q = Expense::with('category')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$start,$end]);

        if ($categoryId) $q->where('category_id', $categoryId);

        $rows  = $q->orderBy('date','desc')->get();
        $total = $rows->sum('amount');

        $budget = Budget::where('user_id', auth()->id())
            ->where('year', $year)->where('month', $month)
            ->with('allocations.category')->first();

        return [$month,$year,$categoryId,$rows,$total,$budget];
    }
}
