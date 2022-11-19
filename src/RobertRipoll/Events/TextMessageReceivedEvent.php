<?php

namespace RobertRipoll\Events;

use DateTime;
use RobertRipoll\Entities\Sender;
use RobertRipoll\Entities\TextMessage;

final class TextMessageReceivedEvent extends MessageReceivedEvent
{
	private TextMessage $textMessage;

	public function __construct(TextMessage $textMessage, Sender $sender, ?DateTime $dateTime = null)
	{
		$this->textMessage = $textMessage;
		parent::__construct($sender, $dateTime);
	}

	public function getTextMessage(): TextMessage
	{
		return $this->textMessage;
	}
}