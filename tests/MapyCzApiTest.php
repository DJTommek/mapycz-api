<?php declare(strict_types=1);

use DJTommek\MapyCzApi\MapyCzApi;
use DJTommek\MapyCzApi\MapyCzApiException;
use PHPUnit\Framework\TestCase;

final class MapyCzApiTest extends TestCase
{
	/** @var MapyCzApi */
	private $api;

	public function setUp(): void
	{
		$this->api = new MapyCzApi();
	}

	public function testLoadPoiDetails(): void
	{
		$place = $this->api->loadPoiDetails('base', 2107710);
		$this->assertEquals(50.132131399999999, $place->getLat());
		$this->assertEquals(16.313767200000001, $place->getLon());

		$place = $this->api->loadPoiDetails('pubt', 15308193);
		$this->assertEquals(50.084007263183594, $place->getLat());
		$this->assertEquals(14.440339088439941, $place->getLon());

		$place = $this->api->loadPoiDetails('firm', 468797);
		$this->assertEquals(50.084747314453125, $place->getLat());
		$this->assertEquals(14.454011917114258, $place->getLon());

		$place = $this->api->loadPoiDetails('traf', 15659817);
		$this->assertEquals(50.093311999999997, $place->getLat());
		$this->assertEquals(14.455159, $place->getLon());

		$place = $this->api->loadPoiDetails('foto', 1080344);
		$this->assertEquals(49.993611111100002, $place->getLat());
		$this->assertEquals(14.205277777799999, $place->getLon());

		$place = $this->api->loadPoiDetails('base', 1833337);
		$this->assertEquals(50.1066236375, $place->getLat());
		$this->assertEquals(14.3662025489, $place->getLon());

		$place = $this->api->loadPoiDetails('osm', 112448327);
		$this->assertEquals(49.444980051414653, $place->getLat());
		$this->assertEquals(11.109054822801225, $place->getLon());

		$place = $this->api->loadPoiDetails('osm', 1000536418);
		$this->assertEquals(54.766918429542365, $place->getLat());
		$this->assertEquals(-101.8737286610846, $place->getLon());

		$place = $this->api->loadPoiDetails('osm', 1040985945);
		$this->assertEquals(-18.917187302602514, $place->getLat());
		$this->assertEquals(47.535795241190634, $place->getLon());

		$place = $this->api->loadPoiDetails('osm', 17164289);
		$this->assertEquals(-45.870330022708686, $place->getLat());
		$this->assertEquals(-67.507560031059569, $place->getLon());
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
		$this->expectExceptionMessage('Cannot find any server handling source invalid-source');
		$this->api->loadPoiDetails('invalid-source', 2107710);
	}

	public function testLoadPanoramaDetails(): void
	{
		$place = $this->api->loadPanoramaDetails(68059377);
		$this->assertEquals(50.075959341112629, $place->getLat());
		$this->assertEquals(15.016771758436011, $place->getLon());

		$place = $this->api->loadPanoramaDetails(66437731);
		$this->assertEquals(50.123351288859986, $place->getLat());
		$this->assertEquals(16.284569347024281, $place->getLon());

		$place = $this->api->loadPanoramaDetails(68007689);
		$this->assertEquals(50.094952509980317, $place->getLat());
		$this->assertEquals(15.023081103835427, $place->getLon());

		$place = $this->api->loadPanoramaDetails(70254688);
		$this->assertEquals(50.078495759444145, $place->getLat());
		$this->assertEquals(14.488369277220368, $place->getLon());
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
		$this->assertEquals(50.075994572837189, $neighbours[0]->getLat());
		$this->assertEquals(15.0168167856528, $neighbours[0]->getLon());
		$this->assertNull($neighbours[1]->far);
		$this->assertEquals(50.075924190323875, $neighbours[1]->getLat());
		$this->assertEquals(15.016726675411652, $neighbours[1]->getLon());

		$neighbours = $this->api->loadPanoramaNeighbours(66437731);
		$this->assertCount(2, $neighbours);
		$this->assertNull($neighbours[0]->far);
		$this->assertEquals(50.123325864977183, $neighbours[0]->getLat());
		$this->assertEquals(16.284511364095028, $neighbours[0]->getLon());
		$this->assertNull($neighbours[1]->far);
		$this->assertEquals(50.123376594103632, $neighbours[1]->getLat());
		$this->assertEquals(16.284626669316179, $neighbours[1]->getLon());

		$neighbours = $this->api->loadPanoramaNeighbours(68007689);
		$this->assertCount(2, $neighbours);
		$this->assertNull($neighbours[0]->far);
		$this->assertEquals(50.094968792948613, $neighbours[0]->getLat());
		$this->assertEquals(15.023015652760325, $neighbours[0]->getLon());
		$this->assertNull($neighbours[1]->far);
		$this->assertEquals(50.09493643386277, $neighbours[1]->getLat());
		$this->assertEquals(15.023146415156226, $neighbours[1]->getLon());

		$neighbours = $this->api->loadPanoramaNeighbours(70254688);
		$this->assertCount(3, $neighbours);
		$this->assertNull($neighbours[0]->far);
		$this->assertEquals(50.07849937463579, $neighbours[0]->getLat());
		$this->assertEquals(14.488475318684442, $neighbours[0]->getLon());
		$this->assertNull($neighbours[1]->far);
		$this->assertEquals(50.078453682198258, $neighbours[1]->getLat());
		$this->assertEquals(14.488397090543286, $neighbours[1]->getLon());
		$this->assertNull($neighbours[2]->far);
		$this->assertEquals(50.07853766759132, $neighbours[2]->getLat());
		$this->assertEquals(14.488341870960509, $neighbours[2]->getLon());
	}

	public function testLoadloadPanoramaNeighboursError1(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('Panorama with id \'99999999999\' not found!');
		$this->api->loadPanoramaNeighbours(99999999999);
	}

}
