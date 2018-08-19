<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'currency',
    ];

    public function updates()
    {
        return $this->belongsTo(RatesUpdate::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function scopeFilter($query, $filter)
    {
        return $query->where('currency', $filter);
    }
}
