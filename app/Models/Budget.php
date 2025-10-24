<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['year','month','user_id','total_amount'];
    public function allocations(){ return $this->hasMany(BudgetAllocation::class); }
}
