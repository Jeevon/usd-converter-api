<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\RateUpdate;
use App\Rate;
use App\Currency;

class RatesController extends Controller
{
    public function index()
    {
        $currencies = [];
        $startDate = date('Y-m-d');
        $data = RateUpdate::first();
        if($data) {
            $startDate = $data->date;

            $currencies = Currency::all()->sortBy('name')
                ->pluck('name', 'currency');
        }
        return compact('currencies', 'startDate');
    }

    public function update() 
    {
       return RateUpdate::storeUpdates(); 
    }

    public function convert(Request $request)
    {
        $response = [];
        $data = $request->all();
        $date = Carbon::parse($data['date'])->format('Y-m-d');
        $rate = RateUpdate::where('date', $date)->first();

        if(is_null($rate)) {
            $rate = RateUpdate::latest()->first();
        }
 
        $response = $rate->convert($data['targetCurrency'], $data['usd']);

        return $response;
    }
}
