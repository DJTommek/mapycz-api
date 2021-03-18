<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi\Types;

/**
 * @version 2021-03-18
 * @author Tomas Palider (DJTommek) https://tomas.palider.cz/
 */
class ReverseGeocodeItemType
{
	/** @var int */
	public $id;
	/** @var string */
	public $name;
	/** @var string */
	public $source;
	/** @var string */
	public $type;
	/** @var float */
	public $x;
	/** @var float */
	public $y;

	public static function cast(\SimpleXMLElement $element): self
	{
		$attributes = $element->attributes();
		$self = new self();
		$self->id = (int)$attributes['id'];
		$self->name = (string)$attributes['name'];
		$self->source = (string)$attributes['source'];
		$self->type = (string)$attributes['type'];
		$self->x = (float)$attributes['x'];
		$self->y = (float)$attributes['y'];
		return $self;
	}

	public function getLat(): float
	{
		return $this->y;
	}

	public function getLon(): float
	{
		return $this->x;
	}
}
