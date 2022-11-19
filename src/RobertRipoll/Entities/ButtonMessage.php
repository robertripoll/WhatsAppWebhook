<?php

namespace RobertRipoll\Entities;

final class ButtonMessage extends Message
{
	private string $payload;
	private string $text;

	public function __construct(string $text, string $payload, string $id, Sender $sender)
	{
		$this->text = $text;
		$this->payload = $payload;

		parent::__construct($id, $sender);
	}

	public function getPayload() : string
	{
		return $this->payload;
	}

	public function getText() : string
	{
		return $this->text;
	}

	public static function getType() : string
	{
		return 'button';
	}
}