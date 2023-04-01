# MapyCZ API PHP wrapper

Simple API wrapper for making requests to [Mapy.cz](https://mapy.cz/) created by the Czech company [Seznam.cz](https://seznam.cz/). No API credentials are required.

[![Packagist Version](https://img.shields.io/packagist/v/DJTommek/mapycz-api?label=Packagist&style=flat-square)](https://packagist.org/packages/djtommek/mapycz-api)
[![GitHub Repo stars](https://img.shields.io/github/stars/DJTommek/mapycz-api?label=Github%20stars&style=flat-square)](https://github.com/DJTommek/mapycz-api)

## Installation
```
composer require djtommek/mapycz-api
```

## Usage example
```php
<?php
$api = new \DJTommek\MapyCzApi\MapyCzApi();
$place = $api->loadPoiDetails('base', 2107710);
printf('lat: %F, lon: %F', $place->getLat(), $place->getLon());
// lat: 50.132131, lon: 16.313767
```

See [tests](tests/MapyCzApiTest.php) or [examples](examples/) for more.

## Testing
```
composer test
```
Note: The tests are making actual requests to the [Mapy.cz](https://mapy.cz/) website.

## Detailed info
[Mapy.cz](https://mapy.cz/) frontend is communicating with backend via FastRPC requests and responses, which are XML-RPC binary encoded by their own custom encoder written in Javascript. See [github.com/seznam/fastrpc](https://github.com/seznam/fastrpc) or [seznam.github.io/frpc](https://seznam.github.io/frpc/).

Instead of creating PHP implementation of this FastRPC encoder/decoder, this wrapper is using HTTP headers:
- `Content-Type: text/xml` to send requests using classic formatted in [XML-RPC](https://wikipedia.org/wiki/XML-RPC).
- `Accept: application/json` to receive responses in [JSON](https://wikipedia.org/wiki/JSON).

### Disclaimer
- [Mapy.cz](https://mapy.cz/) can change any time without any warning.
- Personally, I would recommend not making extensive or malicious requests to prevent being banned. More info can be found on [api.mapy.cz](https://api.mapy.cz/).
