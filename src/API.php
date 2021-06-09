<?php

namespace kayrah87\AgileOctopusAPI;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;

class API
{
    protected $tz           = 'Europe/London';
    protected $base_url;
    protected $base_version = 'v1';
    protected $account;
    protected $key;
    protected $product_code;
    protected $client;

    public function __construct($account, $key, $tz)
    {
        include_once('vendor/autoload.php');
        $this->key     = $key;
        $this->account = $account;
        $this->tz      = $tz;

        $this->product_code = "AGILE-18-02-21";
        $this->base_url     = "https://api.octopus.energy/$this->base_version/";
        $this->product_url  = "products/$this->product_code/";

        $this->client = new Client([
                                       'base_uri' => $this->base_url,
                                   ]);
    }

    public function getMeterPointDetails($mpan)
    {
        if (empty($mpan) || strlen($mpan) !== 13) {
            return [];
        }
        try {
            $uri    = (strlen($mpan) === 13 ? 'electricity' : 'gas')."-meter-points/{$mpan}/";
            $return = $this->doConnection($uri);
        } catch (Exception $e) {
            $return = "{}";
        }

        return $return;
    }

    private function doConnection($uri)
    {
        return $this->client->get($uri, [
            'auth' => [
                $this->key,
                $this->key,
            ],
        ])
                            ->getBody()
                            ->getContents();
    }

    public function getMeterPointConsumption($mpan = null, $serial = null, $date = null)
    {
        if (empty($mpan) || empty($serial)) {
            return [];
        }

        $date = empty($date) ? Carbon::parse('yesterday', $this->tz) : Carbon::parse($date);

        try {
            $uri = (strlen($mpan) === 13 ? 'electricity' : 'gas')."-meter-points/{$mpan}/meters/{$serial}/consumption?period_from=".$date->copy()
                                                                                                                                         ->startOfDay()
                                                                                                                                         ->timezone('utc')
                                                                                                                                         ->toIso8601ZuluString()."&period_to=".$date->copy()
                                                                                                                                                                                    ->endOfDay()
                                                                                                                                                                                    ->timezone('utc')
                                                                                                                                                                                    ->toIso8601ZuluString();

            $return = $this->doConnection($uri);
        } catch (Exception $e) {
            $return = "{}";
        }

        return $return;
    }

    public function getElectricityPrice($region, $including_vat = true, $specific_datetime = null)
    {
        $datetime      = empty($specific_datetime) ? Carbon::now() : Carbon::parse($specific_datetime);
        $current_price = 0;
        $tariffs       = $this->getHalfHourlyRates($region, $datetime);
        foreach ($tariffs as $tariff) {
            $valid_from = Carbon::parse($tariff->valid_from);
            $valid_to   = Carbon::parse($tariff->valid_to);
            if ($datetime->greaterThanOrEqualTo($valid_from) && $datetime->lessThan($valid_to)) {
                if ($including_vat === true) {
                    $current_price = $tariff->value_inc_vat;
                }
                else {
                    $current_price = $tariff->value_exc_vat;
                }
            }
        }

        return $current_price;
    }

    public function getHalfHourlyRates($region, $date = null)
    {
        $datetime = empty($date) ? Carbon::now($this->tz) : Carbon::parse($date)
                                                                  ->setTimezone($this->tz);
        try {
            $response = json_decode($this->doConnection($this->getTariffURL($region)."?period_from=".$datetime->copy()
                                                                                                              ->startOfDay()
                                                                                                              ->timezone('utc')
                                                                                                              ->toIso8601ZuluString()."&period_to=".$datetime->copy()
                                                                                                                                                             ->endOfDay()
                                                                                                                                                             ->timezone('utc')
                                                                                                                                                             ->toIso8601ZuluString()));

            //convert back to the given timezone
            foreach ($response->results as $result) {
                $result->valid_from = Carbon::parse($result->valid_from)
                                            ->timezone($this->tz);
                $result->valid_to   = Carbon::parse($result->valid_to)
                                            ->timezone($this->tz);
            }

            $tariffs = $response->results;
        } catch (Exception $e) {
            $tariffs = "{}";
        }

        return $tariffs;
    }

    private function getTariffURL($region)
    {
        $tariff_code = "E-1R-$this->product_code-$region";

        return $this->product_url."electricity-tariffs/$tariff_code/standard-unit-rates";
    }

}
