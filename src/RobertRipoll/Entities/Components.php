<?php

namespace RobertRipoll\Entities;

use Illuminate\Support\Collection;

class Components
{
	private Collection $components;

	public function __construct(array $components)
	{
		$this->components = Collection::make($components);
	}

	public function getComponents(): Collection
	{
		return $this->components;
	}

	public function toArray(): array
	{
		return $this->components->map(fn (Component $component) => $component->toArray());
	}
}