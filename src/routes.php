<?php

use App\Controllers\ArticleController;
use App\Controllers\ComicController;
use App\Controllers\EventController;
use App\Controllers\GalleryController;
use App\Controllers\MapController;
use App\Controllers\NewsController;
use App\Controllers\RecipeController;
use App\Controllers\SearchController;
use App\Controllers\StreamController;
use App\Controllers\TagController;
use App\Controllers\VideoController;
use App\Controllers\Admin\ComicIssueController as AdminComicIssueController;
use App\Controllers\Admin\ComicStandaloneController as AdminComicStandaloneController;
use App\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Controllers\Admin\PlaygroundController as AdminPlaygroundController;
use App\Controllers\Tests\SmokeTestController;
use Plasticode\Controllers\Auth\AuthController;
use Plasticode\Controllers\Auth\PasswordController;
use Plasticode\Controllers\ParserController;
use Plasticode\Core\Response;
use Plasticode\Middleware\AuthMiddleware;
use Plasticode\Middleware\GuestMiddleware;
use Plasticode\Middleware\AccessMiddleware;
use Plasticode\Middleware\TokenAuthMiddleware;

/** @var ContainerInterface $container */

/**
 * Creates AccessMiddleware.
 * 
 * @var \Closure
 */
$access = fn (string $entity, string $action, ?string $redirect = null)
    => new AccessMiddleware(
        $container->access,
        $container->auth,
        $container->router,
        $entity,
        $action,
        $redirect
    );

$root = $settings['root'];
$trueRoot = (strlen($root) == 0);

$app->group(
    $root,
    function () use ($trueRoot, $settings, $access, $container, $env) {
        // public api

        $this->group(
            '/api/v1',
            function () use ($settings) {
                $this->get(
                    '/captcha',
                    function ($request, $response) use ($settings) {
                        $captcha = $this->captcha->generate(
                            $settings['captcha_digits'], true
                        );

                        return Response::json(
                            $response, ['captcha' => $captcha['captcha']]
                        );
                    }
                );

                $this->get(
                    '/captcha_test',
                    function ($request, $response, $args) use ($settings) {
                        $digits = $request->getParam(
                            'digits', $settings['captcha_digits']
                        );

                        $captcha = $this->captcha->generate($digits, false);

                        return Response::json(
                            $response, ['captcha' => $captcha['captcha']]
                        );
                    }
                );

                $this->get(
                    '/search/{query}',
                    SearchController::class . ':search'
                )->setName('api.search');

                $this->get(
                    '/gallery/chunk/{border_id}',
                    GalleryController::class . ':chunk'
                )->setName('api.gallery.chunk');
            }
        );

        // private api

        $this->group(
            '/api/v1',
            function () use ($settings, $access, $container) {
                foreach ($settings['tables'] as $alias => $table) {
                    if (isset($table['api'])) {
                        $gen = $container->generatorResolver->resolveEntity($alias);
                        $gen->generateAPIRoutes($this, $access);
                    }
                }

                $this->post(
                    '/parser/parse',
                    ParserController::class . ':parse'
                )->setName('api.parser.parse');
            }
        )->add(new TokenAuthMiddleware($container->authService));

        // admin

        $this->get(
            '/admin',
            function ($request, $response, $args) {
                return $this->view->render($response, 'admin/index.twig');
            }
        )->setName('admin.index');

        $this
            ->group(
                '/admin',
                function () use ($settings, $access, $container) {
                    foreach (array_keys($settings['entities']) as $entity) {
                        $gen = $container
                            ->generatorResolver
                            ->resolveEntity($entity);

                        $gen->generateAdminPageRoute($this, $access);
                    }

                    $this
                        ->get(
                            '/playground',
                            AdminPlaygroundController::class
                        )
                        ->setName('admin.playground');

                    $this
                        ->post(
                            '/comics/issue/upload',
                            AdminComicIssueController::class . ':upload'
                        )
                        ->setName('admin.comics.issue.upload');

                    $this
                        ->post(
                            '/comics/standalone/upload',
                            AdminComicStandaloneController::class . ':upload'
                        )
                        ->setName('admin.comics.standalone.upload');

                    $this
                        ->post(
                            '/gallery/upload',
                            AdminGalleryController::class . ':upload'
                        )
                        ->setName('admin.gallery.upload');
                }
            )
            ->add(
                new AuthMiddleware(
                    $container->router,
                    $container->authService,
                    'admin.index'
                )
            );

        // site

        $this->get('/news/{id:\d+}', NewsController::class . ':item')
            ->setName('main.news');

        $this->get('/news/archive', NewsController::class . ':archiveIndex')
            ->setName('main.news.archive');

        $this->get('/news/archive/{year:\d+}', NewsController::class . ':archiveYear')
            ->setName('main.news.archive.year');

        $this->get('/rss', NewsController::class . ':rss')
            ->setName('main.rss');

        $this->get('/articles/{id}[/{cat}]', ArticleController::class . ':item')
            ->setName('main.article');

        $this->get('/streams', StreamController::class . ':index')
            ->setName('main.streams');

        $this->get('/streams/{alias}', StreamController::class . ':item')
            ->setName('main.stream');

        $this->get('/gallery', GalleryController::class . ':index')
            ->setName('main.gallery');

        $this->get('/gallery/{id:\d+}', GalleryController::class . ':picture')
            ->setName('main.gallery.picture.direct');

        $this->get('/gallery/{alias}', GalleryController::class . ':author')
            ->setName('main.gallery.author');

        $this->get('/gallery/{alias}/{id:\d+}', GalleryController::class . ':picture')
            ->setName('main.gallery.picture');

        $this->get('/map', MapController::class . ':index')
            ->setName('main.map');

        $this->get('/comics', ComicController::class . ':index')
            ->setName('main.comics');

        $this->get('/comics/series/{alias}', ComicController::class . ':series')
            ->setName('main.comics.series');

        $this->get(
            '/comics/series/{alias}/{number:\d+}',
            ComicController::class . ':issue'
        )->setName('main.comics.issue');

        $this->get(
            '/comics/series/{alias}/{number:\d+}/{page:\d+}',
            ComicController::class . ':issuePage'
        )->setName('main.comics.issue.page');

        $this->get('/comics/{alias}', ComicController::class . ':standalone')
            ->setName('main.comics.standalone');

        $this->get(
            '/comics/{alias}/{page:\d+}',
            ComicController::class . ':standalonePage'
        )->setName('main.comics.standalone.page');

        $this->get('/recipes/{id:\d+}', RecipeController::class . ':item')
            ->setName('main.recipe');

        $this->get('/recipes[/{skill}]', RecipeController::class . ':index')
            ->setName('main.recipes');

        $this->get('/events', EventController::class . ':index')
            ->setName('main.events');

        $this->get('/events/{id:\d+}', EventController::class . ':item')
            ->setName('main.event');

        $this->get('/videos', VideoController::class . ':index')
            ->setName('main.videos');

        $this->get('/videos/{id:\d+}', VideoController::class . ':item')
            ->setName('main.video');

        $this->get('/tags/{tag}', TagController::class . ':item')
            ->setName('main.tag');

        $this->get(
            $trueRoot ? '/[{game}]' : '[/{game}]',
            NewsController::class . ':index'
        )->setName('main.index');

        // cron

        $this->group(
            '/cron',
            function () {
                $this->get(
                    '/streams/refresh',
                    StreamController::class . ':refresh'
                )->setName('main.cron.streams.refresh');
            }
        );

        // public auth

        $this
            ->group(
                '/auth',
                function () {
                    $this->post('/signup', AuthController::class . ':postSignUp')
                        ->setName('auth.signup');

                    $this->post('/signin', AuthController::class . ':postSignIn')
                        ->setName('auth.signin');
                }
            )
            ->add(
                new GuestMiddleware(
                    $container->router,
                    $container->authService,
                    'main.index'
                )
            );

        // private auth

        $this
            ->group(
                '/auth',
                function () {
                    $this
                        ->post(
                            '/signout',
                            AuthController::class . ':postSignOut'
                        )
                        ->setName('auth.signout');

                    $this
                        ->post(
                            '/password/change',
                            PasswordController::class . ':postChangePassword'
                        )
                        ->setName('auth.password.change');
                }
            )
            ->add(
                new AuthMiddleware(
                    $container->router,
                    $container->authService,
                    'main.index'
                )
            );

        // tests

        if ($env->isDev()) {
            $this->group(
                '/tests',
                function () {
                    $this->get('/smoke', SmokeTestController::class)
                        ->setName('tests.smoke');
                }
            );
        }
    }
);
