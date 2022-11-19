<?php

namespace RobertRipoll\Entities;

abstract class Message
{
	private string $id;
	private Sender $sender;

	public function __construct(string $id, Sender $sender)
	{
		$this->id = $id;
		$this->sender = $sender;
	}

	public final function getId(): string
	{
		return $this->id;
	}

	public final function getSender(): Sender
	{
		return $this->sender;
	}

	public abstract static function getType(): string;
}