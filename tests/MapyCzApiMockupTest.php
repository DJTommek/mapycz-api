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
		$mockJson = file_get_contents(__DIR__ . '/fixtures/' . $filename . '.json');
		$mockedResponse = new \GuzzleHttp\Psr7\Response(200, body: $mockJson);
		$this->mock->append($mockedResponse);
		$place = $this->api->loadPoiDetails('mocked', 123456);
		$this->assertCoordsDelta($latExpected, $lonExpected, $place);
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
}
