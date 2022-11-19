<?php

namespace RobertRipoll\Entities;

abstract class Media extends Message
{
	public abstract static function getType() : string;
}