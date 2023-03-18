<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi;

use DJTommek\MapyCzApi\Types\PanoramaBestType;
use DJTommek\MapyCzApi\Types\PanoramaNeighbourType;
use DJTommek\MapyCzApi\Types\PanoramaType;
use DJTommek\MapyCzApi\Types\PlaceType;
use DJTommek\MapyCzApi\Types\ReverseGeocodeType;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

class MapyCzApi
{
	const API_URL = 'https://pro.mapy.cz';
	const API_URL_PUBLIC = 'https://api.mapy.cz';

	private const API_ENDPOINT_POI = '/poiagg';
	private const API_ENDPOINT_PANORAMA = '/panorpc';
	private const API_ENDPOINT_REVERSE_GEOCODE = '/rgeocode';

	private const API_METHOD_DETAIL = 'detail';
	private const API_METHOD_GET_NEIGHBOURS = 'getneighbours';
	private const API_METHOD_LOOKUP_BOX = 'lookupbox';
	private const API_METHOD_GETBEST = 'getbest';

	// Known "source" parameters accepted in $this->loadPoiDetails()
	public const SOURCE_COOR = 'coor';
	public const SOURCE_BASE = 'base';
	public const SOURCE_FIRM = 'firm';
	public const SOURCE_PUBT = 'pubt';
	public const SOURCE_TRAF = 'traf';
	public const SOURCE_FOTO = 'foto';
	public const SOURCE_OSM = 'osm';

	private ?ClientInterface $client = null;

	private function getClient(): ClientInterface
	{
		if ($this->client === null) {
			$this->client = new \GuzzleHttp\Client();
		}
		return $this->client;
	}

	/**
	 * Set custom client, which will be used to send requests, otherwise default \GuzzleHttp\Client will be used.
	 * Also you can set custom instance of \GuzzleHttp\Client with your own options, eg with updated timeouts.
	 * See tests for example.
	 */
	public function setClient(ClientInterface $client): self
	{
		$this->client = $client;
		return $this;
	}

	/**
	 * @throws ClientExceptionInterface
	 * @throws MapyCzApiException
	 * @throws \JsonException
	 */
	public function loadPoiDetails(string $source, int $id): PlaceType
	{
		$xmlBody = $this->generateXmlRequest(self::API_METHOD_DETAIL, $source, $id);
		$response = $this->makeApiRequest(self::API_ENDPOINT_POI, $xmlBody);
		return PlaceType::cast($response->poi);
	}

	/**
	 * @throws ClientExceptionInterface
	 * @throws MapyCzApiException
	 * @throws \JsonException
	 */
	public function loadPanoramaDetails(int $id): PanoramaType
	{
		$body = $this->generateXmlRequest(self::API_METHOD_DETAIL, $id);
		$response = $this->makeApiRequest(self::API_ENDPOINT_PANORAMA, $body);
		return PanoramaType::cast($response->result);
	}

	/**
	 * @return PanoramaNeighbourType[]
	 * @throws ClientExceptionInterface
	 * @throws MapyCzApiException
	 * @throws \JsonException
	 */
	public function loadPanoramaNeighbours(int $id): array
	{
		$body = $this->generateXmlRequest(self::API_METHOD_GET_NEIGHBOURS, $id);
		$response = $this->makeApiRequest(self::API_ENDPOINT_PANORAMA, $body);
		return PanoramaNeighbourType::createFromResponse($response);
	}

	public function reverseGeocode(float $lat, float $lon): ReverseGeocodeType
	{
		$response = $this->makePublicApiRequest(self::API_ENDPOINT_REVERSE_GEOCODE, [
			'lat' => $lat,
			'lon' => $lon,
		]);
		return ReverseGeocodeType::cast($response);
	}

	/**
	 * @TODO set array shape for parameter $options
	 *
	 * @return PlaceType[]
	 * @throws ClientExceptionInterface
	 * @throws MapyCzApiException
	 * @throws \JsonException
	 */
	public function loadLookupBox(float $lon1, float $lat1, float $lon2, float $lat2, $options): array
	{
		$xmlBody = $this->generateXmlRequest(self::API_METHOD_LOOKUP_BOX, $lon1, $lat1, $lon2, $lat2, $options);
		$response = $this->makeApiRequest(self::API_ENDPOINT_POI, $xmlBody);
		$places = [];
		foreach ($response->poi as $poi) {
			$places[] = PlaceType::cast($poi);
		}
		return $places;
	}

	/**
	 * Try to find best suitable panorama for given coordinates. Returns null if no suitable Panorama was found.
	 *
	 * @throws ClientExceptionInterface
	 * @throws MapyCzApiException
	 * @throws \JsonException
	 */
	public function loadPanoramaGetBest(float $lon, float $lat, float $radius = 50): ?PanoramaBestType
	{
		try {
			$xmlBody = $this->generateXmlRequest(self::API_METHOD_GETBEST, $lon, $lat, $radius);
			$response = $this->makeApiRequest(self::API_ENDPOINT_PANORAMA, $xmlBody);
			return PanoramaBestType::createFromResponse($response->result);
		} catch (MapyCzApiException $exception) {
			if ($exception->getCode() === 404) {
				return null;
			}

			if (str_starts_with($exception->getMessage(), 'No best panorama for point')) {
				// Full example: 'No best panorama for point 50.93E 35.82N and radius 50.00m found!'
				return null;
			}

			throw $exception;
		}
	}

	/**
	 * @throws MapyCzApiException
	 * @throws ClientExceptionInterface
	 * @throws \JsonException
	 */
	private function makeApiRequest(string $endpoint, \SimpleXMLElement $rawPostContent): \stdClass
	{
		$request = new \GuzzleHttp\Psr7\Request(
			method: 'POST',
			uri: self::API_URL . $endpoint,
			headers: [
				'Accept' => 'application/json',
				'Content-Type' => 'text/xml',
			],
			body: $rawPostContent->asXML()
		);
		$response = $this->getClient()->sendRequest($request);
		$body = (string)$response->getBody();
		$content = \json_decode($body, false, 512, JSON_THROW_ON_ERROR);
		if (isset($content->failure) && isset($content->failureMessage)) {
			throw new MapyCzApiException($content->failureMessage, $content->failure);
		}
		if ($content->status === 200 && mb_strtolower($content->statusMessage) === 'ok') {
			return $content;
		} else {
			throw new MapyCzApiException($content->statusMessage, $content->status);
		}
	}

	private function makePublicApiRequest(string $endpoint, array $parameters = []): \SimpleXMLElement
	{
		$url = self::API_URL_PUBLIC . $endpoint;
		if (count($parameters) > 0) {
			$url .= '?' . http_build_query($parameters);
		}

		$request = new \GuzzleHttp\Psr7\Request(
			method: 'GET',
			uri: $url
		);

		$response = $this->getClient()->sendRequest($request);
		$body = (string)$response->getBody();
		return new \SimpleXMLElement($body);
	}

	/**
	 * @param mixed ...$params parameters for methodName
	 * @return \SimpleXMLElement
	 */
	private function generateXmlRequest(string $methodName, ...$params): \SimpleXMLElement
	{
		/**
		 * Workaround to create XML without root element.
		 * @see https://stackoverflow.com/questions/486757/how-to-generate-xml-file-dynamically-using-php#comment22868318_487282
		 * @see https://www.php.net/manual/en/simplexmlelement.construct.php#119447
		 */
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><methodCall></methodCall>');
		$xml->addChild('methodName', $methodName);
		$methodParams = $xml->addChild('params');
		foreach ($params as $param) {
			$xmlParam = $methodParams->addChild('param');
			$xmlValue = $xmlParam->addChild('value');
			self::fillXmlParam($xmlValue, $param);
		}
		return $xml;
	}

	/**
	 * Build XML structure into given XML element based on parameters for API request.
	 *
	 * @param \SimpleXMLElement $xml
	 * @param int|float|string|\stdClass $param
	 */
	private static function fillXmlParam(\SimpleXMLElement $xml, $param): void
	{
		if (is_int($param)) {
			$xml->addChild('int', strval($param));
		} else if (is_float($param)) {
			$xml->addChild('double', strval($param));
		} else if (is_string($param)) {
			$xml->addChild('string', $param);
		} else if ($param instanceof \stdClass) {
			$xmlStruct = $xml->addChild('struct');
			foreach ($param as $structName => $structValue) {
				$xmlStructMember = $xmlStruct->addChild('member');
				$xmlStructMember->addChild('name', $structName);
				$xmlStructMemberValue = $xmlStructMember->addChild('value');
				self::fillXmlParam($xmlStructMemberValue, $structValue);
			}
		} else {
			throw new \InvalidArgumentException(sprintf('Unexpected type "%s" of parameter.', gettype($param)));
		}
	}
}
