<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi\Types;

use DJTommek\MapyCzApi\MapyCzApiException;

/**
 * @version 2021-03-18
 * @author Tomas Palider (DJTommek) https://tomas.palider.cz/
 */
class ReverseGeocodeType
{
	/** @var ?string */
	public $label;
	/** @var int */
	public $status;
	/** @var string */
	public $message;

	/** @var ReverseGeocodeItemType[] */
	public $items = [];

	public static function cast(\SimpleXMLElement $element): self
	{
		if ($element->count() === 0) {
			throw new MapyCzApiException('No data, are coordinates valid?');
		}

		$attributes = $element->attributes();
		$self = new self();
		if (isset($attributes['label'])) {
			$self->label = (string)$attributes['label'];
		}
		$self->status = (int)($attributes['status']);
		$self->message = (string)$attributes['message'];

		foreach ($element->children() as $child) {
			$item = ReverseGeocodeItemType::cast($child);
			$self->items[] = $item;
		}
		return $self;
	}

	public function getAddress(): ?string
	{
		return $this->label;
	}

	public function getLat(): float
	{
		return $this->items[0]->getLat();
	}

	public function getLon(): float
	{
		return $this->items[0]->getLon();
	}
}
