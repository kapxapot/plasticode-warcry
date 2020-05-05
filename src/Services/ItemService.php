<?php

namespace App\Services;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Item;
use App\Repositories\Interfaces\ItemRepositoryInterface;
use Psr\Log\LoggerInterface;

class ItemService
{
    private ItemRepositoryInterface $itemRepository;

    private LinkerInterface $linker;
    private LoggerInterface $logger;

    public function __construct(
        ItemRepositoryInterface $itemRepository,
        LinkerInterface $linker,
        LoggerInterface $logger
    )
    {
        $this->itemRepository = $itemRepository;

        $this->linker = $linker;
        $this->logger = $logger;
    }

    /**
     * Loads item from db, if it's absent or not fully loaded,
     * loads it remotely, updates existing one in case of success
     * and returns it.
     * 
     * If loading from remote source fails, return local item.
     */
    public function getSafe(int $id) : ?Item
    {
        $item = $this->itemRepository->get($id);

        if ($item && $item->isFullyLoaded()) {
            return $item;
        }

        try {
            $item = $this->updateFromRemote($item, $id);
        } catch (\Exception $ex) {
            $this->logger->error(
                'Failed to load remote item with id: ' . $id
                . '. Message: ' . $ex->getMessage() . '.'
            );
        }

        if ($item) {
            $item = $this->itemRepository->save($item);
        }

        return $item;
    }

    /**
     * Returns updated item in case of success, otherwise returns null.
     */
    private function updateFromRemote(?Item $item, int $id) : ?Item
    {
        $url = $this->linker->wowheadItemXml($id);
        $urlRu = $this->linker->wowheadItemRuXml($id);

        $xml = @simplexml_load_file($url, null, LIBXML_NOCDATA);
        $xmlRu = @simplexml_load_file($urlRu, null, LIBXML_NOCDATA);

        if ($xml === false) {
            return null;
        }

        $name = (string)$xml->item->name;

        $item ??= $this->itemRepository->create(
            [
                'id' => $id,
                'name' => $name,
            ]
        );

        $item->icon = (string)$xml->item->icon;
        $item->quality = (string)$xml->item->quality['id'];

        if ($xmlRu !== false) {
            $nameRu = (string)$xmlRu->item->name;

            if ($nameRu !== $name) {
                $item->nameRu = $nameRu;
            }
        }

        return $item;
    }
}
