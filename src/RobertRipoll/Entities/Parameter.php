<?php

namespace RobertRipoll\Entities;

class Parameter
{
	private string $text;

	public function __construct(string $text)
	{
		$this->text = $text;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function toArray(): array
	{
		return [
			'type' => 'text',
			'text' => $this->text,
		];
	}
}