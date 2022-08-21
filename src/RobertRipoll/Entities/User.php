<?php

namespace RobertRipoll\Entities;

abstract class User
{
	protected string $id;
	protected string $phoneNumber;

	public function __construct(string $id, string $phoneNumber)
	{
		$this->id = $id;
		$this->phoneNumber = $phoneNumber;
	}

	public final function getId(): string
	{
		return $this->id;
	}

	public final function getPhoneNumber(): string
	{
		return $this->phoneNumber;
	}
}