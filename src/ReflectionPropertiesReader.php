<?php

namespace Yoga;

/**
 * @method static ReflectionPropertiesReader service()
 */
class ReflectionPropertiesReader extends Service {

    /**
     * Returns all properties of the object including all parents, making the
     * properties accessible.
     * @param object|string $objectOrClass
     * @return \ReflectionProperty[]
     */
    public function getProperties($objectOrClass) {
        $result = [];
        $reflectionClass = new \ReflectionClass($objectOrClass);
        while ($reflectionClass) {
            foreach ($reflectionClass->getProperties() as $property) {
                $result[$property->getName()] = $property;
                $property->setAccessible(true);
            }
            $reflectionClass = $reflectionClass->getParentClass();
        }
        return $result;
    }

}