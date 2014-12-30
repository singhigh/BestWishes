<?php
namespace BestWishes\Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class MainControllerProvider implements ControllerProviderInterface {
    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function () use ($app) {
            return $app['twig']->render('home.twig', array());
        })
            ->bind('homepage');

        $controllers->get('/options', function () use ($app) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        })
            ->bind('options');

        $controllers->get('/logout', function () use ($app) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        })
            ->bind('logout');

        return $controllers;
    }

}