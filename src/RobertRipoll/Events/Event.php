<?php

namespace RobertRipoll\Events;

use DateTime;
use RobertRipoll\WhatsAppWebhook;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

abstract class Event extends SymfonyEvent
{
	private WhatsAppWebhook $chatBot;
	private DateTime $timestamp;

	public function __construct(WhatsAppWebhook $chatBot, ?DateTime $timestamp = null)
	{
		$this->chatBot = $chatBot;
		$this->timestamp = $timestamp ?: new DateTime();
	}

	public final function getChatBot(): WhatsAppWebhook
	{
		return $this->chatBot;
	}

	public final function getTimestamp(): DateTime
	{
		return $this->timestamp;
	}
}