<?php

namespace RobertRipoll\Entities;

abstract class Message
{
	private ?string $id;

	public function __construct(?string $id = null)
	{
		$this->id = $id;
	}

	public final function getId(): ?string
	{
		return $this->id;
	}

	public final function setId(?string $id): void
	{
		$this->id = $id;
	}

	public abstract static function getType(): string;
}