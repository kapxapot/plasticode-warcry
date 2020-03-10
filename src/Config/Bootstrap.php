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
use App\Parsing\LinkMappers\GalleryLinkMapper;
use App\Parsing\LinkMappers\GenericLinkMapper;
use App\Parsing\LinkMappers\HsCardLinkMapper;
use App\Parsing\LinkMappers\ItemLinkMapper;
use App\Parsing\LinkMappers\SpellLinkMapper;
use App\Parsing\LinkMappers\StreamLinkMapper;
use App\Parsing\LinkMappers\VideoLinkMapper;
use App\Parsing\NewsParser;
use App\Repositories\ArticleCategoryRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\EventRepository;
use App\Repositories\GalleryPictureRepository;
use App\Repositories\GameRepository;
use App\Repositories\LocationRepository;
use App\Repositories\NewsRepository;
use App\Repositories\RecipeRepository;
use App\Repositories\RegionRepository;
use App\Repositories\VideoRepository;
use App\Services\ComicService;
use App\Services\GalleryPictureService;
use App\Services\GalleryService;
use App\Services\GameService;
use App\Services\NewsAggregatorService;
use App\Services\RecipeService;
use App\Services\SearchService;
use App\Services\SidebarPartsProviderService;
use App\Services\SkillService;
use App\Services\StreamService;
use App\Services\StreamStatService;
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

                'eventRepository' => function (ContainerInterface $container) {
                    return new EventRepository(
                        $container->db,
                        $container->auth
                    );
                },

                'galleryPictureRepository' => function (ContainerInterface $container) {
                    return new GalleryPictureRepository(
                        $container->db,
                        $container->tagRepository
                    );
                },

                'gameRepository' => function (ContainerInterface $container) {
                    return new GameRepository(
                        $container->db,
                        $container->config
                    );
                },

                'locationRepository' => function (ContainerInterface $container) {
                    return new LocationRepository(
                        $container->db
                    );
                },

                'newsRepository' => function (ContainerInterface $container) {
                    return new NewsRepository(
                        $container->db,
                        $container->auth
                    );
                },

                'recipeRepository' => function (ContainerInterface $container) {
                    return new RecipeRepository(
                        $container->db
                    );
                },

                'regionRepository' => function (ContainerInterface $container) {
                    return new RegionRepository(
                        $container->db
                    );
                },

                'videoRepository' => function (ContainerInterface $container) {
                    return new VideoRepository(
                        $container->db,
                        $container->auth
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
                        $container->settingsProvider,
                        $thumbStrategy,
                        $gallerySettings
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
                        $container->settingsProvider,
                        $thumbStrategy,
                        $comicsSettings
                    );
                },

                'config' => function (ContainerInterface $container) {
                    return new Config(
                        $container->settingsProvider
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

                'galleryLinkMapper' => function (ContainerInterface $container) {
                    return new GalleryLinkMapper(
                        $container->settingsProvider,
                        $container->galleryPictureRepository,
                        $container->renderer,
                        $container->linker
                    );
                },

                'itemLinkMapper' => function (ContainerInterface $container) {
                    return new ItemLinkMapper(
                        $container->recipeRepository,
                        $container->renderer,
                        $container->linker
                    );
                },

                'spellLinkMapper' => function (ContainerInterface $container) {
                    return new SpellLinkMapper(
                        $container->recipeRepository,
                        $container->renderer,
                        $container->linker
                    );
                },

                'genericLinkMapper' => function (ContainerInterface $container) {
                    return new GenericLinkMapper(
                        $container->renderer,
                        $container->linker
                    );
                },

                'doubleBracketsConfig' => function (ContainerInterface $container) {
                    $config = new LinkMapperSource();

                    $config->setDefaultMapper($container->articleLinkMapper);
                    
                    $config->registerTaggedMappers(
                        [
                            $container->newsLinkMapper,
                            $container->eventLinkMapper,
                            $container->streamLinkMapper,
                            $container->videoLinkMapper,
                            $container->hsCardLinkMapper,
                            $container->coordsLinkMapper,
                            $container->galleryLinkMapper,
                            $container->itemLinkMapper,
                            $container->spellLinkMapper,
                        ]
                    );

                    $config->setGenericMapper($container->genericLinkMapper);

                    return $config;
                },

                'newsParser' => function (ContainerInterface $container) {
                    return new NewsParser(
                        $container->settingsProvider,
                        $container->renderer,
                        $container->linker
                    );
                },
                
                'forumParser' => function (ContainerInterface $container) {
                    return new ForumParser($container);
                },
                
                // handlers
                
                'notFoundHandler' => function (ContainerInterface $container) {
                    return new NotFoundHandler($container);
                },
                
                // services

                'comicService' => function (ContainerInterface $container) {
                    return new ComicService();
                },

                'galleryPictureService' => function (ContainerInterface $container) {
                    return new GalleryPictureService(
                        $container->gallery
                    );
                },

                'galleryService' => function (ContainerInterface $container) {
                    $pageSize = $this->settings['gallery']['pics_per_page'];
                    
                    return new GalleryService($pageSize);
                },

                'gameService' => function (ContainerInterface $container) {
                    return new GameService(
                        $container->config
                    );
                },

                'newsAggregatorService' => function (ContainerInterface $container) {
                    return new NewsAggregatorService(
                        $container->newsRepository
                    );
                },

                'recipeService' => function (ContainerInterface $container) {
                    return new RecipeService(
                        $container->config,
                        $container->linker
                    );
                },

                'searchService' => function (ContainerInterface $container) {
                    return new SearchService(
                        $container->tagRepository,
                        $container->linker
                    );
                },

                'sidebarPartsProviderService' => function (ContainerInterface $container) {
                    return new SidebarPartsProviderService(
                        $container->settingsProvider,
                        $container->newsAggregatorService,
                        $container->streamService
                    );
                },

                'skillService' => function (ContainerInterface $container) {
                    return new SkillService(
                        $this->config
                    );
                },

                'streamService' => function (ContainerInterface $container) {
                    return new StreamService(
                        $container->config,
                        $container->cases
                    );
                },

                'streamStatService' => function (ContainerInterface $container) {
                    return new StreamStatService(
                        $container->gameRepository,
                        $container->gameService
                    );
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
