<?php

namespace App\Generators;

use Plasticode\Generators\MenuItemsGenerator as MenuItemsBaseGenerator;

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
        
        $menu = $this->menuRepository->get($menuId);

        $params['breadcrumbs'] = [
            [ 'text' => 'Меню', 'link' => $this->router->pathFor('admin.entities.menus') ],
            [ 'text' => $menu->game()->name ],
            [ 'text' => $menu->text ],
            [ 'text' => 'Элементы меню' ],
        ];
        
        $params['hidden'] = [
            'section_id' => $menuId,
        ];
        
        return $params;
    }
}
