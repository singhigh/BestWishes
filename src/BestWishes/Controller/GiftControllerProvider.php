<?php
namespace BestWishes\Controller;


use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class GiftControllerProvider implements ControllerProviderInterface {
    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function (Application $app) {
            return $app->redirect('/hello');
        });

        return $controllers;
    }
}