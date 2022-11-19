<?php

namespace RobertRipoll\Entities;

final class Image extends Media
{
	public static function getType() : string
	{
		return 'image';
	}
}