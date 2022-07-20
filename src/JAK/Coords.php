<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi\JAK;

/**
 * Utils rewritten from Seznam.cz JAK library
 *
 * @link https://github.com/seznam/JAK
 * @author Tomas Palider (DJTommek) https://tomas.palider.cz/
 */
class Coords
{
	private const _ALPHABET = ['0', 'A', 'B', 'C', 'D', '2', 'E', 'F', 'G', 'H', '4', 'I', 'J', 'K', 'L', 'M', 'N', '6', 'O', 'P', 'Q', 'R', 'S', 'T', '8', 'U', 'V', 'W', 'X', 'Y', 'Z', '-', '1', 'a', 'b', 'c', 'd', '3', 'e', 'f', 'g', 'h', '5', 'i', 'j', 'k', 'l', 'm', 'n', '7', 'o', 'p', 'q', 'r', 's', 't', '9', 'u', 'v', 'w', 'x', 'y', 'z', '.'];

	private const FIVE_CHARS = 1 + 2 << 4;
	private const THREE_CHARS = 1 << 5;

	/**
	 * Convert encoded strings into coordinates.
	 *
	 * Rewritten from Javascript version SMap.Coords._parseNumber(arr, count)
	 * Returning array of coordinates representing raw y (latitude) and x (longitude) coordinates
	 *
	 * @return array<array<string,float>>
	 */
	public static function stringToCoords(string $str): array
	{
		$results = [];
		$coords = [0, 0];
		$coordIndex = 0;
		$characters = array_reverse(str_split(trim($str)));
		while (count($characters)) {
			$num = self::_parseNumber($characters, 1);
			if (($num & self::FIVE_CHARS) == self::FIVE_CHARS) {
				$num -= self::FIVE_CHARS;
				$num = (($num & 15) << 24) + self::_parseNumber($characters, 4);
				$coords[$coordIndex] = $num;
			} else if (($num & self::THREE_CHARS) == self::THREE_CHARS) {
				$num = (($num & 15) << 12) + self::_parseNumber($characters, 2);
				$num -= 1 << 15;
				$coords[$coordIndex] += $num;
			} else {
				$num = (($num & 31) << 6) + self::_parseNumber($characters, 1);
				$num -= 1 << 10;
				$coords[$coordIndex] += $num;
			}
			if ($coordIndex) {
				$results[] = [
					'x' => $coords[0] * 360 / (1 << 28) - 180,
					'y' => $coords[1] * 180 / (1 << 28) - 90,
				];
			}
			$coordIndex = ($coordIndex + 1) % 2;
		}
		return $results;
	}

	/**
	 * Convert encoded characters into number
	 *
	 * Rewritten from Javascript version SMap.Coords._parseNumber(arr, count)
	 */
	public static function _parseNumber(&$characters, $count): int
	{
		$result = 0;
		$i = $count;
		while ($i) {
			if (!count($characters)) {
				throw new \InvalidArgumentException('No data!');
			}
			$character = array_pop($characters);
			$characterIndex = array_search($character, self::_ALPHABET);
			if ($characterIndex === false) {
				continue;
			}
			$result <<= 6;
			$result += $characterIndex;
			$i--;
		}
		return $result;
	}
}
