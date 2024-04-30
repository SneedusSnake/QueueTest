<?php

namespace App;

use Iterator;

interface UserEventsGenerator {
    public function generate(): Iterator;
}