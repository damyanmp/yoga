<?php

namespace Yoga;

abstract class Api {

    abstract public function handle();

    static public function convertResponseToJson($apiResponse) {
        return json_encode(Pickler::service()->pickle($apiResponse, false));
    }

}