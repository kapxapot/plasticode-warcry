<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\TableFieldAvailable;

use App\Models\Game;

class GameAliasAvailable extends TableFieldAvailable {
	public function __construct($id = null) {
		parent::__construct(Game::getTable(), 'alias', $id);
	}
}
