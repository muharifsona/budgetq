<?php

// app/Http/Controllers/CategoryController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function sync(Request $request)
    {
        $data = $request->validate([
            'categories' => 'array',
            'categories.*.id' => 'nullable',
            'categories.*.name' => 'required|string|max:100',
            'categories.*.color' => 'nullable|string|max:20',
            'removed_ids' => 'array',
            'removed_ids.*' => 'integer',
        ]);

        // hapus kategori
        if(!empty($data['removed_ids'])){
            Category::whereIn('id', $data['removed_ids'])->delete();
        }

        // upsert kategori (id ada → update, id baru → create)
        foreach ($data['categories'] as $cat) {
            if (!empty($cat['id']) && Category::where('id', $cat['id'])->exists()) {
                Category::where('id', $cat['id'])->update([
                    'name' => $cat['name'],
                    'color' => $cat['color'] ?? '#e5e7eb',
                ]);
            } else {
                Category::create([
                    'name' => $cat['name'],
                    'color' => $cat['color'] ?? '#e5e7eb',
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }
}
