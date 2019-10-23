<?php

$filename = __DIR__ . '/vendor/autoload.php';
if (file_exists($filename)) {
    require_once($filename);
} else {
    require_once(__DIR__ . '/../../../vendor/autoload.php');
}
\Yoga\Application::service()->bootstrap(true);
