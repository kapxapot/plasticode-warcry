<?php

namespace App\Controllers;

use Plasticode\Controllers\Controller as BaseController;
use Plasticode\Exceptions\ApplicationException;
use Plasticode\Util\Strings;

use App\Models\Game;
use App\Models\Menu;
use App\Services\NewsAggregatorService;
use App\Services\SidebarPartsProviderService;
use App\Services\StreamService;

class Controller extends BaseController
{
	protected $defaultGame;
	protected $sidebarPartsProviderService;

	public function __construct($container)
	{
		parent::__construct($container);
		
		$this->defaultGame = Game::getDefault();
		$this->sidebarPartsProviderService = new SidebarPartsProviderService(
		    $container,
		    new NewsAggregatorService(),
		    new StreamService($container->cases)
		);
	}

	protected function buildParams($settings)
	{
		$params = parent::buildParams($settings);
		
		$params['games'] = Game::getPublished()->all();
		$params['game'] = $this->getGame($settings) ?? $this->defaultGame;
		$params['menu_game'] = $this->getMenuGame($settings);

		return $params;
	}
	
	protected function buildMenu($settings)
	{
		$menuGame = $this->getMenuGame($settings);
		
		return Menu::getByGame($menuGame->id)->all();
	}
	
	protected function getMenuGame($settings)
	{
	    $globalContext = $settings['global_context'] ?? false;

		if (!$globalContext) {
		    $game = $this->getRootGame($settings);
		}
		
		return $game ?? $this->defaultGame;
	}
	
	protected function getRootGame($settings)
	{
	    $game = $this->getGame($settings);
	    
	    if ($game) {
	        $game = $game->root();
	    }
	    
	    return $game;
	}
	
	protected function getGame($settings)
	{
	    return $settings['game'];
	}

	protected function buildPart($settings, $result, $part)
	{
		$game = $this->getRootGame($settings);
		
		$providedPart = $this->sidebarPartsProviderService->getPart($settings, $game, $part);
		
		if ($providedPart === null) {
		    return parent::buildPart($settings, $result, $part);
		}
		
	    $result[$part] = $providedPart;
	    
	    return $result;
	}
	
	public function makePageDescription($text, $limitVar)
	{
	    $limit = $this->getSettings($limitVar);
	    
	    if (!$limit) {
	        throw new ApplicationException('No limit settings found: ' . $limitVar);
	    }
	    
	    return Strings::stripTrunc($text, $limit);
	}
}
