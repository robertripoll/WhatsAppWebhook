<?php

namespace RobertRipoll\Events;

use DateTime;
use RobertRipoll\Entities\ButtonMessage;
use RobertRipoll\Entities\Sender;
use RobertRipoll\Entities\TextMessage;

final class ButtonMessageReceivedEvent extends MessageReceivedEvent
{
	private ButtonMessage $buttonMessage;

	public function __construct(ButtonMessage $buttonMessage, Sender $sender, ?DateTime $dateTime = null)
	{
		$this->buttonMessage = $buttonMessage;
		parent::__construct($sender, $dateTime);
	}

	public function getButtonMessage(): ButtonMessage
	{
		return $this->buttonMessage;
	}
}