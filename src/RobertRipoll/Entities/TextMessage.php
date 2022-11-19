<?php

namespace RobertRipoll\Entities;

final class TextMessage extends Message
{
	private string $message;

	public function __construct(string $message, string $id, Sender $sender)
	{
		$this->message = $message;
		parent::__construct($id, $sender);
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public static function getType(): string
	{
		return 'text';
	}
}