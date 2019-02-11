<?php

namespace App\Config;

use Plasticode\Config\Bootstrap as BootstrapBase;

class Bootstrap extends BootstrapBase
{
    public function getMappings()
    {
        $mappings = parent::getMappings();
        
        return array_merge(
            $mappings,
            [
                'userClass' => function ($container) {
                    return \App\Models\User::class;
                },

                'menuItemClass' => function ($container) {
                    return \App\Models\MenuItem::class;
                },
                
                'captchaConfig' => function ($container) {
                    return new \App\Config\Captcha;  
                },

                'gallery' => function ($container) {
                	$gallerySettings = [
                		'base_dir' => $this->dir,
                		'fields' => [
                			'picture_type' => 'picture_type',
                			'thumb_type' => 'thumb_type',
                		],
                		'folders' => [
                			'picture' => [
                				'storage' => 'gallery_pictures',
                				'public' => 'gallery_pictures_public',
                			],
                			'thumb' => [
                				'storage' => 'gallery_thumbs',
                				'public' => 'gallery_thumbs_public',
                			],
                		],
                	];
                
                	return new \Plasticode\Gallery\Gallery($container, $gallerySettings);
                },
                
                'comics' => function ($container) {
                	$comicsSettings = [
                		'base_dir' => $this->dir,
                		'fields' => [
                			'picture_type' => 'type',
                			'thumb_type' => 'type',
                		],
                		'folders' => [
                			'picture' => [
                				'storage' => 'comics_pages',
                				'public' => 'comics_pages_public',
                			],
                			'thumb' => [
                				'storage' => 'comics_thumbs',
                				'public' => 'comics_thumbs_public',
                			],
                		],
                		'thumb_height' => $this->settings['comics']['thumb_height'],
                	];
                
                	return new \Plasticode\Gallery\Comics($container, $comicsSettings);
                },

                'localization' => function ($container) {
                    return new \App\Config\Localization;
                },

                'renderer' => function ($container) {
                	return new \App\Core\Renderer($container->view);
                },

                'linker' => function ($container) {
                	return new \App\Core\Linker($container);
                },
                
                'parser' => function ($container) {
                	return new \App\Core\Parser($container, $container->parserConfig);
                },
                
                'newsParser' => function ($container) {
                	return new \App\Parsing\NewsParser($container);
                },
                
                'forumParser' => function ($container) {
                	return new \App\Parsing\ForumParser($container);
                },
                
                // handlers
                
                'notFoundHandler' => function ($container) {
                	return new \App\Handlers\NotFoundHandler($container);
                },

/*$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection([
	'driver' => 'mysql',
	'host' => $dbs['host'],
	'database' => $dbs['database'],
	'username' => $dbs['user'],
	'password' => $dbs['password'],
	'charset' => 'utf8',
	'collation' => 'utf8_general_ci',
	'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['eloquent'] = function($c) use ($capsule)
{
	return $capsule;
};*/

            ]
        );
    }
}
