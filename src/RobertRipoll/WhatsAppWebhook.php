<?php

namespace RobertRipoll;

use DateTime;
use Illuminate\Support\Collection;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\RequestInterface;
use RobertRipoll\Entities\ButtonMessage;
use RobertRipoll\Entities\Message;
use RobertRipoll\Entities\Sender;
use RobertRipoll\Entities\TextMessage;
use RobertRipoll\Events\ButtonMessageReceivedEvent;
use RobertRipoll\Events\MessageDeliveredEvent;
use RobertRipoll\Events\MessageReadEvent;
use RobertRipoll\Events\MessageSentEvent;
use RobertRipoll\Events\TextMessageReceivedEvent;
use RobertRipoll\Exceptions\InvalidJsonException;

class WhatsAppWebhook
{
	private const OBJECT_TYPE = 'whatsapp_business_account';
	private const MESSAGING_PRODUCT = 'whatsapp';

	private string $verifyToken;
	private WhatsAppCloudApi $whatsApp;
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(string $verifyToken, array $whatsAppConfig, EventDispatcherInterface $eventDispatcher)
	{
		$this->verifyToken = $verifyToken;
		$this->whatsApp = new WhatsAppCloudApi($whatsAppConfig);
		$this->eventDispatcher = $eventDispatcher;
	}

	public function getWhatsApp(): WhatsAppCloudApi
	{
		return $this->whatsApp;
	}

	public function verify(RequestInterface $message): ?string
	{
		$queryParams = [];
		parse_str(parse_url($message->getUri()->getQuery(), PHP_URL_QUERY), $queryParams);

		$queryParams = new Collection($queryParams);

		$mode = $queryParams->get('hub.mode');
		$token = $queryParams->get('hub.verify_token');
		$challenge = $queryParams->get('hub.challenge');

		if (!$mode || !$token) {
			return null;
		}

		if ($mode != 'subscribe' || $token != $this->verifyToken) {
			return null;
		}

		return $challenge;
	}

	private function getBody(RequestInterface $message): array
	{
		$body = json_decode((string)$message->getBody(), JSON_THROW_ON_ERROR);

		if ($body === null) {
			throw new InvalidJsonException();
		}

		return $body;
	}

	private function getPayload(array $body): ?Collection
	{
		$data = new Collection($body);

		if ($data->get('object') != self::OBJECT_TYPE) {
			return null;
		}

		$entry = new Collection($data->get('entry'));
		$change = (new Collection($entry->get('changes')))->first;
		$payload = new Collection($change->get('value'));

		if ($payload->get('messaging_product') != self::MESSAGING_PRODUCT) {
			return null;
		}

		$metadata = new Collection($payload->get('metadata'));

		if ($metadata->get('phone_number_id') != $this->whatsApp->fromPhoneNumberId()) {
			return null;
		}

		return $payload;
	}

	private function isMessageReceivedPayload(Collection $payload): bool
	{
		return $payload->has('messages') && $payload->get('messages');
	}

	private function getReceivedMessageSender(Collection $data): Sender
	{
		$contact = (new Collection($data->get('contacts')))->first;
		$profile = new Collection($contact->get('profile'));
		$username = $profile->get('name');

		$phoneNumber = $data->get('from');

		return new Sender('', $phoneNumber, $username);
	}

	private function getReceivedMessage(Collection $payload): ?Message
	{
		$data = new Collection($payload->get('messages'));
		$data = new Collection($data->first);

		$sender = $this->getReceivedMessageSender($data);

		$message = null;
		$messageType = $payload->get('type');

		if ($messageType == TextMessage::getType())
		{
			$body = (new Collection($data->get($messageType)))->get('body');
			$message = new TextMessage($body, '', $sender);
		}

		elseif ($messageType == ButtonMessage::getType())
		{
			$button = new Collection($data->get($messageType));
			$message = new ButtonMessage($button->get('text'), $button->get('payload'), '', $sender);
		}

		return $message;
	}

	private function emitReceivedMessageEvent(Message $message): void
	{
		if ($message instanceof TextMessage) {
			$this->eventDispatcher->dispatch(new TextMessageReceivedEvent($message, $message->getSender()));
		}

		elseif ($message instanceof ButtonMessage) {
			$this->eventDispatcher->dispatch(new ButtonMessageReceivedEvent($message, $message->getSender()));
		}
	}

	private function isMessageStatusChangedPayload(Collection $payload): bool
	{
		return $payload->has('statuses') && $payload->get('statuses');
	}

	private function emitMessageStatusChangedEvent(Collection $payload): void
	{
		$data = (new Collection($payload->get('statuses')))->first;
		$status = (new Collection($data))->get('status');

		$messageId = $data->get('id');
		$timestamp = DateTime::createFromFormat('U', $data->get('timestamp')) ?: null;

		switch ($status)
		{
			case 'sent':
				$this->eventDispatcher->dispatch(new MessageSentEvent($messageId, $timestamp));
				break;

			case 'delivered':
				$this->eventDispatcher->dispatch(new MessageDeliveredEvent($messageId, $timestamp));
				break;

			case 'read':
				$this->eventDispatcher->dispatch(new MessageReadEvent($messageId, $timestamp));
				break;
		}
	}

	private function processPayload(Collection $payload): void
	{
		if ($this->isMessageReceivedPayload($payload))
		{
			$message = $this->getReceivedMessage($payload);
			$this->emitReceivedMessageEvent($message);
		}

		elseif ($this->isMessageStatusChangedPayload($payload)) {
			$this->emitMessageStatusChangedEvent($payload);
		}
	}

	/**
	 * @throws InvalidJsonException
	 */
	public function process(RequestInterface $message)
	{
		if ($message->getMethod() !== 'POST') {
			return;
		}

		if (!$payload = $this->getPayload($this->getBody($message))) {
			return;
		}

		$this->processPayload($payload);
	}
}