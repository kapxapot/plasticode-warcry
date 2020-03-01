<?php

namespace App\Config;

use App\Config\Parsing\BBContainerConfig;
use App\Core\Linker;
use App\Core\Renderer;
use App\Handlers\NotFoundHandler;
use App\Parsing\ForumParser;
use App\Parsing\LinkMappers\ArticleLinkMapper;
use App\Parsing\LinkMappers\CoordsLinkMapper;
use App\Parsing\LinkMappers\EventLinkMapper;
use App\Parsing\LinkMappers\HsCardLinkMapper;
use App\Parsing\LinkMappers\StreamLinkMapper;
use App\Parsing\LinkMappers\VideoLinkMapper;
use App\Parsing\NewsParser;
use App\Repositories\ArticleCategoryRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\LocationRepository;
use App\Services\ComicService;
use App\Services\GalleryService;
use App\Services\NewsAggregatorService;
use App\Services\SidebarPartsProviderService;
use App\Services\StreamService;
use App\Services\TagPartsProviderService;
use App\Services\TwitterService;
use Plasticode\Config\Bootstrap as BootstrapBase;
use Plasticode\Gallery\Gallery;
use Plasticode\Gallery\ThumbStrategies\UniformThumbStrategy;
use Plasticode\Parsing\LinkMapperSource;
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
                'articleCategoryRepository' => function (ContainerInterface $container) {
                    return new ArticleCategoryRepository(
                        $container->db
                    );
                },

                'articleRepository' => function (ContainerInterface $container) {
                    return new ArticleRepository(
                        $container->db,
                        $container->auth,
                        $container->articleCategoryRepository
                    );
                },

                'locationRepository' => function (ContainerInterface $container) {
                    return new LocationRepository(
                        $container->db
                    );
                },

                'captchaConfig' => function (ContainerInterface $container) {
                    return new CaptchaConfig();
                },

                'gallery' => function (ContainerInterface $container) {
                    $thumbHeight = $this->settings['gallery']['thumb_height'];
                    $thumbStrategy = new UniformThumbStrategy($thumbHeight);
                    
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
                
                    return new Gallery(
                        $container, $thumbStrategy, $gallerySettings
                    );
                },
                
                'comics' => function (ContainerInterface $container) {
                    $thumbHeight = $this->settings['comics']['thumb_height'];
                    $thumbStrategy = new UniformThumbStrategy($thumbHeight);
                    
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
                    ];
                
                    return new Gallery(
                        $container, $thumbStrategy, $comicsSettings
                    );
                },

                'localizationConfig' => function (ContainerInterface $container) {
                    return new LocalizationConfig();
                },

                'renderer' => function (ContainerInterface $container) {
                    return new Renderer($container->view);
                },

                'linker' => function (ContainerInterface $container) {
                    return new Linker(
                        $container->settingsProvider,
                        $container->router,
                        $container->gallery
                    );
                },

                'bbContainerConfig' => function (ContainerInterface $container) {
                    return new BBContainerConfig();
                },

                'articleLinkMapper' => function (ContainerInterface $container) {
                    return new ArticleLinkMapper(
                        $container->articleRepository,
                        $container->tagRepository,
                        $container->renderer,
                        $container->linker,
                        $container->tagLinkMapper
                    );
                },

                'eventLinkMapper' => function (ContainerInterface $container) {
                    return new EventLinkMapper(
                        $container->renderer,
                        $container->linker
                    );
                },

                'streamLinkMapper' => function (ContainerInterface $container) {
                    return new StreamLinkMapper(
                        $container->renderer,
                        $container->linker
                    );
                },

                'videoLinkMapper' => function (ContainerInterface $container) {
                    return new VideoLinkMapper(
                        $container->renderer,
                        $container->linker
                    );
                },

                'hsCardLinkMapper' => function (ContainerInterface $container) {
                    return new HsCardLinkMapper(
                        $container->renderer,
                        $container->linker
                    );
                },

                'coordsLinkMapper' => function (ContainerInterface $container) {
                    return new CoordsLinkMapper(
                        $container->locationRepository,
                        $container->renderer,
                        $container->linker
                    );
                },

                'doubleBracketsConfig' => function (ContainerInterface $container) {
                    $config = new LinkMapperSource();

                    $config->setDefaultMapper($container->articleLinkMapper);
                    
                    $config->registerTaggedMapper($container->newsLinkMapper);
                    $config->registerTaggedMapper($container->eventLinkMapper);
                    $config->registerTaggedMapper($container->streamLinkMapper);
                    $config->registerTaggedMapper($container->videoLinkMapper);
                    $config->registerTaggedMapper($container->hsCardLinkMapper);
                    $config->registerTaggedMapper($container->coordsLinkMapper);

                    return $config;
                },
                    
                'newsParser' => function (ContainerInterface $container) {
                    return new NewsParser($container);
                },
                
                'forumParser' => function (ContainerInterface $container) {
                    return new ForumParser($container);
                },
                
                // handlers
                
                'notFoundHandler' => function (ContainerInterface $container) {
                    return new NotFoundHandler($container);
                },
                
                // services
                
                'galleryService' => function (ContainerInterface $container) {
                    $pageSize = $this->settings['gallery']['pics_per_page'];
                    
                    return new GalleryService($pageSize);
                },

                'comicService' => function (ContainerInterface $container) {
                    return new ComicService();
                },

                'newsAggregatorService' => function (ContainerInterface $container) {
                    return new NewsAggregatorService();
                },

                'streamService' => function (ContainerInterface $container) {
                    return new StreamService($container->cases);
                },

                'sidebarPartsProviderService' => function (ContainerInterface $container) {
                    return new SidebarPartsProviderService($container);
                },

                'tagPartsProviderService' => function (ContainerInterface $container) {
                    return new TagPartsProviderService($container);
                },

                'twitterService' => function (ContainerInterface $container) {
                    return new TwitterService($container);
                }
            ]
        );
    }
}
