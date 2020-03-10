<?php

namespace App\Controllers;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Game;
use App\Models\Menu;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Services\SidebarPartsProviderService;
use Plasticode\Collection;
use Plasticode\Controllers\Controller as BaseController;
use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouterInterface;

class Controller extends BaseController
{
    private const DefaultPageDescriptionLimit = 1000;

    /** @var SettingsProviderInterface */
    protected $settingsProvider;

    /** @var RouterInterface */
    protected $router;

    /** @var LinkerInterface */
    protected $linker;

    /** @var ParserInterface */
    protected $parser;

    /** @var LoggerInterface */
    protected $logger;

    /** @var GameRepositoryInterface */
    protected $gameRepository;

    /** @var SidebarPartsProviderService */
    protected $sidebarPartsProviderService;

    /** @var Game|null */
    protected $defaultGame;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        
        $this->settingsProvider = $container->settingsProvider;
        $this->router = $container->router;
        $this->linker = $container->linker;
        $this->parser = $container->parser;
        $this->logger = $container->logger;
        $this->gameRepository = $container->gameRepository;
        $this->sidebarPartsProviderService = $container->sidebarPartsProviderService;

        $this->defaultGame = $this->gameRepository->getDefault();
    }

    protected function buildParams(array $settings) : array
    {
        $params = parent::buildParams($settings);
        
        $params['games'] = $this->gameRepository->getAllPublished();
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
        $limit = $this->settingsProvider->getSettings(
            $limitVar,
            self::DefaultPageDescriptionLimit
        );
        
        return Strings::stripTrunc($text, $limit);
    }
}
