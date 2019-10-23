<?php

if (isset($_SERVER['argv'])) {
    require_once(__DIR__ . '/bootstrap.php');
    $application = new \Symfony\Component\Console\Application;
    $consoleDirectory = \Yoga\Application::service()->getRootDirectory() . 'server/Console/';
    $reflections = \Yoga\DirectoryReader::service()->getReflections($consoleDirectory, 'Console');
    foreach ($reflections as $reflection) {
        $class = $reflection->getName();
        $application->add(new $class);
    }
    $application->add(new \Yoga\Console\Command\Migrate);
    $application->add(new \Yoga\Console\Command\Compile);
    $application->add(new \Yoga\Console\Command\Cron);
    $application->add(new \Yoga\Console\Command\ApiDocumentation);
    $application->run();
}

