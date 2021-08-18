<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$api = new \DJTommek\MapyCzApi\MapyCzApi();
$place = $api->loadPoiDetails('base', 1832651);
// Place: Pomník Mistra Jana Husa, lat: 50.087726, lon: 14.421127
printf('Place: %s, lat: %F, lon: %F', $place->title, $place->getLat(), $place->getLon());
