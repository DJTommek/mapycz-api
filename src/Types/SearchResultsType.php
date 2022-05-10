<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi\Types;

use DJTommek\MapyCzApi\MapyCzApi;

/**
 * @method static self cast(\stdClass $stdClass)
 *
 * @version 2021-08-19
 * @author Tomas Palider (DJTommek) https://tomas.palider.cz/
 */
class SearchResultsType extends Type
{
	/** @var MapyCzApi */
	public $client;

	public static function createFromResponse(MapyCzApi $client, \stdClass $response): self
	{
		$result = self::cast($response);
		$result->client = $client;
		return $result;
	}

	public function hasAction(): bool
	{
		return isset($this->action);
	}

	public function runAction()
	{
		$args = $this->action->args;
		return $this->client->loadPoiDetails($args->source, $args->id);
	}
}
