<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        // ambil budget bulan ini
        $budget = Budget::where('user_id',$user->id)
            ->where('year',$year)
            ->where('month',$month)
            ->with('allocations.category')
            ->first();

        // pengeluaran bulan ini (per kategori)
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $expensesByCategory = Expense::where('user_id',$user->id)
            ->whereBetween('date',[$start,$end])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total','category_id');

        // trend 6 bulan
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(now()->copy()->subMonths($i)->format('Y-m'));
        }

        // Query
        $trend = Expense::where('user_id',$user->id)
            ->whereBetween('date',[now()->copy()->subMonths(5)->startOfMonth(), now()->copy()->endOfMonth()])
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total','ym');

        // Fill missing months
        $trendFinal = $months->mapWithKeys(function($m) use ($trend){
            return [$m => $trend[$m] ?? 0];
        });
        $trendLabels = $trendFinal->keys()->map(fn($m) => Carbon::parse($m)->format('M'));

        return view('dashboard', compact(
            'budget',
            'expensesByCategory',
            'trend', 'trendFinal', 'trendLabels'
        ));
    }
}
