<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class StreamTitleAvailableException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Стрим с таким названием уже есть.'
		]
	];
}
