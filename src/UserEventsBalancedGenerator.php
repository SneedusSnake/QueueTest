<?php

namespace App;

use Iterator;
use App\UserEvent;
use DateTimeImmutable;

class UserEventsBalancedGenerator implements UserEventsGenerator {
    private int $eventsPerUser = 1;
    private int $currentUserId = 1;
    private int $count = 0;

    public function __construct(private readonly int $usersCount, private readonly int $eventsCount)
    {
        $this->eventsPerUser = max(1, floor($eventsCount/$this->usersCount));
    }

    public function generate(): Iterator
    {
        while ($this->count < $this->eventsCount) {
            yield $this->generateEvent();
            $this->count++;

            if ($this->count%$this->eventsPerUser === 0) {
                $this->currentUserId = min($this->currentUserId + 1, $this->usersCount);
            }
        }
    }

    private function generateEvent(): UserEvent
    {
        return new UserEvent($this->currentUserId, new DateTimeImmutable());
    }
}