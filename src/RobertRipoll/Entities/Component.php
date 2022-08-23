<?php

namespace RobertRipoll\Entities;

use Illuminate\Support\Collection;

class Component
{
	private Collection $parameters;

	public function __construct(array $parameters)
	{
		$this->parameters = Collection::make($parameters);
	}

	public function getParameters(): Collection
	{
		return $this->parameters;
	}

	public function toArray(): array
	{
		return [
			'type' => 'body',
			'parameters' => $this->parameters->map(fn (Parameter $parameter) => $parameter->toArray()),
		];
	}
}