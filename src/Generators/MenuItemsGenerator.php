<?php

namespace App\Generators;

use Plasticode\Generators\MenuItemsGenerator as MenuItemsBaseGenerator;

use App\Models\Game;

class MenuItemsGenerator extends MenuItemsBaseGenerator
{
	public function getOptions()
	{
		$options = parent::getOptions();
		
		$options['filter'] = 'section_id';
		
		return $options;
	}
	
	public function getAdminParams($args)
	{
		$params = parent::getAdminParams($args);

		$menuId = $args['id'];
		$menu = $this->db->getMenu($menuId);
		$game = Game::get($menu['game_id']);
		
		$params['breadcrumbs'] = [
			[ 'text' => 'Меню', 'link' => $this->router->pathFor('admin.entities.menus') ],
			[ 'text' => $game ? $game['name'] : '(нет игры)' ],
			[ 'text' => $menu['text'] ],
			[ 'text' => 'Элементы меню' ],
		];
		
		$params['hidden'] = [
			'section_id' => $menuId,
		];
		
		return $params;
	}
}
