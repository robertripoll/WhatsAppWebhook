<?php

namespace RobertRipoll\Events;

use RobertRipoll\WhatsAppWebhook;
use RobertRipoll\Entities\Sender;
use RobertRipoll\Entities\TextMessage;

class TextMessageReceivedEvent extends MessageReceivedEvent
{
	private TextMessage $textMessage;

	public function __construct(TextMessage $textMessage, Sender $sender, WhatsAppWebhook $chatBot)
	{
		parent::__construct($sender, $chatBot);
		$this->textMessage = $textMessage;
	}

	public function getTextMessage() : TextMessage
	{
		return $this->textMessage;
	}
}