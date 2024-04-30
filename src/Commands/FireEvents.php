<?php

namespace App\Commands;

use App\UserEvent;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RdKafka\Producer;
use RdKafka\TopicConf;
use RdKafka\Topic;
use Symfony\Component\Console\Input\InputArgument;

class FireEvents extends Command {

    public function __construct(private readonly Producer $producer, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('events', InputArgument::OPTIONAL, 'Number of events to fire', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Firing events');

        $this->fireEvents($input->getArgument('events'), $output);

        return Command::SUCCESS;
    }

    private function fireEvents(int $events, OutputInterface $output): void
    {
        $topic = $this->createTopic();

        $n = 1;
        for ($i = 0; $i < $events; $i++) {
            try {
                $this->fireEvent($topic);
                $this->producer->poll(0);
                if ($n === 10) {
                    $this->producer->flush(100);
                    $n = 1;
                }
            } catch (\Exception $e) {
                $output->writeln('Error occurred when firing an event: ' . $e->getMessage());
            }
            $n++;
        }
    }

    private function createTopic(): Topic
    {
        $topicConfig = new TopicConf();
        $topicConfig->set('message.timeout.ms', 1000);

        return $this->producer->newTopic('test', $topicConfig);
    }

    private function fireEvent(Topic $topic): void
    {
        $event = $this->generateEvent();
        $topic->produce($event->userId - 1, 0, json_encode($event), 'userEvent');
    }

    private function generateEvent(): UserEvent
    {
        return new UserEvent(rand(1, 1000), new DateTimeImmutable());
    }
}