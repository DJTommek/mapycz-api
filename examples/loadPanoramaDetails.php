<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$api = new \DJTommek\MapyCzApi\MapyCzApi();
$place = $api->loadPanoramaDetails(68059377);
// lat: 50.075959, lon: 15.016772
printf('lat: %F, lon: %F', $place->getLat(), $place->getLon());
