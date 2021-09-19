<?php

namespace Larabra\LaravelMediaLibraryInput\Routing;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;

class ResourceRegistrarProxy extends BaseResourceRegistrar
{
    public function getResourceAction($resource, $controller, $method, $options)
    {
        return parent::getResourceAction($resource, $controller, $method, $options);
    }

    public function getResourcePrefix($name)
    {
        return parent::getResourcePrefix($name);
    }
}
