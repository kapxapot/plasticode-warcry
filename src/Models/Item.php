<?php

namespace App\Models;

use Plasticode\Models\DbModel; 

class Item extends DbModel
{
    // getters - one
    
    public static function getSafe($id)
    {
		$item = self::get($id);
		
		if ($item && strlen($item->nameRu) > 0) {
		    return $item;
		}

		return self::getRemote($id);
    }

	private static function getRemote($id)
	{
		$url = self::$linker->wowheadItemXml($id);
		$urlRu = self::$linker->wowheadItemRuXml($id);
		
		$xml = @simplexml_load_file($url, null, LIBXML_NOCDATA);
		$xmlRu = @simplexml_load_file($urlRu, null, LIBXML_NOCDATA);
		
		if ($xml !== false) {
			$name = (string)$xml->item->name;
			
			$item = self::get($id);
			
			if (!$item) {
    			$item = new Item([
    			    'id' => $id,
    				'name' => $name,
    			]);
			}
			
			$item->icon = (string)$xml->item->icon;
			$item->quality = (string)$xml->item->quality['id'];

			if ($xmlRu !== false) {
				$nameRu = (string)$xmlRu->item->name;
				
				if ($nameRu !== $name) {
					$item->nameRu = $nameRu;
				}
			}

			$item->save();
		}
		
		return $item;
	}
    
	// props
	
	public function displayName()
	{
	    return $this->nameRu ?? $item->name;
	}
	
	public function url()
	{
		return self::$linker->wowheadItemRu($this->getId());
	}
	
	public function recipes()
	{
	    return Recipe::getAllByItemId($this->getId());
	}
}
