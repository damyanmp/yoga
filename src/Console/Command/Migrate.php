<?php

namespace Yoga\Console\Command;

/**
 * @Command("migrate", description = "Apply new migrations")
 */
class Migrate extends \Yoga\Console\Command {

    /**
     * @var boolean(isOption = true)
     */
    public $isTestDatabase;

    public function handle() {
        if ($this->isTestDatabase) {
            $testsConfigurationFileName = \Yoga\Application::service()->getRootDirectory() . 'tests/Configuration.php';
            if (!file_exists($testsConfigurationFileName)) {
                return;
            }
            require_once $testsConfigurationFileName;
            \Yoga\Configuration::substitute('\Tests\Configuration');
        }
        $sql = \Yoga\Sql::service();
        $migrationReflections = $this->getMigrationReflections();
        foreach ($migrationReflections as $migrationReflection) {
            $class = $migrationReflection->getName();
            $query = 'SELECT 1
                FROM migrations
                WHERE version = ' . $sql->escapeString($class);
            if ($sql->select1value($query)) {
                continue;
            }
            $message = 'Applying ' . $class;
            if ($this->isTestDatabase) {
                $message .= ' (test database)';
            }
            $this->writeln($message);
            /** @var \Yoga\Migration $migration */
            $migration = $class::service();
            try {
                $migration
                    ->setIsTestDatabase($this->isTestDatabase)
                    ->up();
            } catch (\Exception $e) {
                $this->writeln('ERROR! ' . $e->getMessage());
                throw $e;
            }
            $sql->insert('
                INSERT INTO migrations
                SET version = ' . $sql->escapeString($class) . '
                , applied_dt = NOW()
            ');
        }
    }

    /**
     * @return \ReflectionClass[]
     */
    private function getMigrationReflections() {
        $migrationDirectory = \Yoga\Application::service()->getRootDirectory() . 'server/Migration/';
        $reflections = \Yoga\DirectoryReader::service()
            ->getReflections($migrationDirectory, 'Migration');
        $result = [];
        foreach ($reflections as $reflection) {
            $result[] = $reflection;
        }
        usort(
            $result,
            function ($a, $b) {
                /** @var \ReflectionClass $a */
                $aName = $a->getName();
                /** @var \ReflectionClass $b */
                $bName = $b->getName();
                if ($aName > $bName) {
                    return 1;
                }
                if ($aName < $bName) {
                    return -1;
                }
                return 0;
            }
        );
        return $result;
    }

}