<?php

namespace Yoga\Console\Command\ApiDocumentation;

/**
 * @method static ApiGrouper service()
 */
class ApiGrouper extends \Yoga\Service {

    /**
     * @param \Yoga\Api\Reflection[] $apis
     * @return \Yoga\Console\Command\ApiDocumentation\ApiGroup[]
     */
    public function groupApis(array $apis) {
        $groups = [];
        foreach ($apis as $api) {
            $group = $this->findOrCreateGroup($groups, $api);
            $this->addApiToGroup($group, $api);
        }
        return $groups;
    }

    /**
     * @param \Yoga\Console\Command\ApiDocumentation\ApiGroup[] $groups
     * @param \Yoga\Api\Reflection $api
     * @return \Yoga\Console\Command\ApiDocumentation\ApiGroup
     */
    private function findOrCreateGroup(array &$groups, \Yoga\Api\Reflection $api) {
        $class = $api->getClass();
        $namespace = substr($class, 0, strrpos($class, '\\'));
        if (substr($namespace, 0, 4) == 'Api\\') {
            $namespace = substr($namespace, 4);
        }
        $group = $this->findGroup($groups, $namespace);
        if (!$group) {
            $group = (new ApiGroup)->setNamespace($namespace);
            $groups[] = $group;
        }
        return $group;
    }

    /**
     * @param \Yoga\Console\Command\ApiDocumentation\ApiGroup[] $groups
     * @param string $namespace
     * @return \Yoga\Console\Command\ApiDocumentation\ApiGroup
     */
    private function findGroup(array $groups, $namespace) {
        foreach ($groups as $group) {
            if ($group->getNamespace() == $namespace) {
                return $group;
            }
        }
        return null;
    }

    private function addApiToGroup(
        \Yoga\Console\Command\ApiDocumentation\ApiGroup $group,
        \Yoga\Api\Reflection $api
    ) {
        $route = $this->findOrCreateRoute($group, $api);
        $route->addApi($api);
    }

    /**
     * @param ApiGroup $group
     * @param \Yoga\Api\Reflection $api
     * @return ApiRoute
     */
    private function findOrCreateRoute(
        \Yoga\Console\Command\ApiDocumentation\ApiGroup $group,
        \Yoga\Api\Reflection $api
    ) {
        $urlPattern = $api->getUrlPattern();
        $route = $this->findRoute($group->getRoutes(), $urlPattern);
        if (!$route) {
            $route = (new ApiRoute)->setUrlPattern($urlPattern);
            $group->addRoute($route);
        }
        return $route;
    }

    /**
     * @param ApiRoute[] $routes
     * @param string $urlPattern
     * @return \Yoga\Console\Command\ApiDocumentation\ApiRoute
     */
    private function findRoute(array $routes, $urlPattern) {
        foreach ($routes as $route) {
            if ($route->getUrlPattern() == $urlPattern) {
                return $route;
            }
        }
        return null;
    }

}