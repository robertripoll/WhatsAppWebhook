<?php

namespace RobertRipoll\Events;

use DateTime;

abstract class Event
{
	private DateTime $dateTime;

	public function __construct(?DateTime $dateTime = null)
	{
		$this->dateTime = $dateTime ?: new DateTime();
	}

	public final function getDateTime(): DateTime
	{
		return $this->dateTime;
	}
}