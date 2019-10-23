<?php

namespace Yoga\Enum;

/**
 * @method static HttpResponseCode wrap()
 */
class HttpResponseCode extends \Yoga\Enum {

    const OK = 200;
    const OBJECT_CREATED = 201;

    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const ACCESS_DENIED = 403;
    const NOT_FOUND = 404;
    const INTERNAL_SERVER_ERROR = 500;

}