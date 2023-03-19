<?php declare(strict_types=1);

use DJTommek\MapyCzApi\MapyCzApi;
use DJTommek\MapyCzApi\MapyCzApiException;
use PHPUnit\Framework\TestCase;

/**
 * Test with running real requests. Data on API are often changing so tests are not precise:
 * - precision of coordinates checking is very low
 * - check just chunk of output (eg not whole address, but part of it such as city name)
 * - check type of output (eg address is string)
 * - check at least minimum count (eg returning at least few POIs from lookup)
 *
 * @group request
 */
final class MapyCzApiRealTest extends TestCase
{
	private MapyCzApi $api;

	public function setUp(): void
	{
		$this->api = new MapyCzApi();
	}

	private function assertCoordsDelta(float $latExpected, float $lonExpected, $object): void
	{
		$latReal = $object->getLat();
		$lonReal = $object->getLon();
		// Very low coordinate precision due to often changes on API server
		$this->assertEqualsWithDelta($latExpected, $latReal, 0.001, sprintf('Failed asserting that latitude %s matches expected %s', $latReal, $latExpected));
		$this->assertEqualsWithDelta($lonExpected, $lonReal, 0.001, sprintf('Failed asserting that longitude %s matches expected %s', $lonReal, $lonExpected));
	}

	private function assertNotEmptyString(mixed $input): void
	{
		$this->assertTrue(is_string($input));
		$this->assertTrue(trim($input) !== '');
		assert(is_string($input));
	}

	/**
	 * @dataProvider poiDetailsProvider
	 */
	public function testLoadPoiDetails(array $parameters, array $expected): void
	{
		$place = $this->api->loadPoiDetails(...$parameters);
		$this->assertCoordsDelta($expected[0], $expected[1], $place);
	}

	public function poiDetailsProvider(): array
	{
		return [
			[['base', 2107710], [50.132131399999999, 16.313767200000001]],
			[['pubt', 15308193], [50.084007263183594, 14.440339088439941]],
			[['firm', 468797], [50.084747314453125, 14.454011917114258]],
			[['traf', 15659817], [50.093311999999997, 14.455159]],
			[['foto', 1080344], [49.993611111100002, 14.205277777799999]],
			[['base', 1833337], [50.1066236375, 14.3662025489]],
			[['osm', 112448327], [49.444980051414653, 11.109054822801225]],
			[['osm', 1000536418], [54.766918429542365, -101.8737286610846]],
			[['osm', 1040985945], [-18.917167018396825, 47.53575634915991]],
			[['osm', 17164289], [-45.870288951383145, -67.50777737380889]],
		];

	}

	public function testLoadPoiDetailsError1(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('Not found!');
		$this->api->loadPoiDetails('base', 1234);
	}

	public function testLoadPoiDetailsError2(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('Not Found');
		$this->api->loadPoiDetails('osm', 99999999999);
	}

	public function testLoadPoiDetailsError3(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('Cannot find any server handling source invalid-source');
		$this->api->loadPoiDetails('invalid-source', 2107710);
	}

	/**
	 * @dataProvider panoramaDetailsProvider
	 */
	public function testLoadPanoramaDetails(array $params, array $expected): void
	{
		$panoramaId = $params[0];
		$place = $this->api->loadPanoramaDetails($panoramaId);
		$this->assertCoordsDelta($expected[0], $expected[1], $place);
	}

	public function panoramaDetailsProvider(): array
	{
		return [
			[[68059377], [50.075959341112629, 15.016771758436011]],
			[[66437731], [50.123351288859986, 16.284569347024281]],
			[[68007689], [50.094952509980317, 15.023081103835427]],
			[[70254688], [50.078495759444145, 14.488369277220368]],
		];
	}

	public function testLoadPanoramaDetailsError1(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('Panorama with id \'99999999999\' not found!');
		$this->api->loadPanoramaDetails(99999999999);
	}

	public function testLoadPanoramaNeighbours(): void
	{
		$neighbours = $this->api->loadPanoramaNeighbours(68059377);
		$this->assertCount(2, $neighbours);
		$this->assertNull($neighbours[0]->far);
		$this->assertNull($neighbours[1]->far);
		$this->assertCoordsDelta(50.075994572837189, 15.0168167856528, $neighbours[0]);
		$this->assertCoordsDelta(50.075924190323875, 15.016726675411652, $neighbours[1]);

		$neighbours = $this->api->loadPanoramaNeighbours(66437731);
		$this->assertCount(2, $neighbours);
		$this->assertNull($neighbours[0]->far);
		$this->assertNull($neighbours[1]->far);
		$this->assertCoordsDelta(50.123325864977183, 16.284511364095028, $neighbours[0]);
		$this->assertCoordsDelta(50.123376594103632, 16.284626669316179, $neighbours[1]);

		$neighbours = $this->api->loadPanoramaNeighbours(68007689);
		$this->assertCount(2, $neighbours);
		$this->assertNull($neighbours[0]->far);
		$this->assertNull($neighbours[1]->far);
		$this->assertCoordsDelta(50.094968792948613, 15.023015652760325, $neighbours[0]);
		$this->assertCoordsDelta(50.09493643386277, 15.023146415156226, $neighbours[1]);

		$neighbours = $this->api->loadPanoramaNeighbours(70254688);
		$this->assertCount(3, $neighbours);
		$this->assertNull($neighbours[0]->far);
		$this->assertNull($neighbours[1]->far);
		$this->assertNull($neighbours[2]->far);
		$this->assertCoordsDelta(50.078499374636, 14.488475318684, $neighbours[0]);
		$this->assertCoordsDelta(50.078453682198, 14.488397090543, $neighbours[1]);
		$this->assertCoordsDelta(50.078537667591, 14.488341870961, $neighbours[2]);
	}

	public function testLoadloadPanoramaNeighboursError1(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('Panorama with id \'99999999999\' not found!');
		$this->api->loadPanoramaNeighbours(99999999999);
	}

	public function testLoadReverseGeocode(): void
	{
		$data = $this->api->reverseGeocode(50.088024, 14.421580);
		$address = $data->getAddress();
		$this->assertNotEmptyString($address);
		$this->assertStringContainsString('Staroměstské náměstí', $address);
		$this->assertStringContainsString('Praha', $address);
		$this->assertSame('Ok', $data->message);
		$this->assertSame(200, $data->status);
		$this->assertCoordsDelta(50.08801489569467, 14.421563306025112, $data);

		$this->assertTrue(count($data->items) > 5);

		$matchedCount = 0;

		foreach ($data->items as $item) {
			if ($item->type === 'coun') {
				$this->assertSame('Česko', $item->name);
				$matchedCount++;
			} else if ($item->type === 'muni') {
				$this->assertSame('Praha', $item->name);
				$matchedCount++;
			} else if ($item->type === 'stre') {
				$this->assertSame('Staroměstské náměstí', $item->name);
				$matchedCount++;
			} else if ($item->type === 'addr') {
				$this->assertStringContainsString('Staroměstské náměstí', $item->name);
				$matchedCount++;
			}

		}
		$this->assertSame(4, $matchedCount, sprintf('Some item are missing, matched only %d out of %d.', $matchedCount, 4));
	}

	public function testLoadReverseGeocodeError1(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('No data, are coordinates valid?');
		$this->api->reverseGeocode(50.133923, 514.409660);
	}

	public function testLoadLookupBox(): void
	{
		$options = new stdClass();
		$options->zoom = 13;
		$options->mapsetId = 1;
		$places = $this->api->loadLookupBox(14.099642, 49.997597, 14.367434, 50.102973, $options);

		// Results are kind of random - so this is just general check
		$this->assertTrue(count($places) > 0);
		foreach ($places as $place) {
			$this->assertNotEmptyString($place->title);
			$this->assertIsFloat($place->getLat());
			$this->assertIsFloat($place->getLon());
		}
	}

	public function testLoadLookupBoxError1(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('Key "zoom" does not exist.');
		$options = new stdClass();
		$this->api->loadLookupBox(14.099642, 49.997597, 14.367434, 50.102973, $options);
	}

	public function testLoadPanoramaGetBest(): void
	{
		$bestPanorama = $this->api->loadPanoramaGetBest(14.421127, 50.087726);
		$this->assertInstanceOf(\DJTommek\MapyCzApi\Types\PanoramaBestType::class, $bestPanorama);
		$this->assertIsInt($bestPanorama->pid);
		$this->assertIsFloat($bestPanorama->azimuth);
		$this->assertIsFloat($bestPanorama->distance);
		$this->assertIsFloat($bestPanorama->lookDir);
		$this->assertSame(50, $bestPanorama->radius);
		$link = $bestPanorama->getLink();
		$this->assertNotEmptyString($link);
		$this->assertStringStartsWith('https://mapy.cz/zakladni?x=', $link);
		$this->assertStringContainsString('&pano=1&', $link);
	}

	public function testLoadPanoramaGetBestNotFound(): void
	{
		$bestPanorama = $this->api->loadPanoramaGetBest(14.421127, 50.087726, 10);
		$this->assertNull($bestPanorama);
	}
}
