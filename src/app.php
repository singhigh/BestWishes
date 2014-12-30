<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader;

use BestWishes\Config\ConfigLoader;
use BestWishes\Config\DbConfigLoader;
use BestWishes\Version;

$configPath = __DIR__ . '/../app/config';
$config = new ConfigLoader($configPath);

$app = new Application();
$app->register(new RoutingServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => $config->getDatabaseConfig()
));
$app->register(new TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en'),
));
$app->register(new LocaleServiceProvider());

$app['version'] = Version::VERSION;


$app['translator'] = $app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    $translator->addResource('yaml', __DIR__.'/../app/locales/en.yml', 'en');
    $translator->addResource('yaml', __DIR__.'/../app/locales/fr.yml', 'fr');

    return $translator;
});
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    //$twig->addGlobal('version', Version::VERSION);
    $twig->addGlobal('theme', $app['session']->get('theme', 'default'));
    $twig->addGlobal('sessOK', $app['session']->get('isOK', false));
    $twig->addGlobal('sessLastLogin', $app['session']->get('lastLogin', false));

    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
        return $app['request_stack']->getMasterRequest()->getBasepath().'/'.$asset;
    }));
    $twig->addFunction(new \Twig_SimpleFunction('thmasset', function ($asset) use ($app) {
        return $app['request_stack']->getMasterRequest()->getBasepath().'/theme/' . $app['session']->get('theme', 'default') . '/'.$asset;
    }));

    return $twig;
});


$dbConfig = new DbConfigLoader($app['db']);

return $app;
