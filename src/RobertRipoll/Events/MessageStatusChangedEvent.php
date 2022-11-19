<?php

namespace RobertRipoll\Events;

use DateTime;

abstract class MessageStatusChangedEvent extends Event
{
	private string $messageId;

	public function __construct(string $messageId, ?DateTime $dateTime = null)
	{
		$this->messageId = $messageId;
		parent::__construct($dateTime);
	}

	public final function getMessageId(): string
	{
		return $this->messageId;
	}
}