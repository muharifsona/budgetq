<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingGoal extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'deadline',
    ];

    public function user(){ return $this->belongsTo(User::class); }

    public function getPercentageAttribute()
    {
        return $this->target_amount == 0 ? 0 :
            ($this->current_amount / $this->target_amount) * 100;
    }
}

