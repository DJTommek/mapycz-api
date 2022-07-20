<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi\JAK;

use PHPUnit\Framework\TestCase;

final class CoordsTest extends TestCase
{
	public function testStringToCoords(): void
	{
		$this->assertSame([['x' => 14.61182713508606, 'y' => 50.07315695285797]], Coords::stringToCoords('9hd0GxXt2G'));
		$this->assertSame([
			['x' => 13.780299425125122, 'y' => 49.91954952478409],
			['x' => 13.809481859207153, 'y' => 49.87952224910259],
			['x' => 13.859606981277466, 'y' => 49.92441236972809],
			['x' => 13.894282579421997, 'y' => 49.90208297967911],
		], Coords::stringToCoords('9fJgGxW.HqkQ0xWn3F9fWDGxX0wGlQ0xW9oq'));
	}

	public function testCoordsToString(): void
	{
		$this->assertSame('9hd0GxXt2G', Coords::coordsToString([[14.61182713508606, 50.07315695285797]]));
		$this->assertSame('9fJgGxW.HqkQ0xWn3F9fWDGxX0wGlQ0xW9oq', Coords::coordsToString([
			[13.780299425125122, 49.91954952478409],
			[13.809481859207153, 49.87952224910259],
			[13.859606981277466, 49.92441236972809],
			[13.894282579421997, 49.90208297967911],
		]));
	}

	public function testParseNumber(): void
	{
		$a = ['G', '2', 't', 'X', 'x'];
		$this->assertSame(15846853, Coords::_parseNumber($a, 4));
	}

	public function testSerializeNumber(): void
	{
		$this->assertSame('9hd0G', Coords::_serializeNumber(145113096, 145113096));
		$this->assertSame('xWn3F', Coords::_serializeNumber(-59693, 208603463));
	}
}
