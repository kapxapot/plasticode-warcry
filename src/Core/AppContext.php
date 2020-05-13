<?php

namespace App\Core;

use App\Collections\MenuCollection;
use App\Models\Game;
use App\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Core\AppContext as BaseAppContext;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Log\LoggerInterface;

class AppContext extends BaseAppContext
{
    private MenuRepositoryInterface $menuRepository;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        ViewInterface $view,
        LoggerInterface $logger,
        MenuRepositoryInterface $menuRepository
    )
    {
        parent::__construct(
            $settingsProvider,
            $translator,
            $validator,
            $view,
            $logger,
            $menuRepository
        );

        $this->menuRepository = $menuRepository;
    }

    public function getMenus() : MenuCollection
    {
        return $this->menuRepository->getAll();
    }

    public function getMenusByGame(?Game $game) : MenuCollection
    {
        return $this->menuRepository->getAllByGame($game);
    }
}
