<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CategoryController extends Controller
{
    public function sync(Request $request)
    {
        $incoming = $request->categories;

        foreach ($incoming as $cat)
        {
            \App\Models\Category::updateOrCreate(
                ['id' => $cat['id']],
                ['name' => $cat['name']]
            );
        }

        return response()->json(['ok' => true]);
    }

}
