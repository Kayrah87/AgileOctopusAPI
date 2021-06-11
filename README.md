# AgileOctopusAPI
Welcome to the Agile Octopus API Wrapper.<br>
This may be included in any PHP project and will soon be adapted for Laravel

You may access all of the information you require for this package at [Octopus Energy]('https://octopus.energy/dashboard/developer)

***

##Basic Usage
installing Agile Octopus API is as simple as requiring it through composer

`$ composer require kayrah87/agileoctopusapi`

Once it is included in your project you can instantiate the API in your project thusly:

`$api = new kayrah87\AgileOctopusAPI\API($account, $api_key, $tz);`

`$account` is your Octopus account number.<br>
`$api_key` is your Octopus API key.<br>
`$tz` is your Timezone, most usually 'Europe/London' but Octopus is available in other countries now, so might be worth checking.

##Examples

###⚡ Current Electricity Price<br>
`$api->getElectricityPrice($region)`

`$region` is the DNO code from the region you wish to get the price for. For more information or if you are not sure, see [energy-stats.uk](https://www.energy-stats.uk/dno-region-codes-explained/)

###⚡ Half-Hourly Electricity Prices<br>
The half hourly electricity prices can be called using

`$api->getHalfHourlyRates($region)`

where `$region` is the DNO for the desired region.

###⚡ Electricity Meter Point Details

The meter point details (for electric only) can be called using

`$api->getMeterPointDetails($electricity_mpan)`

where `$electricity_mpan` is the mpan from your electricity meter found under your developer dashboard.

This will return an object with the following properties:<br>
GSP<br>
MPAN<br>
Profile Class

###⚡ Electricity Meter Point Consumption
The electricity meter point half-hourly consumption can be called using

`$api->getMeterPointConsumption($electricity_mpan, $electricity_serial, $date)`

`$electricity_mpan` is the mpan from your electricity meter found under your developer dashboard.<br>
`$electricity_serial` is the serial number from your electricity meter found under your developer dashboard.<br>
`$date` is the date you want to get the consumption for.

###🔥 Gas Meter Point Consumption
The gas meter point half-hourly consumption can be called using

`$api->getMeterPointConsumption($gas_mprn, $gas_serial, $date)`

`$gas_mprn` is the MPRN from your gas meter found under your developer dashboard.<br>
`$electricity_serial` is the serial number from your gas meter found under your developer dashboard.
`$date` is the date you want to get the consumption for.

***
##Queries
If you have any queries please raise a github issue.
If you like this API then consider telling your friends about it and earn both them and me £50 in the process:
[Here](https://share.octopus.energy/blue-rook-804)