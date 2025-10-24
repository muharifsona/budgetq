<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetAllocation extends Model
{
    protected $fillable = ['budget_id','category_id','amount','sort_order'];
    public function category(){ return $this->belongsTo(Category::class); }
}
