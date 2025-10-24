<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cats = [
            ['name'=>'Makan', 'color'=>'#22c55e'],
            ['name'=>'Transport', 'color'=>'#3b82f6'],
            ['name'=>'Listrik', 'color'=>'#f59e0b'],
            ['name'=>'Air', 'color'=>'#06b6d4'],
            ['name'=>'Internet', 'color'=>'#a855f7'],
            ['name'=>'Sekolah', 'color'=>'#ef4444'],
            ['name'=>'Hiburan', 'color'=>'#10b981'],
        ];
        foreach ($cats as $c) \App\Models\Category::firstOrCreate(['name'=>$c['name']], $c);
    }

}
