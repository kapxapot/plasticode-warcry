<?php

namespace App\Config;

use Plasticode\Config\Bootstrap as BootstrapBase;
use Psr\Container\ContainerInterface;

class Bootstrap extends BootstrapBase
{
    /**
     * Get mappings for DI container.
     *
     * @return array
     */
    public function getMappings() : array
    {
        $mappings = parent::getMappings();
        
        return array_merge(
            $mappings,
            [
                'userClass' => function (ContainerInterface $container) {
                    return \App\Models\User::class;
                },

                'menuClass' => function (ContainerInterface $container) {
                    return \App\Models\Menu::class;
                },

                'menuItemClass' => function (ContainerInterface $container) {
                    return \App\Models\MenuItem::class;
                },
                
                'captchaConfig' => function (ContainerInterface $container) {
                    return new \App\Config\Captcha();
                },

                'gallery' => function (ContainerInterface $container) {
                    $thumbHeight = $this->settings['gallery']['thumb_height'];
                    $thumbStrategy = new \Plasticode\Gallery\ThumbStrategies\UniformThumbStrategy($thumbHeight);
                    
                    $gallerySettings = [
                        'base_dir' => $this->dir,
                        'fields' => [
                            'picture_type' => 'picture_type',
                            'thumb_type' => 'picture_type',
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
                
                    return new \Plasticode\Gallery\Gallery($container, $thumbStrategy, $gallerySettings);
                },
                
                'comics' => function (ContainerInterface $container) {
                    $thumbHeight = $this->settings['comics']['thumb_height'];
                    $thumbStrategy = new \Plasticode\Gallery\ThumbStrategies\UniformThumbStrategy($thumbHeight);
                    
                    $comicsSettings = [
                        'base_dir' => $this->dir,
                        'fields' => [
                            'picture_type' => 'pic_type',
                            'thumb_type' => 'pic_type',
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
                        //'thumb_height' => $this->settings['comics']['thumb_height'],
                    ];
                
                    return new \Plasticode\Gallery\Gallery($container, $thumbStrategy, $comicsSettings);
                },

                'localization' => function (ContainerInterface $container) {
                    return new \App\Config\Localization();
                },

                'renderer' => function (ContainerInterface $container) {
                    return new \App\Core\Renderer($container->view);
                },

                'linker' => function (ContainerInterface $container) {
                    return new \App\Core\Linker($container);
                },
                
                'parser' => function (ContainerInterface $container) {
                    return new \App\Core\Parser(
                        $container,
                        $container->parserConfig
                    );
                },
                
                'newsParser' => function (ContainerInterface $container) {
                    return new \App\Parsing\NewsParser($container);
                },
                
                'forumParser' => function (ContainerInterface $container) {
                    return new \App\Parsing\ForumParser($container);
                },
                
                // handlers
                
                'notFoundHandler' => function (ContainerInterface $container) {
                    return new \App\Handlers\NotFoundHandler($container);
                },
                
                // services
                
                'galleryService' => function (ContainerInterface $container) {
                    $pageSize = $this->settings['gallery']['pics_per_page'];
                    
                    return new \App\Services\GalleryService($pageSize);
                },

                'comicService' => function (ContainerInterface $container) {
                    return new \App\Services\ComicService();
                }
            ]
        );
    }
}
