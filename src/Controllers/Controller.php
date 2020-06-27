<?php

namespace App\Controllers;

use App\Collections\MenuCollection;
use App\Core\AppContext;
use App\Core\Interfaces\LinkerInterface;
use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Services\SidebarPartsProviderService;
use Plasticode\Controllers\Controller as BaseController;
use Plasticode\Controllers\Traits\PageDescription;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;

class Controller extends BaseController
{
    use PageDescription;

    private AppContext $appContext;

    protected RouterInterface $router;
    protected LinkerInterface $linker;
    protected ParserInterface $parser;

    protected GameRepositoryInterface $gameRepository;
    protected SidebarPartsProviderService $sidebarPartsProviderService;

    protected ?Game $defaultGame = null;

    public function __construct(ContainerInterface $container)
    {
        $appContext = $container->appContext;

        parent::__construct($container->appContext);

        $this->appContext = $appContext;

        $this->router = $container->router;
        $this->linker = $container->linker;
        $this->parser = $container->parser;

        $this->gameRepository = $container->gameRepository;

        $this->sidebarPartsProviderService =
            $container->sidebarPartsProviderService;

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

    protected function buildMenu(array $settings) : MenuCollection
    {
        $menuGame = $this->getMenuGame($settings);

        return $this->appContext->getMenusByGame($menuGame);
    }

    protected function getMenuGame(array $settings) : ?Game
    {
        $globalContext = $settings['global_context'] ?? false;

        $game = $globalContext
            ? null
            : $this->getRootGame($settings);

        return $game ?? $this->defaultGame;
    }

    protected function getRootGame(array $settings) : ?Game
    {
        $game = $this->getGame($settings);

        return $game ? $game->root() : null;
    }

    protected function getGame(array $settings) : ?Game
    {
        return $settings['game'] ?? null;
    }

    protected function buildPart(
        array $settings,
        array $result,
        string $part
    ) : ?array
    {
        $game = $this->getRootGame($settings);

        $providedPart = null;

        try {
            $providedPart = $this
                ->sidebarPartsProviderService
                ->getPart($settings, $game, $part);
        } catch (InvalidConfigurationException $ex) {
            return parent::buildPart($settings, $result, $part);
        }

        $result[$part] = $providedPart;

        return $result;
    }
}
