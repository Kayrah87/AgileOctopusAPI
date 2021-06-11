<?php

use Carbon\Carbon;

include('src/API.php');
include_once('vendor/autoload.php');

/**
 * Edit this detail with your own from https://octopus.energy/dashboard/developer
 */
$api_key            = '';
$account            = 'A-';
$electricity_mpan   = '';
$electricity_serial = '';
$gas_mprn           = '';
$gas_serial         = '';
$region             = 'B';
$tz                 = "Europe/London";
$gas_unit_price     = 2.9;

//===================================

$styles = [
    'hourly_rates' => [
        'cell'          => "class='sm:w-1/4 lg:w-1/5 text-xs text-center justify-center align-middle'",
        'cell_hideable' => "class='sm:w-1/4 lg:w-1/5 sm:hidden lg:block sm:text-md lg:text-xs text-center justify-center align-middle'",
    ],
    'consumption'  => [
        'cell'          => "class='sm:w-1/5 lg:w-1/6 text-xs text-center justify-center align-middle'",
        'cell_hideable' => "class='sm:w-1/5 lg:w-1/6 sm:hidden lg:block sm:text-md lg:text-xs text-center justify-center align-middle'",
    ],
];

//create the API object
$api = new kayrah87\AgileOctopusAPI\API($account, $api_key, $tz);

//get the relevant data from the wrapper
$electricity_point       = $api->getMeterPointDetails($electricity_mpan)['data'];
$electricity_consumption = $api->getMeterPointConsumption($electricity_mpan, $electricity_serial, Carbon::now($tz)
                                                                                                        ->subDays(3))['data'];
$gas_consumption         = $api->getMeterPointConsumption($gas_mprn, $gas_serial, Carbon::now($tz)
                                                                                        ->subDays(3))['data'];
?>

<head>
    <title>Kayrah87/AgileOctopusAPI</title>
    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-300">
<div class="flex flex-col w-full p-5">
    <div class="mb-5 flex items-center p-3">
        <h1 class="font-bold text-xl">Kayrah87/AgileOctopusAPI</h1>
    </div>
    <div class="mb-5 flex flex-col p-3">
        <p>Welcome to the Agile Octopus API Wrapper.</p>
        <p>This may be included in any PHP project and will soon be adapted for Laravel</p>

        <h2 class="mt-3 mb-3 text-xl font-bold">Basic Usage</h2>

        <p>installing Agile Octopus API is as simple as requiring it through composer</p>
        <p class="bg-gray-400 p-3 rounded-md"><code>$ composer require kayrah87/agileoctopusapi</code></p>
        <p>Once it is included in your project you can instantiate the API in your project thusly:</p>
        <p class="bg-gray-400 p-3 rounded-md"><code>$api = new kayrah87\AgileOctopusAPI\API($account, $api_key,
                $tz);</code></p>
        <p><code>$account</code> is your Octopus account number</p>
        <p><code>$api_key</code> is your Octopus API key</p>
        <p><code>$tz</code> is your Timezone, most usually 'Europe/London' but Octopus is available in other countries
            now, so might be worth checking.</p>
    </div>
    <h2 class="ml-3 mt-3 mb-3 text-xl font-bold">Examples</h2>
    <div class="ml-3 mb-5 flex items-center mr-2">
        <div class="lg:w-1/2 w-full">
            <p class="bg-gray-400 p-3 rounded-md mt-3 mb-3"><code>$api->getElectricityPrice($region)</code></p>
            <p class="mb-2"><code>$region</code> is the DNO code from the region you wish to get the price for. For more
                information or if you are not sure, see <a
                        href="https://www.energy-stats.uk/dno-region-codes-explained/"
                        class="text-yellow-600 hover:text-yellow-500">energy-stats.uk</a></p>
            <div class="widget w-full p-4 rounded-lg bg-white border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                <div class="flex flex-row items-center justify-between">
                    <div class="flex flex-col">
                        <div class="text-xs uppercase font-light text-gray-500">
                            Current Electricity Price
                        </div>
                        <div class="text-xl font-bold">
                            <?php
                            echo $api->getElectricityPrice($region)['data']." p/Kwh";
                            ?>
                        </div>
                    </div>
                    <svg class="stroke-current text-gray-500" fill="none" height="24" stroke="currentColor"
                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12">
                        </polyline>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="ml-3 flex w-full justify-between flex-wrap">
        <div class="lg:w-1/2 sm:w-full">
            <div class="items-center mr-2 mb-2 widget p-4 rounded-lg bg-white border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                <div class="p-2 text-lg font-bold"><h1>&#9889; Half-Hourly Electricity Prices</h1></div>
                <div class="mb-2 p-2 border-4 border-purple-300 rounded-lg">
                    <p>The half hourly electricity prices can be called using</p>
                    <p class="bg-gray-400 p-3 rounded-md mt-3 mb-3"><code>$api->getHalfHourlyRates($region)</code></p>
                    <p>where <code>$region</code> is the DNO for the desired region.</p>

                </div>
                <table class="text-left w-full">
                    <thead class="bg-black flex text-white w-full">
                    <tr class="flex w-full text-sm">
                        <th <?php
                        echo $styles['hourly_rates']['cell_hideable']; ?>>Date
                        </th>
                        <th <?php
                        echo $styles['hourly_rates']['cell']; ?>>From
                        </th>
                        <th <?php
                        echo $styles['hourly_rates']['cell']; ?>>To
                        </th>
                        <th <?php
                        echo $styles['hourly_rates']['cell']; ?>>Price inc. VAT
                        </th>
                        <th <?php
                        echo $styles['hourly_rates']['cell']; ?>>Pice exc. VAT
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-grey-light flex flex-col items-center justify-between overflow-y-scroll w-full h-64">
                    <?php

                    foreach ($api->getHalfHourlyRates($region)['data'] as $rate) {
                        echo "<tr class=\"flex w-full pl-2\">
<td {$styles['hourly_rates']['cell_hideable']}>".Carbon::parse($rate->valid_from)
                                                       ->format('d/m/Y')."</td>
<td {$styles['hourly_rates']['cell']}>".Carbon::parse($rate->valid_from)
                                              ->format('H:i')."</td>
<td {$styles['hourly_rates']['cell']}>".Carbon::parse($rate->valid_to)
                                              ->format('H:i')."</td>
<td {$styles['hourly_rates']['cell']}>".round($rate->value_inc_vat, 1)."</td>
<td {$styles['hourly_rates']['cell']}>".round($rate->value_exc_vat, 1)."</td></tr>";
                    }

                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="lg:w-1/2 sm:w-full">
            <div class="items-center mr-2 mb-2 widget p-4 rounded-lg bg-white border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                <div class="p-2 text-lg font-bold"><h1>&#9889; Electricity Meter Point Details</h1></div>
                <div class="mb-2 p-2 border-4 border-purple-300 rounded-lg">
                    <p>The meter point details (for electric only) can be called using</p>
                    <p class="bg-gray-400 p-3 rounded-md mt-3 mb-3">
                        <code>$api->getMeterPointDetails($electricity_mpan)</code></p>
                    <p>where <code>$electricity_mpan</code> is the mpan from your electricity meter found under your
                        developer dashboard.</p>
                </div>

                <ul class="flex flex-col p-4">
                    <li class="border-gray-400 flex flex-row mb-2">
                        <div class="bg-gray-200 rounded-md flex flex-1 items-center p-4  transition duration-500 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex flex-col rounded-md w-10 h-10 bg-gray-300 justify-center items-center mr-4">
                                &#9889;
                            </div>
                            <div class="flex-1 pl-1 mr-16">
                                <div class="font-medium">GSP</div>
                            </div>
                            <div class="text-gray-600 text-xs"><?php
                                echo $electricity_point->gsp ?? '' ?></div>
                        </div>
                    </li>
                    <li class="border-gray-400 flex flex-row mb-2">
                        <div class="bg-gray-200 rounded-md flex flex-1 items-center p-4  transition duration-500 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex flex-col rounded-md w-10 h-10 bg-gray-300 justify-center items-center mr-4">
                                &#9889;
                            </div>
                            <div class="flex-1 pl-1 mr-16">
                                <div class="font-medium">MPAN</div>
                            </div>
                            <div class="text-gray-600 text-xs"><?php
                                echo $electricity_point->mpan ?? '' ?></div>
                        </div>
                    </li>
                    <li class="border-gray-400 flex flex-row mb-2">
                        <div class="bg-gray-200 rounded-md flex flex-1 items-center p-4  transition duration-500 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex flex-col rounded-md w-10 h-10 bg-gray-300 justify-center items-center mr-4">
                                &#9889;
                            </div>
                            <div class="flex-1 pl-1 mr-16">
                                <div class="font-medium">Profile Class</div>
                            </div>
                            <div class="text-gray-600 text-xs"><?php
                                echo $electricity_point->profile_class ?? '' ?></div>
                        </div>
                    </li>
                </ul>

            </div>
        </div>
        <div class="lg:w-1/2 sm:w-full">
            <div class="items-center mr-2 mb-2 widget p-4 rounded-lg bg-white border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                <div class="p-2 text-lg font-bold"><h1>&#9889; Electricity Meter Point Consumption</h1></div>
                <div class="mb-2 p-2 border-4 border-purple-300 rounded-lg">
                    <p>The electricity meter point half-hourly consumption can be called using</p>
                    <p class="bg-gray-400 p-3 rounded-md mt-3 mb-3"><code>$api->getMeterPointConsumption($electricity_mpan,
                            $electricity_serial, $date)</code></p>
                    <p><code>$electricity_mpan</code> is the mpan from your electricity meter found under your developer
                        dashboard.</p>
                    <p><code>$electricity_serial</code> is the serial number from your electricity meter found under
                        your developer dashboard.</p>
                    <p><code>$date</code> is the date you want to get the consumption for.</p>
                </div>

                <table class="text-left w-full">
                    <thead class="bg-black flex text-white w-full">
                    <tr class="flex w-full text-sm">
                        <th <?php
                        echo $styles['consumption']['cell_hideable']; ?>>Date
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>From
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>To
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>Usage
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>Cost exc. VAT
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>Cost inc. VAT
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-grey-light flex flex-col items-center justify-between overflow-y-scroll w-full h-64">
                    <?php

                    foreach (($electricity_consumption->results ?? []) as $key => $consumption_period) {
                        echo "<tr class=\"flex w-full pl-2\">
<td {$styles['consumption']['cell_hideable']}>".Carbon::parse($consumption_period->interval_start)
                                                      ->format('d/m/Y')."</td>
<td {$styles['consumption']['cell']}>".Carbon::parse($consumption_period->interval_start)
                                             ->format('H:i')."</td>
<td {$styles['consumption']['cell']}>".Carbon::parse($consumption_period->interval_end)
                                             ->format('H:i')."</td>
<td {$styles['consumption']['cell']}>".round($consumption_period->consumption, 3)."</td>
<td {$styles['consumption']['cell']}>".round(($consumption_period->consumption * $api->getElectricityPrice($region,
                                                                                                           false,
                                                                                                           $consumption_period->interval_start)['data']),
                                1)." p</td>
<td {$styles['consumption']['cell']}>".round(($consumption_period->consumption * $api->getElectricityPrice($region, true,
                                                                                                          $consumption_period->interval_start)['data']),
                                             1)." p</td>
</tr>";
                    }

                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="lg:w-1/2 sm:w-full">
            <div class="items-center mr-2 mb-2 widget p-4 rounded-lg bg-white border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                <div class="p-2 text-lg font-bold"><h1>&#128293; Gas Meter Point Consumption</h1></div>

                <div class="mb-2 p-2 border-4 border-purple-300 rounded-lg">
                    <p>The gas meter point half-hourly consumption can be
                        called using</p>
                    <p class="bg-gray-400 p-3 rounded-md mt-3 mb-3"><code>$api->getMeterPointConsumption($gas_mprn,
                            $gas_serial, $date)</code></p>
                    <p><code>$gas_mprn</code> is the MPRN from your gas meter found under your developer dashboard.</p>
                    <p><code>$electricity_serial</code> is the serial number from your gas meter found under your
                        developer dashboard.</p>
                    <p><code>$date</code> is the date you want to get the consumption for.</p>
                </div>

                <table class="text-left w-full">
                    <thead class="bg-black flex text-white w-full">
                    <tr class="flex w-full text-sm">
                        <th <?php
                        echo $styles['consumption']['cell_hideable']; ?>>Date
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>From
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>To
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>Usage
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>Cost exc. VAT
                        </th>
                        <th <?php
                        echo $styles['consumption']['cell']; ?>>Cost inc. VAT
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-grey-light flex flex-col items-center justify-between overflow-y-scroll w-full h-64">
                    <?php

                    foreach (($gas_consumption->results ?? []) as $key => $consumption_period) {
                        echo "<tr class=\"flex w-full pl-2\">
<td {$styles['consumption']['cell_hideable']}>".Carbon::parse($consumption_period->interval_start)
                                                      ->format('d/m/Y')."</td>
<td {$styles['consumption']['cell']}>".Carbon::parse($consumption_period->interval_start)
                                             ->format('H:i')."</td>
<td {$styles['consumption']['cell']}>".Carbon::parse($consumption_period->interval_end)
                                             ->format('H:i')."</td>
<td {$styles['consumption']['cell']}>".round($consumption_period->consumption, 3)."</td>
<td {$styles['consumption']['cell']}>".round($consumption_period->consumption * $gas_unit_price, 1)." p</td>
<td {$styles['consumption']['cell']}>".round($consumption_period->consumption * $gas_unit_price, 1)." p</td>
</tr>";
                    }

                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</body>