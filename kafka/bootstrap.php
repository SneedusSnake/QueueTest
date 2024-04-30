<?php

require __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$conf = new RdKafka\Conf();
$conf->set('log_level', $_ENV['LOG_LEVEL']);
$conf->set('debug', $_ENV['DEBUG']);
$conf->set('socket.timeout.ms', $_ENV['SOCKET_TIMEOUT_MS']);
$conf->set('socket.blocking.max.ms', $_ENV['SOCKET_BLOCKING_MAX_MS']);
$conf->set('queue.buffering.max.ms', $_ENV['SOCKET_BUFFERING_MAX_MS']);
$conf->set('queue.buffering.max.messages', $_ENV['QUEUE_BUFFERING_MAX_MESSAGES']);
$conf->set('group.id', $_ENV['GROUP_ID']);
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

return $conf;