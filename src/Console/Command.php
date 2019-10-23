<?php

namespace Yoga\Console;

abstract class Command extends \Symfony\Component\Console\Command\Command {

    abstract protected function handle();

    final protected function configure() {
        $reflection = $this->getReflection();
        $this
            ->setName($reflection->getName())
            ->setDescription($reflection->getDescription());
        foreach ($reflection->getParameters() as $parameter) {
            if ($parameter instanceof \Yoga\Console\Command\Reflection\Parameter\Flag) {
                $this->addOption(
                    $parameter->getName(),
                    '',
                    \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                    $parameter->getDescription()
                );
            } else {
                /** @var \Yoga\Console\Command\Reflection\Parameter\Argument $parameter */
                if ($parameter->getIsOption()) {
                    if ($parameter->getIsRequired()) {
                        $mode = \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED;
                    } else {
                        $mode = \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL;
                    }
                    if ($parameter->getIsArray()) {
                        $mode |= \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY;
                    }
                    $this->addOption(
                       $parameter->getName(),
                       null,
                       $mode,
                       $parameter->getDescription()
                    );
                } else {
                    if ($parameter->getIsRequired()) {
                        $mode = \Symfony\Component\Console\Input\InputArgument::REQUIRED;
                    } else {
                        $mode = \Symfony\Component\Console\Input\InputArgument::OPTIONAL;
                    }
                    if ($parameter->getIsArray()) {
                        $mode |= \Symfony\Component\Console\Input\InputArgument::IS_ARRAY;
                    }
                    $this->addArgument(
                        $parameter->getName(),
                        $mode,
                        $parameter->getDescription()
                    );
                }
            }
        }
    }

    /**
     * @return \Yoga\Console\Command\Reflection
     */
    protected function getReflection() {
        $class = get_called_class();
        return \Yoga\ComputeOnce::service()->handle(function () use ($class) {
            return \Yoga\Console\Command\Reflection\Reader::service()
                ->getReflection($class);
        });
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->input = $input;
        $this->output = $output;
        $reflection = $this->getReflection();
        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            if ($parameter instanceof \Yoga\Console\Command\Reflection\Parameter\Flag) {
                $value = $input->getOption($name);
            } else {
                /** @var \Yoga\Console\Command\Reflection\Parameter\Argument $parameter */
                if ($parameter->getIsOption()) {
                    $value = $input->getOption($name);
                    if ($parameter->getIsRequired() && null === $value) {
                        throw new \Exception('Value expected for ' . $parameter->getName());
                    }
                } else {
                    $value = $input->getArgument($name);
                }
            }
            if (null !== $value) {
                /** @var phpStormHack $this <-- phpStorm complains at $this->$name without this */
                $this->$name = $value;
            }
        }
        try {
            $result = $this->handle();
        } catch (\Exception $e) {
            \Yoga\Logger::service()->debug($e->getMessage() . "\n" . $e->getTraceAsString());
            return 1;
        }
        return $result;
    }

    protected function writeln($s) {
        $this->getOutput()->writeln($s);
    }

    /**
     * @var int
     */
    private $progressBarTotalSteps;

    /**
     * @var int
     */
    private $progressBarCurrentStep;

    /**
     * @param int $totalSteps
     * @return \Symfony\Component\Console\Helper\ProgressHelper
     */
    protected function progressBarStart($totalSteps) {
        $progressBar = $this->getProgressBar();
        $progressBar->start($this->getOutput(), $totalSteps);
        $progressBar->display();
        $this->progressBarTotalSteps = $totalSteps;
        $this->progressBarCurrentStep = 0;
    }

    protected function progressBarAdvance($increment = 1) {
        $this->getProgressBar()->advance($increment);
        $this->progressBarCurrentStep += $increment;
        if ($this->progressBarCurrentStep >= $this->progressBarTotalSteps) {
            $this->getProgressBar()->finish();
        }
    }

    /**
     * @return \Symfony\Component\Console\Helper\ProgressHelper
     */
    private function getProgressBar() {
        return \Yoga\ComputeOnce::service()->handle(function () {
            return $this->getHelperSet()->get('progress');
        });
    }

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    protected function getInput() {
        return $this->input;
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    protected function getOutput() {
        return $this->output;
    }

}