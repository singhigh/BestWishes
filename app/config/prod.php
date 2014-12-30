<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../../templates');
$app['twig.options'] = array(
    'cache' => __DIR__ . '/../cache/twig',
    'strict_variables' => true
);

