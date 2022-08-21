<?php

namespace RobertRipoll\Entities;

class TextMessage extends Message
{
	private string $text;

	public function __construct(string $text, ?string $id = null)
	{
		parent::__construct($id);
		$this->text = $text;
	}

	public function getText() : string
	{
		return $this->text;
	}

	public static function getType() : string
	{
		return 'text';
	}
}