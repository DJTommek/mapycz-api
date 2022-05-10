<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$api = new \DJTommek\MapyCzApi\MapyCzApi();
$bestPanorama = $api->loadPanoramaGetBest(50.087726, 14.421127);
var_dump($bestPanorama);
