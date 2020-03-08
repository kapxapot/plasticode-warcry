<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel; 

/**
 * @property integer $id
 * @property string $name
 * @property string|null $nameRu
 * @property string $icon
 * @property string $quality
 */
class Item extends DbModel
{
    public static function getSafe(int $id) : self
    {
        $item = self::get($id);
        
        if ($item && strlen($item->nameRu) > 0) {
            return $item;
        }

        return self::getRemote($id);
    }

    private static function getRemote(int $id) : self
    {
        $linker = self::$container->linker;

        $url = $linker->wowheadItemXml($id);
        $urlRu = $linker->wowheadItemRuXml($id);
        
        $xml = @simplexml_load_file($url, null, LIBXML_NOCDATA);
        $xmlRu = @simplexml_load_file($urlRu, null, LIBXML_NOCDATA);
        
        if ($xml !== false) {
            $name = (string)$xml->item->name;
            
            $item = self::get($id);
            
            if (!$item) {
                $item = new self(
                    [
                        'id' => $id,
                        'name' => $name,
                    ]
                );
            }
            
            $item->icon = (string)$xml->item->icon;
            $item->quality = (string)$xml->item->quality['id'];

            if ($xmlRu !== false) {
                $nameRu = (string)$xmlRu->item->name;
                
                if ($nameRu !== $name) {
                    $item->nameRu = $nameRu;
                }
            }

            // Todo: remove this dirty hack
            $item = self::save($item);
        }
        
        return $item;
    }
    
    // props
    
    public function displayName() : string
    {
        return $this->nameRu ?? $this->name;
    }
    
    public function url() : string
    {
        return self::$container->linker->wowheadItemRu($this->getId());
    }
    
    public function recipes() : Collection
    {
        return self::$container->recipeRepository->getAllByItemId($this->getId());
    }
}
