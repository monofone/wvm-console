<?php

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'dbname' => $app['db_name'],
        'host' => $app['db_host'],
        'user' => $app['db_user'],
        'password' => $app['db_password']
    ),
));
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path' => array(__DIR__ . '/../templates'),
    'twig.options' => array('cache' => __DIR__ . '/../cache'),
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                    // add custom globals, filters, tags, ...

                    return $twig;
                }));
$app->register($sc = new \Silex\Provider\ServiceControllerServiceProvider());

$app->register($p = new Silex\Provider\WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => __DIR__.'/../cache/profiler',
));

$app->mount('/_profiler', $p);

$app['twitterConnection'] = $app->share(function($app) {
            return new Twitter($app['consumer_key'], $app['consumer_secret'], $app['access_token'], $app['access_token_secret']);
        });
$app['eventChecker'] = $app->share(function($app) {
            return new Blage\EventChecker($app['db'], $app['twitterConnection'], $app['eventCheckerConfiguration']);
        });

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^/',
            'http' => true,
            'users' => array(
                // raw password is foo
                'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
            ),
        ),
    )
));
