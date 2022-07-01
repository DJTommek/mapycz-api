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

	/**
	 * Generate mapy.cz link opening this particular Panorama view
	 *
	 * Example outputs:
	 * - Raw output: https://mapy.cz/zakladni?x=14.421499685654&y=50.087788829892&pano=1&pid=70102895&yaw=4.4632872665053
	 * - Enhanced example, (*)see below: https://mapy.cz/zakladni?x=14.421499685654&y=50.087788829892&pano=1&pid=70102895&yaw=4.4632872665053&source=coor&id=14.4211267%2C50.0877258
	 * - Enhanced example, (*)see below: https://mapy.cz/zakladni?x=14.421499685654&y=50.087788829892&pano=1&pid=70102895&yaw=4.4632872665053&source=base&id=1832651
	 *
	 * Other optional query parameters, that could be added to URL:
	 * - pitch (float) Vertical alignment. Between -1 (looing up) and 1 (looking down). 0 is looking straight
	 * - fov (float) Field of view
	 * - source (string) (*)see below
	 * - id (string|int) (*)see below
	 *
	 * (*)  If these parameters are provided, 2D map is zoomed in to view both panorama view location and
	 *      selected source location (coordinates or specific POI), instead of being zoomed out on whole country
	 *      At least this is valid for 2022-07-01
	 */
	public function getLink(): string
	{
		$url = 'https://mapy.cz/zakladni';
		$queryParameters = [
			'x' => $this->getLon(),
			'y' => $this->getLat(),
			'pano' => 1,
			'pid' => $this->pid,
			// @TODO Should be used 'azimuth' or 'lookDir' for generating 'yaw'? Both seems to be the same or very similar float numbers
			'yaw' => deg2rad($this->azimuth), // Horizontal alignment (north, west, ...)
		];
		return $url . '?' . http_build_query($queryParameters);
	}
}
