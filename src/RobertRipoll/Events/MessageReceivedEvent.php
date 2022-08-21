<?php

namespace RobertRipoll\Events;

use RobertRipoll\WhatsAppWebhook;
use RobertRipoll\Entities\Sender;

abstract class MessageReceivedEvent extends Event
{
	protected Sender $sender;

	public function __construct(Sender $sender, WhatsAppWebhook $chatBot)
	{
		parent::__construct($chatBot);
		$this->sender = $sender;
	}

	public final function getSender() : Sender
	{
		return $this->sender;
	}
}