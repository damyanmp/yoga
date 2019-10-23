<?php

namespace Yoga;

/**
 * @method static Task service()
 */
abstract class Task extends Service {

    abstract public function handle();

}