<?php

namespace App\Commands;

use App\UserEvent;
use App\UserEventHandler;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RdKafka\KafkaConsumer;
use RdKafka\Message;

class Consumer extends Command {

    public function __construct(private readonly KafkaConsumer $consumer, private readonly UserEventHandler $handler, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Consuming events');

        $this->consumer->subscribe(['test']);

        while (true) {
            $message = $this->consumer->consume(120*1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $event = $this->transformMessageIntoEvent($message);
                    $this->handler->handle($event);
                    $output->writeln('Handled event for userId: ' . $event->userId);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    $output->writeln("No more messages; will wait for more");
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    $output->writeln("Timed out");
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }

        return Command::SUCCESS;
    }

    private function transformMessageIntoEvent(Message $message): UserEvent
    {
        $payload = json_decode($message->payload, true);

        return new UserEvent(
            $payload['user_id'],
            new DateTimeImmutable($payload['occurred_on'])
        );
    }
}