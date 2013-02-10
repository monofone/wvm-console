<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('WVM twitter console', 'n/a');
/*
$console
    ->register('my-command')
    ->setDefinition(array(
        // new InputOption('some-option', null, InputOption::VALUE_NONE, 'Some help'),
    ))
    ->setDescription('My command description')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        // do something
    })
;*/
$console->register('route-list')
	->setDefinition(array())
	->setDescription('prepare application to run')
	->setCode(function(InputInterface $input, OutputInterface $output) use ($app){
	include_once __DIR__ . '/controllers.php';
	//freeze the controllers and add the resulting
	$app['routes']->addCollection($app['controllers']->flush());
	$pattern = $app['routes']->all();
	$output->getFormatter()->setStyle('routeName', new \Symfony\Component\Console\Formatter\OutputFormatterStyle('red'));
	$output->getFormatter()->setStyle('pattern', new \Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow'));
	foreach($pattern as $routeName => $route){
		$output->writeln('<routeName>'.$routeName . '</routeName>  => <pattern>' . $route->getPattern().'</pattern>');
	}
});
$console
    ->register('check-events')
    ->setDefinition(array(
        // new InputOption('some-option', null, InputOption::VALUE_NONE, 'Some help'),
    ))
    ->setDescription('checks the event table and sends tweet if any')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $app['eventChecker']->sendTweets();

    })
;
return $console;
