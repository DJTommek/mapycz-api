<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi\Types;

/**
 * @version 2020-10-22
 * @author Tomas Palider (DJTommek) https://tomas.palider.cz/
 */
#[\AllowDynamicProperties] abstract class Type
{
	/**
	 * Cast stdClass into specific type
	 *
	 * @author https://stackoverflow.com/a/3243949/3334403
	 * @author https://tommcfarlin.com/cast-a-php-standard-class-to-a-specific-type/
	 * @return Type
	 */
	public static function cast(\stdClass $instance)
	{
		// @phpstan-ignore-next-line
		return unserialize(sprintf('O:%d:"%s"%s', \strlen(static::class), static::class, strstr(strstr(serialize($instance), '"'), ':')));
	}

	/** @var mixed */
	public $mark;

	public function getLat(): float
	{
		return $this->mark->lat;
	}

	public function getLon(): float
	{
		return $this->mark->lon;
	}
}
