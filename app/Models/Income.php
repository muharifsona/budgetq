<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'user_id','category_id','source','amount','date','note',
        'allocation_mode','target_month','target_year'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
