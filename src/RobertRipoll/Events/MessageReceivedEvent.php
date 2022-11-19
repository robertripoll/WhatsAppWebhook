<?php

namespace RobertRipoll\Events;

use DateTime;
use RobertRipoll\Entities\Sender;

abstract class MessageReceivedEvent extends Event
{
	private Sender $sender;

	public function __construct(Sender $sender, ?DateTime $dateTime = null)
	{
		$this->sender = $sender;
		parent::__construct($dateTime);
	}

	public final function getSender(): Sender
	{
		return $this->sender;
	}
}