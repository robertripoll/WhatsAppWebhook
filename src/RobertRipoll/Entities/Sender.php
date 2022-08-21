<?php

namespace RobertRipoll\Entities;

class Sender extends User
{
	protected string $username;

	public function __construct(string $id, string $phoneNumber, string $username)
	{
		parent::__construct($id, $phoneNumber);
		$this->username = $username;
	}

	public function getUsername(): string
	{
		return $this->username;
	}
}