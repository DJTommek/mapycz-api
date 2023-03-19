<?php declare(strict_types=1);

use DJTommek\MapyCzApi\MapyCzApi;
use DJTommek\MapyCzApi\Types\ReverseGeocodeItemType;
use DJTommek\MapyCzApi\Types\ReverseGeocodeType;
use DJTommek\MapyCzApi\Types\Type;
use PHPUnit\Framework\TestCase;

/**
 * Test which are running mocked requests, which means not running actual requests to API servers, but faking requests
 * by providing responses from pre-saved fixtures stored locally. All check should be as precise as possible
 */
final class MapyCzApiMockupTest extends TestCase
{
	private MapyCzApi $api;
	private \GuzzleHttp\Handler\MockHandler $mock;

	public function setUp(): void
	{
		$this->mock = new \GuzzleHttp\Handler\MockHandler();
		$handlerStack = \GuzzleHttp\HandlerStack::create($this->mock);
		$client = new GuzzleHttp\Client([
			'handler' => $handlerStack,
		]);

		$this->api = new MapyCzApi();
		$this->api->setClient($client);
	}

	private function assertCoords(float $latExpected, float $lonExpected, Type|ReverseGeocodeType|ReverseGeocodeItemType $object): void
	{
		$latReal = $object->getLat();
		$lonReal = $object->getLon();
		$this->assertSame($latExpected, $latReal, sprintf('Failed asserting that latitude %s matches expected %s', $latReal, $latExpected));
		$this->assertSame($lonExpected, $lonReal, sprintf('Failed asserting that longitude %s matches expected %s', $lonReal, $lonExpected));
	}

	/**
	 * @dataProvider poiDetailsProvider
	 * @param array{float, float} $expected
	 */
	public function testLoadPoiDetails(string $filename, array $expected): void
	{
		[$latExpected, $lonExpected] = $expected;
		$this->setMockup($filename);
		$place = $this->api->loadPoiDetails('mocked', 123456);
		$this->assertCoords($latExpected, $lonExpected, $place);
	}

	public function testLoadPoiDetailsError1(): void
	{
		$this->setMockup('poiDetailsNotFound1.json');
		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionMessage('Not found!');
		$this->api->loadPoiDetails('base', 1234);
	}

	public function testLoadPoiDetailsError2(): void
	{
		$this->setMockup('poiDetailsNotFound2.json');
		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionMessage('Not Found');
		$this->api->loadPoiDetails('osm', 99999999999);
	}

	public function testLoadPoiDetailsError3(): void
	{
		$this->setMockup('poiDetailsError3.json');
		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionMessage('Cannot find any server handling source invalid-source');
		$this->expectExceptionCode(404);
		$this->api->loadPoiDetails('invalid-source', 2107710);
	}

	public function testLoadPanoramaNeighbours1(): void
	{
		$this->setMockup('panoramaNeighbours1.json');
		$neighbours = $this->api->loadPanoramaNeighbours(68059377);
		$this->assertCount(2, $neighbours);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[0]);
		$neighbours[0]->angle = 39.423730238676058;
		$this->assertNull($neighbours[0]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[0]->near);
		$this->assertCoords(50.075994572837196, 15.0168167856528, $neighbours[0]);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[1]);
		$neighbours[1]->angle = 219.52328536981966;
		$this->assertNull($neighbours[1]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[1]->near);
		$this->assertCoords(50.075924190323875, 15.016726675411652, $neighbours[1]);
	}

	public function testLoadPanoramaNeighbours2(): void
	{
		$this->setMockup('panoramaNeighbours2.json');
		$neighbours = $this->api->loadPanoramaNeighbours(70254688);
		$this->assertCount(3, $neighbours);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[0]);
		$neighbours[0]->angle = 87.359772499162261;
		$this->assertNull($neighbours[0]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[0]->near);
		$this->assertCoords(50.07849937463579, 14.488475318684442, $neighbours[0]);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[1]);
		$neighbours[1]->angle = 157.34892725846578;
		$this->assertNull($neighbours[1]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[1]->near);
		$this->assertCoords(50.078453682198258, 14.488397090543286, $neighbours[1]);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[2]);
		$neighbours[2]->angle = 337.56931566125115;
		$this->assertNull($neighbours[2]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[2]->near);
		$this->assertCoords(50.078537667591313, 14.488341870960509, $neighbours[2]);
	}

	public function testLoadPanoramaNeighboursError1(): void
	{
		$this->setMockup('panoramaNeighboursError1.json');
		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionMessage('Panorama with id \'99999999999\' not found!');
		$this->api->loadPanoramaNeighbours(99999999999);
	}

	/**
	 * @dataProvider reverseGeocodeProvider
	 * @param array{float, float} $expected
	 */
	public function testReverseGeocode(string $filename, array $expected): void
	{
		[$expectedAddress, $expectedLat, $expectedLon] = $expected;
		$this->setMockup($filename);

		$data = $this->api->reverseGeocode(12.34, 12.34);
		$this->assertSame(200, $data->status);
		$this->assertSame('Ok', $data->message);
		$this->assertSame($expectedAddress, $data->getAddress());
		$this->assertCoords($expectedLat, $expectedLon, $data);
	}

	/**
	 * More detailed test
	 */
	public function testReverseGeocode1(): void
	{
		$this->setMockup('reverseGeocode1.xml');
		$data = $this->api->reverseGeocode(12.34, 12.34);

		$this->assertSame(200, $data->status);
		$this->assertSame('Ok', $data->message);

		$this->assertCount(8, $data->items);

		$item = $data->items[0];
		$this->assertSame(8939832, $item->id);
		$this->assertSame('Staroměstské náměstí 606/11', $item->name);
		$this->assertSame('addr', $item->source);
		$this->assertSame('addr', $item->type);
		$this->assertCoords(50.08801489569467, 14.421563306025112, $item);

		$item = $data->items[4];
		$this->assertSame(3468, $item->id);
		$this->assertSame('Praha', $item->name);
		$this->assertSame('muni', $item->source);
		$this->assertSame('muni', $item->type);
		$this->assertCoords(50.0835493857, 14.4341412988, $item);
	}

	public function testLoadReverseGeocodeError1(): void
	{
		$this->setMockup('reverseGeocodeError1.xml');
		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionMessage('No data, are coordinates valid?');
		$this->api->reverseGeocode(50.133923, 514.409660);
	}

	public function testLoadLookupBox(): void
	{
		$this->setMockup('loadLookupBox1.json');

		$options = new stdClass();
		$options->zoom = 13;
		$options->mapsetId = 1;
		$places = $this->api->loadLookupBox(14.099642, 49.997597, 14.367434, 50.102973, $options);
		$this->assertCount(8, $places);

		$place = $places[0];
		$this->assertSame('Obchvat Jinočan - Okružní ulice', $place->title);
		$this->assertCoords(50.033941076300003, 14.2750335485, $place);

		$place = $places[3];
		$this->assertSame('Letiště Václava Havla Praha (PRG)', $place->title);
		$this->assertCoords(50.108395197299998, 14.2621233398, $place);
	}

	public function testLoadLookupBoxError1(): void
	{
		$this->setMockup('loadLookupBoxError1.json');

		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionCode(-502);
		$this->expectExceptionMessage('Key "zoom" does not exist.');

		$options = new stdClass();
		$this->api->loadLookupBox(14.099642, 49.997597, 14.367434, 50.102973, $options);
	}

	public function testLoadPanoramaGetBest1(): void
	{
		$this->setMockup('panoramaGetBest1.json');

		$bestPanorama = $this->api->loadPanoramaGetBest(14.421127, 50.087726);
		$this->assertSame(70102895, $bestPanorama->pid);
		$this->assertEqualsWithDelta(255.76090162311178, $bestPanorama->azimuth, 0.0001);
		$this->assertEqualsWithDelta(27.56126704628921, $bestPanorama->distance, 0.0001);
		$this->assertEqualsWithDelta(255.76090162311178, $bestPanorama->lookDir, 0.0001);
		$this->assertSame(50, $bestPanorama->radius);
		$this->assertSame('https://mapy.cz/zakladni?x=14.421499685654&y=50.087788829892&pano=1&pid=70102895&yaw=4.4638698311926', $bestPanorama->getLink());
	}

	public function testLoadPanoramaGetBestError1(): void
	{
		$this->setMockup('panoramaGetBestError1.json');

		$bestPanorama = $this->api->loadPanoramaGetBest(14.421127, 50.087726, 10);
		$this->assertNull($bestPanorama);
	}

	/**
	 * @return array{array{string, array{string, float, float}}}
	 */
	public function reverseGeocodeProvider(): array
	{
		return [
			['reverseGeocode1.xml', ['Staroměstské náměstí 606/11, Praha, 110 00, Hlavní město Praha', 50.08801489569467, 14.421563306025112]],
			['reverseGeocode2.xml', ['Plaza de Santa Ana', 40.41480397411152, -3.700791339178318]],
			['reverseGeocode3.xml', ['Atlantský oceán', 13.58192094506344, -38.32031204752161]],
		];
	}

	/**
	 * @return array{array{string, array{float, float}}}
	 */
	public function poiDetailsProvider(): array
	{
		return [
			['poiDetailsBase1.json', [50.132131399999999, 16.313767200000001]],
			['poiDetailsPubt1.json', [50.084007263183594, 14.440339088439941]],
			['poiDetailsFirm1.json', [50.084747314453125, 14.454011917114258]],
			['poiDetailsOsm1.json', [-45.870288951383145, -67.50777737380889]],
		];
	}

	private function setMockup(string $filename): void
	{
		$mockJson = file_get_contents(__DIR__ . '/fixtures/' . $filename);
		assert(is_string($mockJson));
		$mockedResponse = new \GuzzleHttp\Psr7\Response(200, body: $mockJson);
		$this->mock->append($mockedResponse);
	}
}
