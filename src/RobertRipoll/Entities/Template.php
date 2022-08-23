<?php

namespace RobertRipoll\Entities;

use Illuminate\Support\Collection;

class Template extends Message
{
	private string $name;
	private string $language;
	private ?Components $components;

	public function __construct(string $name, string $language, ?Components $components = null, ?string $id = null)
	{
		parent::__construct($id);

		$this->name = $name;
		$this->language = $language;
		$this->components = $components;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getLanguage(): string
	{
		return $this->language;
	}

	public function hasComponents(): bool
	{
		return $this->components !== null;
	}

	public function getComponents(): ?Components
	{
		return $this->components;
	}

	public static function getType() : string
	{
		return 'template';
	}
}