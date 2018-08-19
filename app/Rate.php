<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = [
        'exchange_rate',
        'rate_update_id',
        'currency_id'
    ];

    public function updates()
    {
        return $this->belongsTo(RatesUpdate::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
