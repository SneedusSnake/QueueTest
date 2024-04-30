<?php

namespace App;

use DateTimeImmutable;
use JsonSerializable;

class UserEvent implements JsonSerializable {

    public function __construct(public readonly int $userId, public readonly DateTimeImmutable $occurredOn)
    {
        
    }

    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->userId,
            'occurred_on' => $this->occurredOn->format('d.m.Y H:i:s'),
        ];
    }
}