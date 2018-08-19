<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\DB;

class RateUpdate extends Model
{
    protected $table = 'rate_updates';
    protected $fillable = [
        'date'
    ];

    public function rates() 
    {
        return $this->hasMany(Rate::class);
    }

    public static function getFloatRates()
    {
        try {
            $client = new Guzzle;
            $res = $client->get(env('FLOAT_RATES_API'));
            $xml = simplexml_load_string($res->getBody(), "SimpleXMLElement", LIBXML_NOCDATA);
            return json_decode(json_encode($xml));
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            return $response->getBody()->getContents();
        }
    }

    public static function storeUpdates()
    {
        $currencies = [];
        $message = 'Rates has been successfully updated!';
        DB::transaction(function() use (&$currencies) {
            $response = self::getFloatRates();
            $date = Carbon::parse($response->pubDate)->format('Y-m-d');

            $rate_update = self::firstOrCreate([ 
                'date' => $date 
            ]);

            $rates = [];
            foreach($response->item as $rate) {
                $name = trim($rate->targetName);
                $currencies[$rate->targetCurrency] = $name;
                $currency = Currency::firstOrCreate([
                    'name' => $name,
                    'currency' => $rate->targetCurrency
                ]);
                $rates[] = Rate::firstOrNew([
                    'exchange_rate' => $rate->exchangeRate,
                    'currency_id' => $currency->id,
                    'rate_update_id' => $rate_update->id
                ]);
            }

            $rate_update->rates()->saveMany($rates);
        });
        asort($currencies);
        return compact('currencies', 'message');
    }

    public function convert($targetCurrency, $usd)
    {
        $serverResponse = [];
        $currency = Currency::filter($targetCurrency)->first();

        if($currency) {
            $response = Rate::where('rate_update_id', $this->id)
                ->where('currency_id', $currency->id)
                ->first();

            if($response) {
                $result = $usd * $response->exchange_rate;
                $serverResponse = [
                    'result' => round($result, 2)
                ];
            }
        }

        return $serverResponse;
    }
}
