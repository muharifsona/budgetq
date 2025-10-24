<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['user_id','category_id','date','amount','note'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category(){ return $this->belongsTo(Category::class); }
    public function user(){ return $this->belongsTo(User::class); }
}

