<?php

namespace RobertRipoll;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Response;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use RobertRipoll\Entities\Message;
use RobertRipoll\Entities\Sender;
use RobertRipoll\Entities\Template;
use RobertRipoll\Entities\TextMessage;
use RobertRipoll\Entities\User;
use RobertRipoll\Events\Event;
use RobertRipoll\Events\MessageDeliveredEvent;
use RobertRipoll\Events\MessageReadEvent;
use RobertRipoll\Events\TextMessageReceivedEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WhatsAppWebhook
{
	private const WHATSAPP_OBJECT = 'whatsapp_business_account';
	private const WHATSAPP_PRODUCT = 'whatsapp';

	private Collection $payload;
	private WhatsAppCloudApi $whatsApp;
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(string $phoneNumberId, string $accessToken, ?EventDispatcherInterface $eventDispatcher = null)
	{
		$this->whatsApp = new WhatsAppCloudApi(['from_phone_number_id' => $phoneNumberId, 'access_token' => $accessToken]);
		$this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();
	}

	private function load(): bool
	{
		$payload = Collection::make($_POST);

		if ($payload->get('object') != self::WHATSAPP_OBJECT) {
			return false;
		}

		$payload = Collection::make($payload->get('entry'))->first;

		if (!$payload || $payload->get('id') != $this->whatsApp->fromPhoneNumberId()) {
			return false;
		}

		$change = Collection::make($payload->get('changes'))->first;

		if (!$change) {
			return false;
		}

		$payload = Collection::make($change);

		if (!$payload || $payload->get('messaging_product') != self::WHATSAPP_PRODUCT) {
			return false;
		}

		$this->payload = $payload;

		return true;
	}

	public function addListener(string $eventName, callable $callback): void
	{
		$this->eventDispatcher->addListener($eventName, $callback);
	}

	private function isIncomingMessagePayload(): bool
	{
		$payload = $this->payload;

		return $payload->has('messages') && $payload->get('messages');
	}

	private function getSender(): ?Sender
	{
		$metadata = Collection::make($this->payload->get('metadata'));
		$contact = Collection::make($this->payload->get('contacts'))->first;

		if (!$metadata || !$contact) {
			return null;
		}

		$profile = Collection::make($contact->get('profile'));

		if (!$profile) {
			return null;
		}

		return new Sender($contact->get('wa_id'), $metadata->get('display_phone_number'), $profile->get('display_phone_number'));
	}

	private function getMessage(): Message
	{
		$message = null;

		$payload = Collection::make(Collection::make($this->payload->get('messages'))->first);
		$messageType = $payload->get('type');

		if ($messageType == TextMessage::getType()) {
			$message = new TextMessage('Hello world');
		}

		return $message;
	}

	private function dispatch(Event $event): void
	{
		$this->eventDispatcher->dispatch($event, get_class($event));
	}

	private function processIncomingMessage(): bool
	{
		$sender = $this->getSender();
		$message = $this->getMessage();

		if ($message instanceof TextMessage) {
			$this->dispatch(new TextMessageReceivedEvent($message, $sender, $this));
		}

		return true;
	}

	private function isMessageStatusChangePayload(): bool
	{
		$payload = $this->payload;

		return $payload->has('statuses') && $payload->get('statuses');
	}

	private function processMessageStatusChange(): bool
	{
		$payload = $this->payload;

		$data = Collection::make($payload->get('statuses'))->first;
		$status = $data->get('status');

		if ($status == 'delivered') {
			$this->dispatch(new MessageDeliveredEvent($this));
		}

		elseif ($status == 'read') {
			$this->dispatch(new MessageReadEvent($this));
		}

		else {
			return false;
		}

		return true;
	}

	private function execute(): bool
	{
		if ($this->isIncomingMessagePayload()) {
			return $this->processIncomingMessage();
		}

		if ($this->isMessageStatusChangePayload()) {
			return $this->processMessageStatusChange();
		}

		return false;
	}

	public function process(): void
	{
		if (!$this->load()) {
			return;
		}

		$this->execute();
	}

	private function sendTextMessage(User $recipient, TextMessage $message): Response
	{
		return $this->whatsApp->sendTextMessage($recipient->getPhoneNumber(), $message->getText());
	}

	private function sendTemplate(User $recipient, Template $template): Response
	{
		if ($template->hasComponents()) {
			$netflieComponent = new Component([], $template->getComponents()->toArray(), []);
		}

		else {
			$netflieComponent = null;
		}

		return $this->whatsApp->sendTemplate($recipient->getPhoneNumber(), $template->getName(), $template->getLanguage(), $netflieComponent);
	}

	/**
	 * @throws ResponseException
	 */
	public function send(User $recipient, Message $message): void
	{
		if ($message instanceof TextMessage) {
			$response = $this->sendTextMessage($recipient, $message);
		}

		elseif ($message instanceof Template) {
			$response = $this->sendTemplate($recipient, $message);
		}

		else {
			throw new InvalidArgumentException('Unsupported message type');
		}

		if ($response->isError() || $response->httpStatusCode() != 200) {
			$response->throwException();
		}
	}
}