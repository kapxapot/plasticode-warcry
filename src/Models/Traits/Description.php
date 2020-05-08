<?php

namespace App\Models\Traits;

/**
 * @property string|null $description
 * @method string|null parsedDescription()
 * @method static withParsedDescription(string|callable|null $parsedDescription)
 */
trait Description
{
    protected string $parsedDescriptionPropertyName = 'parsedDescription';
}
