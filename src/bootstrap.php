<?php

function debugModeOn() {
	global $debug;
	
	if ($debug !== true) {
		error_reporting(E_ALL & ~E_NOTICE);
		ini_set("display_errors", 1);
		
		$debug = true;
	}
}

if (isset($_GET['debug'])) {
	debugModeOn();
}

$root = __DIR__ . '/..';

require $root . '/vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv($root);
$dotenv->load();

session_start();

$path = $root . '/settings/';
$appSettings = \Plasticode\Core\Settings::load($path, function($settings) {
	$settings['twitch']['client_id'] = getenv('TWITCH_CLIENT_ID');
	$settings['telegram']['bot_token'] = getenv('TELEGRAM_BOT_TOKEN');
	
	return $settings;
});

$app = new \Slim\App($appSettings);
$container = $app->getContainer();
$settings = $container->get('settings');

if ($settings['debug']) {
	debugModeOn();
}

require $src . 'dependencies.php';

$app->add(new \Plasticode\Middleware\SlashMiddleware($container));
$app->add(new \Plasticode\Middleware\CookieAuthMiddleware($container, $settings['auth_token_key']));

require $src . 'routes.php';

$app->run();
