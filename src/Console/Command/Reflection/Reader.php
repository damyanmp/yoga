<?php

namespace Yoga\Console\Command\Reflection;

/**
 * @method static Reader service()
 */
class Reader extends \Yoga\Service {

    /**
     * @param string $class
     * @throws \Exception
     * @return \Yoga\Console\Command\Reflection
     */
    public function getReflection($class) {
        $reflection = \Yoga\Reflection\Reader::service()
            ->getReflection($class);
        $result = new \Yoga\Console\Command\Reflection;
        foreach ($reflection->getAnnotations() as $annotation) {
            if ('Command' == $annotation->getName()) {
                $arguments = $annotation->getArguments();
                if (!isset($arguments[0])) {
                    throw new \Exception('Unable to read name in Command annotation for `' . $class . '`');
                }
                $result->setName($arguments[0]);
                if (isset($arguments['description'])) {
                    $result->setDescription($arguments['description']);
                }
                break;
            }
        }
        if (!$result->getName()) {
            throw new \Exception('Unable to read Command annotation for `' . $class . '`');
        }
        $parameters = [];
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes();
            $arraysService = \Yoga\Arrays::service();
            if ($arraysService->safeGet($attributes, 'isFlag')) {
                $commandParameter = new \Yoga\Console\Command\Reflection\Parameter\Flag;
            } else {
                $commandParameter = (new \Yoga\Console\Command\Reflection\Parameter\Argument)
                    ->setIsOption($arraysService->safeGet($attributes, 'isOption'))
                    ->setIsRequired($arraysService->safeGet($attributes, 'isRequired'))
                    ->setIsArray($arraysService->safeGet($attributes, 'isArray'));
            }
            $commandParameter
                ->setName($property->getName())
                ->setDescription($arraysService->safeGet($attributes, 'description'));
            $parameters[] = $commandParameter;
        }
        $result->setParameters($parameters);
        return $result;
    }

}