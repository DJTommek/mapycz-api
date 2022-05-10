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
		$this->assertEquals(-18.917167018396825, $place->getLat());
		$this->assertEquals(47.53575634915991, $place->getLon());

		$place = $this->api->loadPoiDetails('osm', 17164289);
		$this->assertEquals(-45.870288951383145, $place->getLat());
		$this->assertEquals(-67.50777737380889, $place->getLon());
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
		$this->assertCount(4, $neighbours);
		$this->assertNull($neighbours[0]->far);
		$this->assertEquals(50.078590071104, $neighbours[0]->getLat());
		$this->assertEquals(14.488226145758, $neighbours[0]->getLon());
		$this->assertNull($neighbours[1]->far);
		$this->assertEquals(50.078571741589, $neighbours[1]->getLat());
		$this->assertEquals(14.488507011519, $neighbours[1]->getLon());
		$this->assertNull($neighbours[2]->far);
		$this->assertEquals(50.078562904609, $neighbours[2]->getLat());
		$this->assertEquals(14.488405598016, $neighbours[2]->getLon());
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
		$this->assertSame('Staroměstské náměstí 606/11, Praha, 110 00, Hlavní město Praha', $data->label);
		$this->assertSame('Ok', $data->message);
		$this->assertSame(200, $data->status);
		$this->assertSame('Staroměstské náměstí 606/11, Praha, 110 00, Hlavní město Praha', $data->getAddress());
		$this->assertSame(50.08801489569467, $data->getLat());
		$this->assertSame(14.421563306025112, $data->getLon());

		$this->assertCount(8, $data->items);

		$this->assertSame(8939832, $data->items[0]->id);
		$this->assertSame('Staroměstské náměstí 606/11', $data->items[0]->name);
		$this->assertSame('addr', $data->items[0]->source);
		$this->assertSame('addr', $data->items[0]->type);
		$this->assertSame(14.421563306025112, $data->items[0]->x);
		$this->assertSame(50.08801489569467, $data->items[0]->y);
		$this->assertSame(50.08801489569467, $data->items[0]->getLat());
		$this->assertSame(14.421563306025112, $data->items[0]->getLon());

		$this->assertSame(121933, $data->items[1]->id);
		$this->assertSame('Staroměstské náměstí', $data->items[1]->name);
		$this->assertSame('stre', $data->items[1]->source);
		$this->assertSame('stre', $data->items[1]->type);
		$this->assertSame(14.420509314368898, $data->items[1]->x);
		$this->assertSame(50.08777079179988, $data->items[1]->y);
		$this->assertSame(50.08777079179988, $data->items[1]->getLat());
		$this->assertSame(14.420509314368898, $data->items[1]->getLon());

		$this->assertSame(87, $data->items[2]->id);
		$this->assertSame('Praha 1', $data->items[2]->name);
		$this->assertSame('quar', $data->items[2]->source);
		$this->assertSame('quar', $data->items[2]->type);
		$this->assertSame(14.424132200124081, $data->items[2]->x);
		$this->assertSame(50.08783679317715, $data->items[2]->y);
		$this->assertSame(50.08783679317715, $data->items[2]->getLat());
		$this->assertSame(14.424132200124081, $data->items[2]->getLon());

		$this->assertSame(13674, $data->items[3]->id);
		$this->assertSame('Staré Město', $data->items[3]->name);
		$this->assertSame('ward', $data->items[3]->source);
		$this->assertSame('ward', $data->items[3]->type);
		$this->assertSame(14.417781898902357, $data->items[3]->x);
		$this->assertSame(50.084551750804636, $data->items[3]->y);
		$this->assertSame(50.084551750804636, $data->items[3]->getLat());
		$this->assertSame(14.417781898902357, $data->items[3]->getLon());

		$this->assertSame(3468, $data->items[4]->id);
		$this->assertSame('Praha', $data->items[4]->name);
		$this->assertSame('muni', $data->items[4]->source);
		$this->assertSame('muni', $data->items[4]->type);
		$this->assertSame(14.4341412988, $data->items[4]->x);
		$this->assertSame(50.0835493857, $data->items[4]->y);
		$this->assertSame(50.0835493857, $data->items[4]->getLat());
		$this->assertSame(14.4341412988, $data->items[4]->getLon());

		$this->assertSame(47, $data->items[5]->id);
		$this->assertSame('Okres Hlavní město Praha', $data->items[5]->name);
		$this->assertSame('dist', $data->items[5]->source);
		$this->assertSame('dist', $data->items[5]->type);
		$this->assertSame(14.466000012808934, $data->items[5]->x);
		$this->assertSame(50.066789200117995, $data->items[5]->y);
		$this->assertSame(50.066789200117995, $data->items[5]->getLat());
		$this->assertSame(14.466000012808934, $data->items[5]->getLon());

		$this->assertSame(10, $data->items[6]->id);
		$this->assertSame('Hlavní město Praha', $data->items[6]->name);
		$this->assertSame('regi', $data->items[6]->source);
		$this->assertSame('regi', $data->items[6]->type);
		$this->assertSame(14.466, $data->items[6]->x);
		$this->assertSame(50.066789, $data->items[6]->y);
		$this->assertSame(50.066789, $data->items[6]->getLat());
		$this->assertSame(14.466, $data->items[6]->getLon());

		$this->assertSame(112, $data->items[7]->id);
		$this->assertSame('Česko', $data->items[7]->name);
		$this->assertSame('coun', $data->items[7]->source);
		$this->assertSame('coun', $data->items[7]->type);
		$this->assertSame(15.338411, $data->items[7]->x);
		$this->assertSame(49.742858, $data->items[7]->y);
		$this->assertSame(49.742858, $data->items[7]->getLat());
		$this->assertSame(15.338411, $data->items[7]->getLon());


		$data = $this->api->reverseGeocode(50.133923, 14.409660);
		$this->assertSame('Dolákova, Praha, Hlavní město Praha', $data->getAddress());
		$this->assertSame('Ok', $data->message);
		$this->assertSame(200, $data->status);
		$this->assertSame('Dolákova, Praha, Hlavní město Praha', $data->getAddress());
		$this->assertSame(50.13358200394987, $data->getLat());
		$this->assertSame(14.406162964024444, $data->getLon());
		$this->assertCount(7, $data->items);


		$data = $this->api->reverseGeocode(40.414711, -3.700830);
		$this->assertSame('Plaza de Santa Ana', $data->getAddress());
		$this->assertSame('Ok', $data->message);
		$this->assertSame(200, $data->status);
		$this->assertSame('Plaza de Santa Ana', $data->getAddress());
		$this->assertSame(40.41480397411152, $data->getLat());
		$this->assertSame(-3.700791339178318, $data->getLon());
		$this->assertCount(7, $data->items);

		$this->assertSame(1023817397, $data->items[0]->id);
		$this->assertSame('Plaza de Santa Ana', $data->items[0]->name);
		$this->assertSame('osm', $data->items[0]->source);
		$this->assertSame('osms', $data->items[0]->type);
		$this->assertSame(-3.700791339178318, $data->items[0]->x);
		$this->assertSame(40.41480397411152, $data->items[0]->y);
		$this->assertSame(40.41480397411152, $data->items[0]->getLat());
		$this->assertSame(-3.700791339178318, $data->items[0]->getLon());


		$data = $this->api->reverseGeocode(0, 0);
		$this->assertSame('Atlantský oceán', $data->getAddress());
		$this->assertCount(1, $data->items);

		$this->assertSame(112794890, $data->items[0]->id);
		$this->assertSame('Atlantic Ocean', $data->items[0]->name);
		$this->assertSame('osm', $data->items[0]->source);
		$this->assertSame('osma', $data->items[0]->type);
		$this->assertSame(-38.32031204752161, $data->items[0]->x);
		$this->assertSame(13.58192094506344, $data->items[0]->y);
		$this->assertSame(13.58192094506344, $data->items[0]->getLat());
		$this->assertSame(-38.32031204752161, $data->items[0]->getLon());
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
		$this->assertCount(7, $places);

		// @TODO results are kind of random - so just checking, that no exception is thrown.
//		$this->assertEquals('Obchvat Jinočan - Okružní ulice', $places[0]->title);
//		$this->assertEquals(50.0339410763, $places[0]->getLat());
//		$this->assertEquals(14.2750335485, $places[0]->getLon());
//		$this->assertEquals('Letiště Václava Havla Praha (PRG)', $places[3]->title);
//		$this->assertEquals(50.1083951973, $places[3]->getLat());
//		$this->assertEquals(14.2621233398, $places[3]->getLon());
	}

	public function testLoadLookupBoxError1(): void
	{
		$this->expectException(MapyCzApiException::class);
		$this->expectExceptionMessage('Key "zoom" does not exist.');
		$options = new stdClass();
		$this->api->loadLookupBox(14.099642, 49.997597, 14.367434, 50.102973, $options);
	}
}
