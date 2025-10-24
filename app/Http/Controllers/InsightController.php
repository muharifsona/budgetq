<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use Carbon\Carbon;
use DB;

class InsightController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Current month
        $now = Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        // Last month
        $last_start = $now->copy()->subMonth()->startOfMonth();
        $last_end   = $now->copy()->subMonth()->endOfMonth();

        // Total bulan ini
        $thisMonth = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$start,$end])
            ->sum('amount');

        // Total bulan lalu
        $lastMonth = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$last_start,$last_end])
            ->sum('amount');

        // Per kategori bulan ini
        $byCategory = Expense::where('user_id',$user->id)
            ->whereBetween('date', [$start,$end])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total','category_id');

        // Frekuensi
        $frequency = Expense::where('user_id',$user->id)
            ->whereBetween('date', [$start,$end])
            ->selectRaw('category_id, COUNT(*) as cnt')
            ->groupBy('category_id')
            ->pluck('cnt','category_id');

        // Insights
        $insights = [];
        $ratio = 0;

        // Spike vs last month
        if ($lastMonth > 0) {
            $ratio = (($thisMonth - $lastMonth) / $lastMonth) * 100;
            if ($ratio > 20) $insights[] = "Pengeluaran naik ".round($ratio)."% dibanding bulan lalu.";
            if ($ratio < -20) $insights[] = "Pengeluaran turun ".abs(round($ratio))."% dibanding bulan lalu.";
        }

        // Find highest spending category
        if ($byCategory->count() > 0) {
            $maxCatId = $byCategory->sortDesc()->keys()->first();
            $maxVal   = $byCategory[$maxCatId];
            $catName  = Category::find($maxCatId)->name;
            $insights[] = "Kategori terbesar bulan ini: <b>{$catName}</b> (".number_format($maxVal,0,",",".").").";
        }

        // Frequent category
        $freqCat = $frequency->sortDesc()->keys()->first();
        if ($freqCat) {
            $catName = Category::find($freqCat)->name;
            $insights[] = "Anda sering bertransaksi pada kategori <b>{$catName}</b>.";
        }

        // Average ticket size
        $avg = Expense::where('user_id',$user->id)
            ->whereBetween('date', [$start,$end])
            ->avg('amount');

        if ($avg && $avg > 200000) {
            $insights[] = "Rata-rata pengeluaran cukup tinggi (".number_format($avg,0,",",".")." per transaksi).";
        }

        // Recurring weekly pattern
        $weekly = Expense::where('user_id',$user->id)
            ->whereBetween('date', [$start,$end])
            ->selectRaw("DAYNAME(date) as day, COUNT(*) as cnt")
            ->groupBy('day')
            ->orderBy('cnt','desc')
            ->first();

        if ($weekly) {
            $insights[] = "Hari paling boros: <b>{$weekly->day}</b>.";
        }

        // Score
        $score = 100;

        if ($ratio > 20) $score -= 20;
        if ($avg > 200000) $score -= 10;
        if ($freqCat && $frequency[$freqCat] > 10) $score -= 5;

        if ($score < 0) $score = 0;

        if ($ratio > 20) {
            $insights[] = "Coba evaluasi pengeluaran besar bulan ini. Ada yang bisa ditunda?";
        }
        if ($avg > 200000) {
            $insights[] = "Pertimbangkan menurunkan ticket size, misal cari promo / alternatif.";
        }

        // Leak Detector: small but frequent
        $leaks = Expense::where('user_id',$user->id)
            ->whereBetween('date',[$start,$end])
            ->selectRaw('category_id, COUNT(*) as cnt, AVG(amount) as avg')
            ->groupBy('category_id')
            ->get()
            ->filter(fn($x)=> $x->avg < 50000 && $x->cnt > 8);

        if ($leaks->count()) {
            foreach ($leaks as $l) {
                $cat = Category::find($l->category_id)->name;
                $insights[] = "Hati-hati kebocoran kecil di <b>{$cat}</b> — sering tapi nominal kecil.";
            }
        }

        $subs = Expense::where('user_id',$user->id)
            ->selectRaw('note, COUNT(*) as cnt, AVG(amount) as avg')
            ->groupBy('note')
            ->having('cnt','>=',3)
            ->get();

        foreach ($subs as $s) {
            $insights[] = "Sepertinya kamu punya subscription: <b>{$s->note}</b> (± "
                .number_format($s->avg,0,',','.').")";
        }

        $coffee = Expense::where('user_id',$user->id)
            ->whereBetween('date',[$start,$end])
            ->where('amount','<',30000)
            ->count();

        if ($coffee > 10) {
            $insights[] = "Kebiasaan kopi/teh/camilan mulai keliatan (~{$coffee}x).";
        }

        $weekend = Expense::where('user_id',$user->id)
            ->whereBetween('date',[$start,$end])
            ->whereRaw("DAYOFWEEK(date) IN (1,7)") // Minggu 1, Sabtu 7
            ->sum('amount');

        $weekday = Expense::where('user_id',$user->id)
            ->whereBetween('date',[$start,$end])
            ->whereRaw("DAYOFWEEK(date) NOT IN (1,7)")
            ->sum('amount');

        if ($weekend > $weekday * 0.6) {
            $insights[] = "Weekend spending cukup tinggi — coba batasin hangout/entertainment.";
        }

        $year  = $now->year;
        $month = $now->month;

        $budget = \App\Models\Budget::where('user_id',$user->id)
            ->where('year',$year)
            ->where('month',$month)
            ->first();

        if ($budget && $budget->total_amount > 0) {
            $spentPct = ($thisMonth / $budget->total_amount) * 100;

            if ($spentPct > 80 && $now->day < 20) {
                $insights[] = "Burn rate tinggi: sudah ".round($spentPct)."% sebelum tanggal 20.";
            }
        }

        $necessary = Expense::where('user_id',$user->id)
            ->whereBetween('date',[$start,$end])
            ->whereHas('category', fn($q)=>$q->where('is_necessary', true))
            ->sum('amount');

        $want = $thisMonth - $necessary;

        if ($want > $necessary) {
            $insights[] = "Pengeluaran keinginan (non-primer) melebihi kebutuhan.";
        }

        $lastByCategory = Expense::where('user_id',$user->id)
            ->whereBetween('date',[$last_start,$last_end])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total','category_id');

        foreach ($byCategory as $c => $val) {
            $prev = $lastByCategory[$c] ?? 0;
            if ($prev > 0 && ($val - $prev) / $prev > 0.3) {
                $name = Category::find($c)->name;
                $insights[] = "Kategori <b>{$name}</b> naik drastis dibanding bulan lalu.";
            }
        }

        $suggestions = [];

        if ($spentPct > 80) $suggestions[] = "Pertimbangkan menunda pembelian non-primer.";
        if ($coffee > 10) $suggestions[] = "Kurangi minuman/outdoor snack, bisa hemat signifikan.";
        if ($ratio > 20) $suggestions[] = "Evaluasi item besar bulan ini, ada yang bisa di-refund?";
        if ($want > $necessary) $suggestions[] = "Upgrade budget kategori primer.";

        $badges = [];

        if ($coffee > 10) $badges[] = "☕ Coffee Lover";
        if ($spentPct > 80) $badges[] = "🔥 Burn Rate High";
        if ($thisMonth < $lastMonth) $badges[] = "💰 Good Saving";
        if ($suggestions < 1) $badges[] = "🫶 Finance Guardian";

        // Recurring Vendor Detector
        $twoMonth = Expense::where('user_id',$user->id)
            ->whereBetween('date',[now()->copy()->subMonths(2)->startOfMonth(), now()->copy()->endOfMonth()])
            ->selectRaw('note, COUNT(*) as cnt, AVG(amount) as avg')
            ->groupBy('note')
            ->having('cnt','>=',3)
            ->get();

        foreach ($twoMonth as $v) {
            $insights[] = "Sepertinya ada tagihan berulang: <b>{$v->note}</b> (± ".number_format($v->avg,0,',','.').")";
        }

        $stress = 0;

        if ($ratio > 20) $stress += 20;
        if ($want > $necessary) $stress += 15;
        if ($avg > 200000) $stress += 10;
        if ($spentPct > 80) $stress += 15;

        $insights[] = "Stress Index: <b>{$stress}</b> (0=calm, 60+=high stress)";

        $totalSpend = $thisMonth ?: 1;
        $dep = [];

        foreach ($byCategory as $cat=>$val) {
        $dep[$cat] = ($val / $totalSpend) * 100;
        }

        $maxDep = max($dep);
        $maxDepCat = array_search($maxDep, $dep);

        if ($maxDep > 40) {
            $catName = Category::find($maxDepCat)->name;
            $insights[] = "Dependency tinggi pada kategori <b>{$catName}</b> (".round($maxDep)."%) — hati².";
        }

        if ($want > $necessary && $spentPct > 80) {
            $suggestions[] = "Alihkan 10–20% budget dari hiburan/food delivery ke kebutuhan pokok.";
        }

        $leakTimeline = Expense::where('user_id',$user->id)
            ->whereBetween('date',[$start,$end])
            ->where('amount','<',30000)
            ->selectRaw("WEEK(date) as w, SUM(amount) as total")
            ->groupBy('w')
            ->pluck('total','w');

        $radar = [
            'necessity' => ($necessary / $thisMonth) * 100,
            'wants'     => ($want / $thisMonth) * 100,
            'burnRate'  => $spentPct,
            'frequency' => ($frequency->sum() / 50) * 100, // normalized
            'dependency'=> $maxDep,
        ];

        $progress6 = Expense::where('user_id',$user->id)
            ->whereBetween('date',[now()->copy()->subMonths(5)->startOfMonth(), now()->copy()->endOfMonth()])
            ->selectRaw("DATE_FORMAT(date,'%Y-%m') as m, SUM(amount) as total")
            ->groupBy('m')->pluck('total','m');

        $progress6 = $progress6->toArray();
        if (last($progress6) > reset($progress6)*1.3) {
            $insights[] = "Pengeluaran 6 bulan naik >30% — tren kurang sehat.";
        }

        if ($stress > 40) $badges[] = "😰 Stressed Spender";
        if ($maxDep > 40) $badges[] = "🌀 Category Addict";
        if ($leaks->count() > 0) $badges[] = "💧 Leak Hunter";
        if ($weekend > $weekday) $badges[] = "🎉 Weekend Warrior";

        $insights[] = match(true) {
            $spentPct > 90 => "Bulan ini agak ngebut ya bang? rem dikit 🛑",
            $ratio > 20   => "Bengkak dibanding bulan lalu, coba audit transaksi besar 👀",
            $want > $necessary => "Keinginan menyalip kebutuhan — atur ulang prioritas 🧭",
            default       => "Stabil, pertahankan ritme 👍"
        };

        $actions = ['Nothing'];

        if ($want > $necessary) $actions[] = "Cut 1 hiburan langganan.";
        if ($coffee > 10)      $actions[] = "Batasi 'ngopi luar' jadi 2x minggu.";
        if ($spentPct > 80)    $actions[] = "Bekukan pembelian discretionary 7 hari.";
        if ($ratio > 20)       $actions[] = "Audit top 3 kategori & pause 1 minggu.";

        return view('insight.index', compact(
            'thisMonth',
            'lastMonth', 'byCategory', 'insights', 'suggestions', 'badges', 'score', 'leakTimeline', 'radar', 'actions'
        ));
    }
}
