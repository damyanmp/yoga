<?php

namespace Yoga\Enum;

/**
 * @method static AwsInstanceStatus wrap()
 */
class AwsInstanceStatus extends \Yoga\Enum {

    const PENDING = 0;
    const RUNNING = 16;
    const STOPPING = 64;
    const STOPPED = 80;

    const UNKNOWN = 777;

}