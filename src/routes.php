<?php

use Plasticode\Core\Core;
use Plasticode\Middleware\AuthMiddleware;
use Plasticode\Middleware\GuestMiddleware;
use Plasticode\Middleware\AccessMiddleware;
use Plasticode\Middleware\TokenAuthMiddleware;

$access = function ($entity, $action, $redirect = null) use ($container) {
	return new AccessMiddleware($container, $entity, $action, $redirect);
};

$root = $settings['root'];
$trueRoot = (strlen($root) == 0);

$app->group($root, function () use ($trueRoot, $settings, $access, $container) {
	// api
	
	$this->group('/api/v1', function () use ($settings) {
		$this->get('/captcha', function ($request, $response, $args) use ($settings) {
			$captcha = $this->captcha->generate($settings['captcha_digits'], true);
			return Core::json($response, [ 'captcha' => $captcha['captcha'] ]);
		});

		$this->get('/captcha_test', function ($request, $response, $args) use ($settings) {
		    $digits = $request->getParam('digits', $settings['captcha_digits']);
			$captcha = $this->captcha->generate($digits, false);

			return Core::json($response, [ 'captcha' => $captcha['captcha'] ]);
		});

    	$this->get('/search/{query}', \App\Controllers\SearchController::class . ':search')
    	    ->setName('api.search');

    	$this->get('/gallery/chunk/{border_id}', \App\Controllers\GalleryController::class . ':chunk')
    	    ->setName('api.gallery.chunk');
	});
	
	$this->group('/api/v1', function () use ($settings, $access, $container) {
		foreach ($settings['tables'] as $alias => $table) {
			if (isset($table['api'])) {
				$gen = $container->generatorResolver->resolveEntity($alias);
				$gen->generateAPIRoutes($this, $access);
			}
		}
	
		$this->post('/parser/parse', \Plasticode\Controllers\ParserController::class . ':parse')
			->setName('api.parser.parse');
	})->add(new TokenAuthMiddleware($container));
	
	// admin
	
	$this->get('/admin', function ($request, $response, $args) {
		return $this->view->render($response, 'admin/index.twig');
	})->setName('admin.index');
	
	$this->group('/admin', function () use ($settings, $access, $container) {
		foreach (array_keys($settings['entities']) as $entity) {
			$gen = $container->generatorResolver->resolveEntity($entity);
			$gen->generateAdminPageRoute($this, $access);
		}
		
    	$this->get('/playground', \App\Controllers\Admin\PlaygroundController::class)->setName('admin.playground');
    	$this->post('/comics/upload', \App\Controllers\Admin\ComicController::class . ':upload')->setName('admin.comics.upload');
    	$this->post('/gallery/upload', \App\Controllers\Admin\GalleryController::class . ':upload')->setName('admin.gallery.upload');
	})->add(new AuthMiddleware($container, 'admin.index'));

	// site
	
	$this->get('/news/{id:\d+}', \App\Controllers\NewsController::class . ':item')->setName('main.news');
	$this->get('/news/archive', \App\Controllers\NewsController::class . ':archiveIndex')->setName('main.news.archive');
	$this->get('/news/archive/{year:\d+}', \App\Controllers\NewsController::class . ':archiveYear')->setName('main.news.archive.year');
	$this->get('/rss', \App\Controllers\NewsController::class . ':rss')->setName('main.rss');
	
	//$this->get('/articles/source/{id}[/{cat}]', \App\Controllers\ArticleController::class . ':source')->setName('main.articles.convert');
	$this->get('/articles/{id}[/{cat}]', \App\Controllers\ArticleController::class . ':item')->setName('main.article');
	
	$this->get('/streams', \App\Controllers\StreamController::class . ':index')->setName('main.streams');
	$this->get('/streams/{alias}', \App\Controllers\StreamController::class . ':item')->setName('main.stream');

	$this->get('/gallery', \App\Controllers\GalleryController::class . ':index')->setName('main.gallery');
	$this->get('/gallery/{id:\d+}', \App\Controllers\GalleryController::class . ':picture')->setName('main.gallery.picture.direct');
	$this->get('/gallery/{alias}', \App\Controllers\GalleryController::class . ':author')->setName('main.gallery.author');
	$this->get('/gallery/{alias}/{id:\d+}', \App\Controllers\GalleryController::class . ':picture')->setName('main.gallery.picture');
	
	$this->get('/map', \App\Controllers\MapController::class . ':index')->setName('main.map');
	
	$this->get('/comics', \App\Controllers\ComicController::class . ':index')->setName('main.comics');
	$this->get('/comics/series/{alias}', \App\Controllers\ComicController::class . ':series')->setName('main.comics.series');
	$this->get('/comics/series/{alias}/{number:\d+}', \App\Controllers\ComicController::class . ':issue')->setName('main.comics.issue');
	$this->get('/comics/series/{alias}/{number:\d+}/{page:\d+}', \App\Controllers\ComicController::class . ':issuePage')->setName('main.comics.issue.page');
	$this->get('/comics/{alias}', \App\Controllers\ComicController::class . ':standalone')->setName('main.comics.standalone');
	$this->get('/comics/{alias}/{page:\d+}', \App\Controllers\ComicController::class . ':standalonePage')->setName('main.comics.standalone.page');

	$this->get('/recipes/{id:\d+}', \App\Controllers\RecipeController::class . ':item')->setName('main.recipe');
	$this->get('/recipes[/{skill}]', \App\Controllers\RecipeController::class . ':index')->setName('main.recipes');

	$this->get('/events', \App\Controllers\EventController::class . ':index')->setName('main.events');
	$this->get('/events/{id:\d+}', \App\Controllers\EventController::class . ':item')->setName('main.event');

	$this->get('/videos', \App\Controllers\VideoController::class . ':index')->setName('main.videos');
	$this->get('/videos/{id:\d+}', \App\Controllers\VideoController::class . ':item')->setName('main.video');

	$this->get('/tags/{tag}', \App\Controllers\TagController::class . ':item')->setName('main.tag');

	$this->get($trueRoot ? '/[{game}]' : '[/{game}]', \App\Controllers\NewsController::class . ':index')->setName('main.index');

	// cron
	
	$this->group('/cron', function () {
		$this->get('/streams/refresh', \App\Controllers\StreamController::class . ':refresh')->setName('main.cron.streams.refresh');
	});

	// auth
	
	$this->group('/auth', function () {
		$this->post('/signup', \Plasticode\Controllers\Auth\AuthController::class . ':postSignUp')->setName('auth.signup');
		$this->post('/signin', \Plasticode\Controllers\Auth\AuthController::class . ':postSignIn')->setName('auth.signin');
	})->add(new GuestMiddleware($container, 'main.index'));
		
	$this->group('/auth', function () {
		$this->post('/signout', \Plasticode\Controllers\Auth\AuthController::class . ':postSignOut')->setName('auth.signout');
		$this->post('/password/change', \Plasticode\Controllers\Auth\PasswordController::class . ':postChangePassword')->setName('auth.password.change');
	})->add(new AuthMiddleware($container, 'main.index'));
	
	// tests
	
	$this->group('/tests', function () {
	    $this->get('/smoke', \App\Controllers\Tests\SmokeTestController::class)
	        ->setName('tests.smoke');
	})->add(new AuthMiddleware($container, 'main.index'));
});
