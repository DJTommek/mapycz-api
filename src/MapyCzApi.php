<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi;

use DJTommek\MapyCzApi\Types\PanoramaNeighbourType;
use DJTommek\MapyCzApi\Types\PanoramaType;
use DJTommek\MapyCzApi\Types\PlaceType;
use DJTommek\MapyCzApi\Types\ReverseGeocodeType;

class MapyCzApi
{
	const API_URL = 'https://pro.mapy.cz';
	const API_URL_PUBLIC = 'https://api.mapy.cz';

	private const API_ENDPOINT_POI = '/poiagg';
	private const API_ENDPOINT_PANORAMA = '/panorpc';
	private const API_ENDPOINT_REVERSE_GEOCODE = '/rgeocode';

	private const API_METHOD_DETAIL = 'detail';
	private const API_METHOD_GET_NEIGHBOURS = 'getneighbours';

	// Known "source" parameters accepted in $this->loadPoiDetails()
	public const SOURCE_COOR = 'coor';
	public const SOURCE_BASE = 'base';
	public const SOURCE_FIRM = 'firm';
	public const SOURCE_PUBT = 'pubt';
	public const SOURCE_TRAF = 'traf';
	public const SOURCE_FOTO = 'foto';
	public const SOURCE_OSM = 'osm';

	/** @throws MapyCzApiException|\JsonException */
	public function loadPoiDetails(string $source, int $id): PlaceType
	{
		$xmlBody = $this->generateXmlRequest(self::API_METHOD_DETAIL, $source, $id);
		$response = $this->makeApiRequest(self::API_ENDPOINT_POI, $xmlBody);
		return PlaceType::cast($response->poi);
	}

	/** @throws MapyCzApiException|\JsonException */
	public function loadPanoramaDetails(int $id): PanoramaType
	{
		$body = $this->generateXmlRequest(self::API_METHOD_DETAIL, $id);
		$response = $this->makeApiRequest(self::API_ENDPOINT_PANORAMA, $body);
		return PanoramaType::cast($response->result);
	}

	/**
	 * @return PanoramaNeighbourType[]
	 * @throws MapyCzApiException|\JsonException
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

	/** @throws MapyCzApiException|\JsonException */
	private function makeApiRequest(string $endpoint, \SimpleXMLElement $rawPostContent): \stdClass
	{
		$response = Utils::fileGetContents(self::API_URL . $endpoint, [
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $rawPostContent->asXML(),
			CURLOPT_HTTPHEADER => [
				'Accept: application/json',
				'Content-Type: text/xml',
			],
		]);
		$content = \json_decode($response, false, 512, JSON_THROW_ON_ERROR);
		if (isset($content->failure) && isset($content->failureMessage)) {
			throw new MapyCzApiException($content->failureMessage, -501);
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
		$response = Utils::fileGetContents($url);
		return new \SimpleXMLElement($response);
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
