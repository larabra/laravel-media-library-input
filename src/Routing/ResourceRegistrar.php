<?php

namespace Larabra\LaravelMediaLibraryInput\Routing;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;

class ResourceRegistrar extends BaseResourceRegistrar
{
    protected static $baseMedia = 'media';
    protected static $uriMedia = 'medias';

    protected $resourceDefaults = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'createMedia', 'reorderMedia', 'destroyMedia', 'downloadMedia'
    ];

    /**
     * Add the create method for a resourceful media route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    public function addResourceCreateMedia($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name) . '/{' . $base . '}/' . self::$uriMedia;

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'createMedia', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the reorder method for a resourceful media route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceReorderMedia($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name) . '/{' . $base . '}/' . self::$uriMedia;

        $action = $this->getResourceAction($name, $controller, 'reorderMedia', $options);

        return $this->router->put($uri, $action);
    }

    /**
     * Add the destroy method for a resourceful media route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceDestroyMedia($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name) . '/{' . $base . '}/' . self::$uriMedia . '/{' . self::$baseMedia . '}';

        $action = $this->getResourceAction($name, $controller, 'destroyMedia', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Add the download method for a resourceful media route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceDownloadMedia($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name) . '/{' . $base . '}/' . self::$uriMedia . '/{' . self::$baseMedia . '}/download';

        $action = $this->getResourceAction($name, $controller, 'downloadMedia', $options);

        return $this->router->get($uri, $action);
    }
}
