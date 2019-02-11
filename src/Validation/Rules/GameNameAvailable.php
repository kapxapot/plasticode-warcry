<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\TableFieldAvailable;

use App\Models\Game;

class GameNameAvailable extends TableFieldAvailable {
	public function __construct($id = null) {
		parent::__construct(Game::getTable(), 'name', $id);
	}
}
