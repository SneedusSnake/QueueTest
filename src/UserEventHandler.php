<?php

namespace App;

class UserEventHandler {
    public function handle(UserEvent $event): void
    {
        sleep(1);
    }
}