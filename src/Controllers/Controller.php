<?php

namespace App\Controllers;

use App\Models\Game;
use App\Models\Menu;
use Plasticode\Collection;
use Plasticode\Controllers\Controller as BaseController;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;

class Controller extends BaseController
{
    private const DefaultPageDescriptionLimit = 1000;

    protected $defaultGame;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        
        $this->defaultGame = Game::getDefault();
    }

    protected function buildParams(array $settings) : array
    {
        $params = parent::buildParams($settings);
        
        $params['games'] = Game::getPublished()->all();
        $params['game'] = $this->getGame($settings) ?? $this->defaultGame;
        $params['menu_game'] = $this->getMenuGame($settings);

        return $params;
    }
    
    protected function buildMenu(array $settings) : Collection
    {
        $menuGame = $this->getMenuGame($settings);
        
        return Menu::getByGame($menuGame->id)->all();
    }
    
    protected function getMenuGame(array $settings) : ?Game
    {
        $globalContext = $settings['global_context'] ?? false;

        if (!$globalContext) {
            $game = $this->getRootGame($settings);
        }
        
        return $game ?? $this->defaultGame;
    }
    
    protected function getRootGame(array $settings) : ?Game
    {
        $game = $this->getGame($settings);
        
        if ($game) {
            $game = $game->root();
        }
        
        return $game;
    }
    
    protected function getGame(array $settings) : ?Game
    {
        return $settings['game'];
    }

    protected function buildPart(array $settings, array $result, string $part) : ?array
    {
        $game = $this->getRootGame($settings);
        
        $providedPart = $this->sidebarPartsProviderService
            ->getPart($settings, $game, $part);
        
        if ($providedPart === null) {
            return parent::buildPart($settings, $result, $part);
        }
        
        $result[$part] = $providedPart;
        
        return $result;
    }
    
    public function makePageDescription(string $text, string $limitVar) : string
    {
        $limit = $this->getSettings(
            $limitVar,
            self::DefaultPageDescriptionLimit
        );
        
        return Strings::stripTrunc($text, $limit);
    }
}
