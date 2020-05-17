<?php

namespace App\Config;

use App\Config\Parsing\BBContainerConfig;
use App\Core\AppContext;
use App\Core\Linker;
use App\Core\Renderer;
use App\Factories\UpdateStreamsJobFactory;
use App\Handlers\NotFoundHandler;
use App\Hydrators\ArticleCategoryHydrator;
use App\Hydrators\ArticleHydrator;
use App\Hydrators\ComicIssueHydrator;
use App\Hydrators\ComicIssuePageHydrator;
use App\Hydrators\ComicSeriesHydrator;
use App\Hydrators\ComicStandaloneHydrator;
use App\Hydrators\ComicStandalonePageHydrator;
use App\Hydrators\EventHydrator;
use App\Hydrators\ForumHydrator;
use App\Hydrators\ForumMemberHydrator;
use App\Hydrators\ForumTopicHydrator;
use App\Hydrators\GalleryAuthorCategoryHydrator;
use App\Hydrators\GalleryAuthorHydrator;
use App\Hydrators\GalleryPictureHydrator;
use App\Hydrators\GameHydrator;
use App\Hydrators\ItemHydrator;
use App\Hydrators\MenuHydrator;
use App\Hydrators\NewsHydrator;
use App\Hydrators\RecipeHydrator;
use App\Hydrators\RegionHydrator;
use App\Hydrators\SkillHydrator;
use App\Hydrators\StreamHydrator;
use App\Hydrators\UserHydrator;
use App\Hydrators\VideoHydrator;
use App\Models\Article;
use App\Models\Comic;
use App\Models\ComicSeries;
use App\Models\ComicStandalone;
use App\Models\Event;
use App\Models\ForumTopic;
use App\Models\GalleryPicture;
use App\Models\News;
use App\Models\Stream;
use App\Models\Video;
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
use App\Repositories\ComicIssuePageRepository;
use App\Repositories\ComicIssueRepository;
use App\Repositories\ComicPublisherRepository;
use App\Repositories\ComicSeriesRepository;
use App\Repositories\ComicStandalonePageRepository;
use App\Repositories\ComicStandaloneRepository;
use App\Repositories\EventRepository;
use App\Repositories\EventTypeRepository;
use App\Repositories\ForumMemberRepository;
use App\Repositories\ForumPostRepository;
use App\Repositories\ForumRepository;
use App\Repositories\ForumTagRepository;
use App\Repositories\ForumTopicRepository;
use App\Repositories\GalleryAuthorCategoryRepository;
use App\Repositories\GalleryAuthorRepository;
use App\Repositories\GalleryPictureRepository;
use App\Repositories\GameRepository;
use App\Repositories\ItemRepository;
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
use App\Repositories\UserRepository;
use App\Repositories\VideoRepository;
use App\Services\ComicService;
use App\Services\ForumService;
use App\Services\GalleryPictureService;
use App\Services\GalleryService;
use App\Services\GameService;
use App\Services\ItemService;
use App\Services\NewsAggregatorService;
use App\Services\RecipeService;
use App\Services\SearchService;
use App\Services\SidebarPartsProviderService;
use App\Services\StreamService;
use App\Services\StreamStatService;
use App\Services\TagPartsProviderService;
use App\Services\TwitterService;
use Plasticode\Config\Bootstrap as BootstrapBase;
use Plasticode\Config\TagsConfig;
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
                        $c->parser
                    )
                )
            );

        $map['comicIssueRepository'] = fn (CI $c) =>
            new ComicIssueRepository(
                $c->repositoryContext,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new ComicIssueHydrator(
                        $c->comicIssuePageRepository,
                        $c->comicSeriesRepository,
                        $c->linker,
                        $c->parser
                    )
                )
            );

        $map['comicIssuePageRepository'] = fn (CI $c) =>
            new ComicIssuePageRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new ComicIssuePageHydrator(
                        $c->comicIssueRepository,
                        $c->linker
                    )
                )
            );

        $map['comicPublisherRepository'] = fn (CI $c) =>
            new ComicPublisherRepository(
                $c->repositoryContext
            );

        $map['comicSeriesRepository'] = fn (CI $c) =>
            new ComicSeriesRepository(
                $c->repositoryContext,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new ComicSeriesHydrator(
                        $c->comicIssueRepository,
                        $c->comicPublisherRepository,
                        $c->gameRepository,
                        $c->linker,
                        $c->parser
                    )
                )
            );

        $map['comicStandaloneRepository'] = fn (CI $c) =>
            new ComicStandaloneRepository(
                $c->repositoryContext,
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new ComicStandaloneHydrator(
                        $c->comicPublisherRepository,
                        $c->comicStandalonePageRepository,
                        $c->gameRepository,
                        $c->linker,
                        $c->parser
                    )
                )
            );

        $map['comicStandalonePageRepository'] = fn (CI $c) =>
            new ComicStandalonePageRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new ComicStandalonePageHydrator(
                        $c->comicStandaloneRepository,
                        $c->linker
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
                        $c->parser
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
                        $c->newsParser
                    )
                )
            );

        $map['galleryAuthorCategoryRepository'] = fn (CI $c) =>
            new GalleryAuthorCategoryRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new GalleryAuthorCategoryHydrator(
                        $c->galleryAuthorRepository
                    )
                )
            );

        $map['galleryAuthorRepository'] = fn (CI $c) =>
            new GalleryAuthorRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new GalleryAuthorHydrator(
                        $c->forumMemberRepository,
                        $c->galleryAuthorCategoryRepository,
                        $c->galleryPictureRepository,
                        $c->linker,
                        $c->parser
                    )
                )
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
                        $c->linker,
                        $c->parser
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
                        $c->forumRepository,
                        $c->gameRepository,
                        $c->linker,
                        $c->gameService
                    )
                )
            );

        $map['itemRepository'] = fn (CI $c) =>
            new ItemRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new ItemHydrator(
                        $c->recipeRepository,
                        $c->linker
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
                        $c->gameRepository,
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
                        $c->parser
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
                        $c->regionRepository
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
                $c->tagRepository,
                new ObjectProxy(
                    fn () =>
                    new StreamHydrator(
                        $c->gameRepository,
                        $c->userRepository,
                        $c->gameService,
                        $c->streamService,
                        $c->linker,
                        $c->parser
                    )
                )
            );

        $map['streamStatRepository'] = fn (CI $c) =>
            new StreamStatRepository(
                $c->repositoryContext
            );

        $map['userRepository'] = fn (CI $c) =>
            new UserRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new UserHydrator(
                        $c->forumMemberRepository,
                        $c->roleRepository,
                        $c->linker,
                        $c->gravatar
                    )
                )
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
                        $c->parser
                    )
                )
            );

        $map['appContext'] = fn (CI $c) =>
            new AppContext(
                $c->settingsProvider,
                $c->translator,
                $c->validator,
                $c->view,
                $c->logger,
                $c->menuRepository
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
            new TagsConfig(
                [
                    Article::class => 'articles',
                    News::class => 'news',
                    ForumTopic::class => 'news',
                    Event::class => 'events',
                    GalleryPicture::class => 'gallery',
                    ComicSeries::class => 'comics',
                    Comic::class => 'comics',
                    ComicStandalone::class => 'comics',
                    Video::class => 'videos',
                    Stream::class => 'streams',
                ]
            );

        $map['renderer'] = fn (CI $c) =>
            new Renderer(
                $c->view
            );

        $map['linker'] = fn (CI $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router,
                $c->gallery,
                $c->tagsConfig
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
            new ComicService(
                $c->comicIssueRepository,
                $c->comicSeriesRepository,
                $c->comicStandaloneRepository
            );

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

            return new GalleryService(
                $c->galleryAuthorRepository,
                $c->galleryPictureRepository,
                $pageSize
            );
        };

        $map['gameService'] = fn (CI $c) =>
            new GameService(
                $c->gameRepository,
                $c->config
            );

        $map['itemService'] = fn (CI $c) =>
            new ItemService(
                $c->itemRepository,
                $c->linker,
                $c->logger
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
                $c->articleRepository,
                $c->eventRepository,
                $c->newsRepository,
                $c->tagRepository,
                $c->linker
            );

        $map['sidebarPartsProviderService'] = fn (CI $c) =>
            new SidebarPartsProviderService(
                $c->settingsProvider,
                $c->articleRepository,
                $c->eventRepository,
                $c->galleryPictureRepository,
                $c->newsAggregatorService,
                $c->streamService
            );

        $map['streamService'] = fn (CI $c) =>
            new StreamService(
                $c->streamRepository,
                $c->config,
                $c->cases
            );

        $map['streamStatService'] = fn (CI $c) =>
            new StreamStatService(
                $c->gameRepository,
                $c->streamStatRepository,
                $c->gameService,
                $c->config
            );

        $map['tagPartsProviderService'] = fn (CI $c) =>
            new TagPartsProviderService(
                $c->articleRepository,
                $c->eventRepository,
                $c->galleryPictureRepository,
                $c->videoRepository,
                $c->comicService,
                $c->newsAggregatorService,
                $c->streamService
            );

        $map['twitterService'] = fn (CI $c) =>
            new TwitterService(
                $c->linker
            );

        $map['updateStreamsJobFactory'] = fn (CI $c) =>
            new UpdateStreamsJobFactory(
                $c->settingsProvider,
                $c->cache,
                $c->linker,
                $c->twitch,
                $c->telegram,
                $c->logger,
                $c->streamRepository,
                $c->streamStatRepository,
                $c->streamStatService
            );

        // handlers

        $map['notFoundHandler'] = fn (CI $c) =>
            new NotFoundHandler(
                $c
            );

        return $map;
    }
}
