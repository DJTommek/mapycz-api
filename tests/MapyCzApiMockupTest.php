<?php declare(strict_types=1);

use DJTommek\MapyCzApi\MapyCzApi;
use PHPUnit\Framework\TestCase;

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

	private function assertCoordsDelta(float $latExpected, float $lonExpected, \DJTommek\MapyCzApi\Types\Type $object): void
	{
		$latReal = $object->getLat();
		$lonReal = $object->getLon();
		$this->assertEqualsWithDelta($latExpected, $latReal, 0.00001, sprintf('Failed asserting that latitude %s matches expected %s', $latReal, $latExpected));
		$this->assertEqualsWithDelta($lonExpected, $lonReal, 0.00001, sprintf('Failed asserting that longitude %s matches expected %s', $lonReal, $lonExpected));
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
		$this->assertCoordsDelta($latExpected, $lonExpected, $place);
	}

	public function testLoadPoiDetailsError1(): void
	{
		$this->setMockup('poiDetailsNotFound1');
		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionMessage('Not found!');
		$this->api->loadPoiDetails('base', 1234);
	}


	public function testLoadPoiDetailsError2(): void
	{
		$this->setMockup('poiDetailsNotFound2');
		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionMessage('Not Found');
		$this->api->loadPoiDetails('osm', 99999999999);
	}

	public function testLoadPanoramaNeighbours1(): void
	{
		$this->setMockup('panoramaNeighbours1');
		$neighbours = $this->api->loadPanoramaNeighbours(68059377);
		$this->assertCount(2, $neighbours);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[0]);
		$neighbours[0]->angle = 39.423730238676058;
		$this->assertNull($neighbours[0]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[0]->near);
		$this->assertCoordsDelta(50.075994572837196, 15.0168167856528, $neighbours[0]);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[1]);
		$neighbours[1]->angle = 219.52328536981966;
		$this->assertNull($neighbours[1]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[1]->near);
		$this->assertCoordsDelta(50.075924190323875, 15.016726675411652, $neighbours[1]);
	}

	public function testLoadPanoramaNeighbours2(): void
	{
		$this->setMockup('panoramaNeighbours2');
		$neighbours = $this->api->loadPanoramaNeighbours(70254688);
		$this->assertCount(3, $neighbours);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[0]);
		$neighbours[0]->angle = 87.359772499162261;
		$this->assertNull($neighbours[0]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[0]->near);
		$this->assertCoordsDelta(50.078499374636, 14.488475318684, $neighbours[0]);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[1]);
		$neighbours[1]->angle = 157.34892725846578;
		$this->assertNull($neighbours[1]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[1]->near);
		$this->assertCoordsDelta(50.078453682198, 14.488397090543, $neighbours[1]);

		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaNeighbourType::class, $neighbours[2]);
		$neighbours[2]->angle = 337.56931566125115;
		$this->assertNull($neighbours[2]->far);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaType::class, $neighbours[2]->near);
		$this->assertCoordsDelta(50.078537667591, 14.488341870961, $neighbours[2]);
	}

	public function testLoadPanoramaNeighboursError1(): void
	{
		$this->setMockup('panoramaNeighboursError1');
		$this->expectException(\DJTommek\MapyCzApi\MapyCzApiException::class);
		$this->expectExceptionMessage('Panorama with id \'99999999999\' not found!');
		$this->api->loadPanoramaNeighbours(99999999999);
	}

	/**
	 * @return array{array{string, array{float, float}}}
	 */
	public function poiDetailsProvider(): array
	{
		return [
			['poiDetailsBase1', [50.132131399999999, 16.313767200000001]],
			['poiDetailsPubt1', [50.084007263183594, 14.440339088439941]],
			['poiDetailsFirm1', [50.084747314453125, 14.454011917114258]],
			['poiDetailsOsm1', [-45.870288951383145, -67.50777737380889]],
		];
	}

	private function setMockup(string $filename): void
	{
		$mockJson = file_get_contents(__DIR__ . '/fixtures/' . $filename . '.json');
		assert(is_string($mockJson));
		$mockedResponse = new \GuzzleHttp\Psr7\Response(200, body: $mockJson);
		$this->mock->append($mockedResponse);
	}
}
