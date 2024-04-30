#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use App\Commands\Consumer;
use App\Commands\FireEvents;
use App\UserEventHandler;
use Symfony\Component\Console\Application;

$conf = new RdKafka\Conf();
//$conf->set('log_level', (string) LOG_DEBUG);
//$conf->set('debug', 'all');
$conf->set('socket.timeout.ms', 50);
$conf->set('socket.blocking.max.ms', 1);
$conf->set('queue.buffering.max.ms', 1);
$conf->set('queue.buffering.max.messages', 10);
$conf->set('group.id', 'defaultConsumerGroup');
$conf->set('metadata.broker.list', 'kafka');
$conf->set('auto.offset.reset', 'earliest');
$conf->set('enable.partition.eof', 'true');

$conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
    switch ($err) {
        case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
            echo "Assign: ";
            var_dump($partitions);
            $kafka->assign($partitions);
            break;

         case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
             echo "Revoke: ";
             var_dump($partitions);
             $kafka->assign(NULL);
             break;

         default:
            throw new \Exception($err);
    }
});

$producer = new RdKafka\Producer($conf);
$producer->addBrokers("kafka");

$consumer = new RdKafka\KafkaConsumer($conf);


$application = new Application();

$application->add(new FireEvents($producer, 'FireEvents'));
$application->add(new Consumer($consumer, new UserEventHandler(), 'Consume'));
$application->run(null);



