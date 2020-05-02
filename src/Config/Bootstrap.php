<?php

namespace App\Config;

use App\Config\Parsing\BBContainerConfig;
use App\Core\Linker;
use App\Core\Renderer;
use App\Handlers\NotFoundHandler;
use App\Hydrators\ArticleCategoryHydrator;
use App\Hydrators\ArticleHydrator;
use App\Hydrators\EventHydrator;
use App\Hydrators\ForumHydrator;
use App\Hydrators\ForumMemberHydrator;
use App\Hydrators\ForumTopicHydrator;
use App\Hydrators\GalleryPictureHydrator;
use App\Hydrators\GameHydrator;
use App\Hydrators\MenuHydrator;
use App\Hydrators\NewsHydrator;
use App\Hydrators\RecipeHydrator;
use App\Hydrators\RegionHydrator;
use App\Hydrators\SkillHydrator;
use App\Hydrators\StreamHydrator;
use App\Hydrators\VideoHydrator;
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
use App\Repositories\EventTypeRepository;
use App\Repositories\ForumMemberRepository;
use App\Repositories\ForumPostRepository;
use App\Repositories\ForumRepository;
use App\Repositories\ForumTagRepository;
use App\Repositories\ForumTopicRepository;
use App\Repositories\GalleryAuthorRepository;
use App\Repositories\GalleryPictureRepository;
use App\Repositories\GameRepository;
use App\Repositories\LocationRepository;
use App\Repositories\MenuItemRepository;
use App\Repositories\MenuRepository;
use App\Repositories\NewsRepository;
use App\Repositories\RecipeRepository;
use App\Repositories\RecipeSourceRepository;
use App\Repositories\RegionRepository;
use App\Repositories\SkillRepository;
use App\Repositories\StreamRepository;
use App\Repositories\StreamStatRepository;
use App\Repositories\VideoRepository;
use App\Services\ComicService;
use App\Services\ForumService;
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
use Plasticode\Hydrators\MenuItemHydrator;
use Plasticode\ObjectProxy;
use Plasticode\Parsing\LinkMapperSource;
use Psr\Container\ContainerInterface as CI;

class Bootstrap extends BootstrapBase
{
    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
    {
        $map = parent::getMappings();

        $map['articleCategoryRepository'] = fn (CI $c) =>
            new ArticleCategoryRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new ArticleCategoryHydrator(
                    )
                )
            );

        $map['articleRepository'] = fn (CI $c) =>
            new ArticleRepository(
                $c->repositoryContext,
                $c->articleCategoryRepository,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new ArticleHydrator(
                        $c->articleCategoryRepository,
                        $c->articleRepository,
                        $c->gameRepository,
                        $c->userRepository,
                        $c->cutParser,
                        $c->linker,
                        $c->parser,
                        $c->tagsConfig
                    )
                )
            );

        $map['eventRepository'] = fn (CI $c) =>
            new EventRepository(
                $c->repositoryContext,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new EventHydrator(
                        $c->eventTypeRepository,
                        $c->gameRepository,
                        $c->regionRepository,
                        $c->userRepository,
                        $c->cutParser,
                        $c->linker,
                        $c->parser,
                        $c->tagsConfig
                    )
                )
            );

        $map['eventTypeRepository'] = fn (CI $c) =>
            new EventTypeRepository(
                $c->repositoryContext
            );

        $map['forumMemberRepository'] = fn (CI $c) =>
            new ForumMemberRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new ForumMemberHydrator(
                        $c->linker
                    )
                )
            );

        $map['forumPostRepository'] = fn (CI $c) =>
            new ForumPostRepository(
                $c->repositoryContext
            );

        $map['forumRepository'] = fn (CI $c) =>
            new ForumRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new ForumHydrator(
                        $c->forumRepository,
                        $c->gameRepository,
                        $c->forumService
                    )
                )
            );

        $map['forumTagRepository'] = fn (CI $c) =>
            new ForumTagRepository(
                $c->repositoryContext
            );

        $map['forumTopicRepository'] = fn (CI $c) =>
            new ForumTopicRepository(
                $c->repositoryContext,
                $c->forumTagRepository,
                $c->gameRepository,
                new ObjectProxy(
                    fn () =>
                    new ForumTopicHydrator(
                        $c->forumMemberRepository,
                        $c->forumPostRepository,
                        $c->forumRepository,
                        $c->forumTagRepository,
                        $c->gameRepository,
                        $c->cutParser,
                        $c->forumParser,
                        $c->linker,
                        $c->newsParser,
                        $c->tagsConfig
                    )
                )
            );

        $map['galleryAuthorRepository'] = fn (CI $c) =>
            new GalleryAuthorRepository(
                $c->repositoryContext
            );

        $map['galleryPictureRepository'] = fn (CI $c) =>
            new GalleryPictureRepository(
                $c->repositoryContext,
                $c->tagRepository,
                $c->linker,
                new ObjectProxy(
                    fn () =>
                    new GalleryPictureHydrator(
                        $c->galleryAuthorRepository,
                        $c->galleryPictureRepository,
                        $c->gameRepository,
                        $c->linker
                    )
                )
            );

        $map['gameRepository'] = fn (CI $c) =>
            new GameRepository(
                $c->repositoryContext,
                $c->config,
                new ObjectProxy(
                    fn () =>
                    new GameHydrator(
                    )
                )
            );

        $map['locationRepository'] = fn (CI $c) =>
            new LocationRepository(
                $c->repositoryContext
            );

        $map['menuItemRepository'] = fn (CI $c) =>
            new MenuItemRepository(
                $c->repositoryContext,
                new MenuItemHydrator(
                    $c->linker
                )
            );

        $map['menuRepository'] = fn (CI $c) =>
            new MenuRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new MenuHydrator(
                        $c->menuItemRepository,
                        $c->linker
                    )
                )
            );

        $map['newsRepository'] = fn (CI $c) =>
            new NewsRepository(
                $c->repositoryContext,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new NewsHydrator(
                        $c->gameRepository,
                        $c->userRepository,
                        $c->cutParser,
                        $c->linker,
                        $c->parser,
                        $c->tagsConfig
                    )
                )
            );

        $map['recipeRepository'] = fn (CI $c) =>
            new RecipeRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new RecipeHydrator(
                        $c->recipeSourceRepository,
                        $c->skillRepository,
                        $c->linker
                    )
                )
            );

        $map['recipeSourceRepository'] = fn (CI $c) =>
            new RecipeSourceRepository(
                $c->repositoryContext
            );

        $map['regionRepository'] = fn (CI $c) =>
            new RegionRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new RegionHydrator(
                    )
                )
            );

        $map['skillRepository'] = fn (CI $c) =>
            new SkillRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new SkillHydrator(
                        $c->linker,
                        $c->config
                    )
                )
            );

        $map['streamRepository'] = fn (CI $c) =>
            new StreamRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new StreamHydrator(
                    )
                )
            );

        $map['streamStatRepository'] = fn (CI $c) =>
            new StreamStatRepository(
                $c->repositoryContext
            );

        $map['videoRepository'] = fn (CI $c) =>
            new VideoRepository(
                $c->repositoryContext,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new VideoHydrator(
                        $c->gameRepository,
                        $c->userRepository,
                        $c->cutParser,
                        $c->linker,
                        $c->parser,
                        $c->tagsConfig
                    )
                )
            );

        $map['gallery'] = function (CI $c) {
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
                $c->settingsProvider,
                $thumbStrategy,
                $gallerySettings
            );
        };

        $map['comics'] = function (CI $c) {
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
                $c->settingsProvider,
                $thumbStrategy,
                $comicsSettings
            );
        };

        $map['config'] = fn (CI $c) =>
            new Config(
                $c->settingsProvider
            );

        $map['captchaConfig'] = fn (CI $c) =>
            new CaptchaConfig();

        $map['localizationConfig'] = fn (CI $c) =>
            new LocalizationConfig();

        $map['tagsConfig'] = fn (CI $c) =>
            new TagsConfig();

        $map['renderer'] = fn (CI $c) =>
            new Renderer(
                $c->view
            );

        $map['linker'] = fn (CI $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router,
                $c->gallery
            );

        $map['bbContainerConfig'] = fn (CI $c) =>
            new BBContainerConfig();

        $map['articleLinkMapper'] = fn (CI $c) =>
            new ArticleLinkMapper(
                $c->articleRepository,
                $c->tagRepository,
                $c->renderer,
                $c->linker,
                $c->tagLinkMapper
            );

        $map['eventLinkMapper'] = fn (CI $c) =>
            new EventLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['streamLinkMapper'] = fn (CI $c) =>
            new StreamLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['videoLinkMapper'] = fn (CI $c) =>
            new VideoLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['hsCardLinkMapper'] = fn (CI $c) =>
            new HsCardLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['coordsLinkMapper'] = fn (CI $c) =>
            new CoordsLinkMapper(
                $c->locationRepository,
                $c->renderer,
                $c->linker
            );

        $map['galleryLinkMapper'] = fn (CI $c) =>
            new GalleryLinkMapper(
                $c->settingsProvider,
                $c->galleryPictureRepository,
                $c->renderer,
                $c->linker
            );

        $map['itemLinkMapper'] = fn (CI $c) =>
            new ItemLinkMapper(
                $c->recipeRepository,
                $c->renderer,
                $c->linker
            );

        $map['spellLinkMapper'] = fn (CI $c) =>
            new SpellLinkMapper(
                $c->recipeRepository,
                $c->renderer,
                $c->linker
            );

        $map['genericLinkMapper'] = fn (CI $c) =>
            new GenericLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['doubleBracketsConfig'] = function (CI $c) {
            $config = new LinkMapperSource();

            $config->setDefaultMapper($c->articleLinkMapper);
            
            $config->registerTaggedMappers(
                [
                    $c->newsLinkMapper,
                    $c->eventLinkMapper,
                    $c->streamLinkMapper,
                    $c->videoLinkMapper,
                    $c->hsCardLinkMapper,
                    $c->coordsLinkMapper,
                    $c->galleryLinkMapper,
                    $c->itemLinkMapper,
                    $c->spellLinkMapper,
                ]
            );

            $config->setGenericMapper($c->genericLinkMapper);

            return $config;
        };

        $map['newsParser'] = fn (CI $c) =>
            new NewsParser(
                $c->settingsProvider,
                $c->renderer,
                $c->linker
            );

        $map['forumParser'] = fn (CI $c) =>
            new ForumParser();

        // services

        $map['comicService'] = fn (CI $c) =>
            new ComicService();

        $map['forumService'] = fn (CI $c) =>
            new ForumService(
                $c->gameRepository
            );

        $map['galleryPictureService'] = fn (CI $c) =>
            new GalleryPictureService(
                $c->gallery
            );

        $map['galleryService'] = function (CI $c) {
            $pageSize = $this->settings['gallery']['pics_per_page'];

            return new GalleryService($pageSize);
        };

        $map['gameService'] = fn (CI $c) =>
            new GameService(
                $c->gameRepository,
                $c->config
            );

        $map['newsAggregatorService'] = function (CI $c) {
            $service = new NewsAggregatorService(
                $c->linker
            );

            $service->registerStrictSource($c->newsRepository);
            $service->registerStrictSource($c->forumTopicRepository);

            $service->registerSource($c->articleRepository);
            $service->registerSource($c->eventRepository);
            $service->registerSource($c->videoRepository);

            return $service;
        };

        $map['recipeService'] = fn (CI $c) =>
            new RecipeService(
                $c->config,
                $c->linker
            );

        $map['searchService'] = fn (CI $c) =>
            new SearchService(
                $c->tagRepository,
                $c->linker
            );

        $map['sidebarPartsProviderService'] = fn (CI $c) =>
            new SidebarPartsProviderService(
                $c->settingsProvider,
                $c->newsAggregatorService,
                $c->streamService
            );

        $map['skillService'] = fn (CI $c) =>
            new SkillService(
                $this->config
            );

        $map['streamService'] = fn (CI $c) =>
            new StreamService(
                $c->config,
                $c->cases
            );

        $map['streamStatService'] = fn (CI $c) =>
            new StreamStatService(
                $c->gameRepository,
                $c->gameService
            );

        $map['tagPartsProviderService'] = fn (CI $c) =>
            new TagPartsProviderService(
                $c->galleryService,
                $c->newsAggregatorService,
                $c->streamService
            );

        $map['twitterService'] = fn (CI $c) =>
            new TwitterService(
                $c->linker
            );

        // handlers

        $map['notFoundHandler'] = fn (CI $c) =>
            new NotFoundHandler(
                $c
            );
        
        return $map;
    }
}
