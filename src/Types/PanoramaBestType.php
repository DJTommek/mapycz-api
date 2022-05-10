<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi\Types;

/**
 * Result of Panorama API call getbest
 *
 * @method static self cast(\stdClass $stdClass)
 *
 * @version 2022-05-11
 * @author Tomas Palider (DJTommek) https://tomas.palider.cz/
 */
class PanoramaBestType extends Type
{
	/** @var int ID of panorama */
	public $pid;
	/** @var float */
	public $azimuth;
	/** @var float */
	public $distance;
	/** @var float */
	public $lookDir;
	/** @var PanoramaType */
	public $panInfo;
	/** @var float */
	public $radius;

	public static function createFromResponse(\stdClass $response): self
	{
		$self = new self();
		foreach ($response as $key => $value) {
			if ($key === 'panInfo') {
				$self->{$key} = PanoramaType::cast($value);
			} else {
				$self->{$key} = $value;
			}
		}
		return $self;
	}
}
