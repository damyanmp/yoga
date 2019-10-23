<?php

namespace Yoga;

/**
 * @method static Traits service()
 */
class Traits extends \Yoga\Service {

    /**
     * @param string|object $classOrObject
     * @param string $traitName
     * @return boolean
     */
    public function isTraitUsed($classOrObject, $traitName) {
        $class = is_object($classOrObject) ? get_class($classOrObject) : $classOrObject;
        do {
            if (in_array($traitName, class_uses($class))) {
                return true;
            }
        } while ($class = get_parent_class($class));
        return false;
    }

}