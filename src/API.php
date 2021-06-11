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

    /**
     * @param $mpan
     *
     * @return array|string
     */
    public function getMeterPointDetails($mpan)
    {
        if (empty($mpan) || strlen($mpan) !== 13) {
            return $this->buildResponse('Error', [], 'Incorrect or missing MPAN');
        }
        try {
            $uri = (strlen($mpan) === 13 ? 'electricity' : 'gas')."-meter-points/{$mpan}/";


            return $this->buildResponse('OK', json_decode($this->doConnection($uri)));
        } catch (Exception $e) {
            return $this->buildResponse('Error', [], $e->getMessage());
        }
    }

    /**
     * @param $uri
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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

    /**
     * @param  $mpan
     * @param  $serial
     * @param  null  $date
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMeterPointConsumption(string $mpan, string $serial, string $date = null): array
    {
        if (empty($mpan) || empty($serial)) {
            return $this->buildResponse('Error', [], 'Not enough input to proceed');

        }

        $date = empty($date) ? Carbon::parse('yesterday', $this->tz) : Carbon::parse($date);

        try {
            //convert given datetimes to a UTC zulu string - the API's weapon of choice
            $period_to   = $date->copy()
                                ->endOfDay()
                                ->timezone('utc')
                                ->toIso8601ZuluString();
            $period_from = $date->copy()
                                ->startOfDay()
                                ->timezone('utc')
                                ->toIso8601ZuluString();

            //build the URI
            $uri = (strlen($mpan) === 13 ? 'electricity' : 'gas')."-meter-points/{$mpan}/meters/{$serial}/consumption?period_from={$period_from}&period_to={$period_to}";

            return $this->buildResponse('OK', json_decode($this->doConnection($uri)));
        } catch (Exception $e) {
            return $this->buildResponse('Error', [], $e->getMessage());

        }
    }

    /**
     * @param $region
     * @param  bool  $including_vat
     * @param  null  $specific_datetime
     *
     * @return mixed
     */
    public function getElectricityPrice($region, $including_vat = true, $specific_datetime = null) :array
    {
        $datetime = empty($specific_datetime) ? Carbon::now() : Carbon::parse($specific_datetime);
        $tariffs  = $this->getHalfHourlyRates($region, $datetime)['data'];
        foreach ($tariffs as $tariff) {
            $valid_from = Carbon::parse($tariff->valid_from);
            $valid_to   = Carbon::parse($tariff->valid_to);
            if ($datetime->greaterThanOrEqualTo($valid_from) && $datetime->lessThan($valid_to)) {
                if ($including_vat === true) {
                    return $this->buildResponse('OK', $tariff->value_inc_vat);
                }
                else {
                    return $this->buildResponse('OK', $tariff->value_exc_vat);
                }
            }
        }
        return $this->buildResponse('Error', 0, 'No electricity prices found');
    }

    /**
     * @param $region
     * @param  null  $date
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHalfHourlyRates($region, $date = null):array
    {
        if(empty($region)) {
            return $this->buildResponse('Error', [], 'Not enough input to proceed');

        }
        $datetime = empty($date) ? Carbon::now($this->tz) : Carbon::parse($date)
                                                                  ->setTimezone($this->tz);
        try {
            //convert given datetimes to a UTC zulu string - the API's weapon of choice
            $period_to   = $datetime->copy()
                                    ->endOfDay()
                                    ->timezone('utc')
                                    ->toIso8601ZuluString();
            $period_from = $datetime->copy()
                                    ->startOfDay()
                                    ->timezone('utc')
                                    ->toIso8601ZuluString();

            $tariff_url = $this->getTariffURL($region)."?period_from={$period_from}&period_to={$period_to}";

            $response = json_decode($this->doConnection($tariff_url));
            //convert back to the given timezone
            foreach ($response->results as $result) {
                $result->valid_from = Carbon::parse($result->valid_from)
                                            ->timezone($this->tz);
                $result->valid_to   = Carbon::parse($result->valid_to)
                                            ->timezone($this->tz);
            }

            return $this->buildResponse('OK', $response->results);

        } catch (Exception $e) {
            return $this->buildResponse('Error', [], $e->getMessage());

        }
    }

    /**
     * @param $region
     *
     * @return string
     *
     * takes the region and builds a URL for retrieving tariff information
     *
     */
    private function getTariffURL($region)
    {
        if(empty($region)) {
            return $this->buildResponse('Error', [], 'Not enough input to proceed');
        }

        $tariff_code = "E-1R-$this->product_code-$region";

        return $this->product_url."electricity-tariffs/$tariff_code/standard-unit-rates";
    }

    private function buildResponse($status, $data, $message = null)
    {
        return [
                               'status' => [
                                   'response' => $status,
                                   'reason'   => $message ?? 'OK',
                               ],
                               'data' => $data
                           ];
    }

}
