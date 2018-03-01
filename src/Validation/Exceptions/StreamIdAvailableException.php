<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class StreamIdAvailableException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Стрим с таким кодом уже есть или недопустимый код стрима.'
		]
	];
}
