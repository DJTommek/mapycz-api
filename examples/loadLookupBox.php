<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$api = new \DJTommek\MapyCzApi\MapyCzApi();
$options = new stdClass();
$options->zoom = 13;
$options->mapsetId = 1;
$places = $api->loadLookupBox(14.099642, 49.997597, 14.367434, 50.102973, $options);
var_dump(sprintf('Discovered %d places:', count($places)));
foreach ($places as $place) {
	var_dump(sprintf('Place: %s, lat: %F, lon: %F', $place->title, $place->getLat(), $place->getLon()));
}
