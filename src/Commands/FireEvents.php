<?php

namespace App\Commands;

use App\UserEventsBalancedGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RdKafka\Producer;
use RdKafka\TopicConf;
use RdKafka\Topic;
use Symfony\Component\Console\Input\InputArgument;

class FireEvents extends Command {

    private const USERS_COUNT = 1000;

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
        $eventGenerator = new UserEventsBalancedGenerator(self::USERS_COUNT, $events);
        $bufferedMessages = 1;

        foreach ($eventGenerator->generate() as $event) {
            try {
                $output->writeln("Firing event for userId {$event->userId}");
                $topic->produce($event->userId - 1, 0, json_encode($event), 'userEvent');
                $this->producer->poll(0);
                if ($bufferedMessages === 10) {
                    $this->producer->flush(100);
                    $bufferedMessages = 1;
                }
            } catch (\Exception $e) {
                $output->writeln('Error occurred when firing an event: ' . $e->getMessage());
            }

            $bufferedMessages++;
        }

        $output->writeln('Finished firing events');
    }

    private function createTopic(): Topic
    {
        $topicConfig = new TopicConf();
        $topicConfig->set('message.timeout.ms', 1000);

        return $this->producer->newTopic('test', $topicConfig);
    }
}