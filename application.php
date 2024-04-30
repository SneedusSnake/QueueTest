#!/usr/bin/env php
<?php

$conf = require __DIR__ . '/kafka/bootstrap.php';

use App\Commands\Consumer;
use App\Commands\FireEvents;
use App\UserEventHandler;
use Symfony\Component\Console\Application;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$producer = new RdKafka\Producer($conf);
$producer->addBrokers("kafka");

$consumer = new RdKafka\KafkaConsumer($conf);


$application = new Application();

$application->add(new FireEvents($producer, 'FireEvents'));
$application->add(new Consumer($consumer, new UserEventHandler(), 'Consume'));
$application->run();



