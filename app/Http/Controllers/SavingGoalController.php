<?php

namespace App\Http\Controllers;

use App\Models\SavingGoal;
use Illuminate\Http\Request;

class SavingGoalController extends Controller
{
    public function index()
    {
        $goals = SavingGoal::where('user_id', auth()->id())
            ->orderBy('created_at','desc')
            ->get();

        return view('saving-goals.index', compact('goals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'target_amount' => ['required','numeric','min:1000'],
            'deadline' => ['nullable','date'],
        ]);

        $data['user_id'] = auth()->id();

        SavingGoal::create($data);

        return back()->with('status','Saving goal dibuat!');
    }

    public function update(Request $request, SavingGoal $goal)
    {
        abort_unless($goal->user_id === auth()->id(), 403);

        $data = $request->validate([
            'current_amount' => ['required','numeric','min:0']
        ]);

        $goal->update($data);

        return back()->with('status','Progress diperbarui!');
    }
}
