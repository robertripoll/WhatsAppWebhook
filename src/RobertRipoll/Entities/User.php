<?php

namespace RobertRipoll\Entities;

abstract class User
{
	private string $phoneNumber;

	public function __construct(string $phoneNumber)
	{
		$this->phoneNumber = $phoneNumber;
	}

	public final function getPhoneNumber(): string
	{
		return $this->phoneNumber;
	}
}