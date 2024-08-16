<?php

namespace App\Http\Services;

use App\Exception\InvalidStateException;
use Closure;

final class LaravelSabre
{
    /**
     * The collection of node to use with the sabre server.
     *
     * @var array|\Sabre\DAV\Tree|\Sabre\DAV\INode|\Closure|null
     */
    private static $nodes;

    /**
     * The collection of plugins to register to the sabre server.
     *
     * @var array|\Closure|null
     */
    private static $plugins = [];

    /**
     * The callback used to authenticate a request.
     *
     * @var null|\Closure
     */
    private static $auth;

    /**
     * Get the nodes property.
     *
     * @return array|\Sabre\DAV\Tree|\Sabre\DAV\INode|\Closure|null
     */
    public static function getNodesProperty()
    {
        return self::$nodes;
    }

    /**
     * Set the nodes property.
     *
     * @param  array|\Sabre\DAV\Tree|\Sabre\DAV\INode|\Closure|null  $nodes
     */
    public static function setNodesProperty($nodes): void
    {
        self::$nodes = $nodes;
    }

    /**
     * Get the auth property.
     *
     * @return null|\Closure
     */
    public static function getAuthProperty()
    {
        return self::$auth;
    }

    /**
     * Set the auth property.
     *
     * @param  null|\Closure  $auth
     */
    public static function setAuthProperty($auth): void
    {
        self::$auth = $auth;
    }

    /**
     * Get the plugins property.
     *
     * @return array|\Closure|null
     */
    public static function getPluginsProperty()
    {
        return self::$plugins;
    }

    /**
     * Set the plugins property.
     *
     * @param  array|\Closure|null  $plugins
     */
    public static function setPluginsProperty($plugins): void
    {
        self::$plugins = $plugins;
    }

    /**
     * Returns list of nodes to create the sabre collection.
     *
     * @return array|\Sabre\DAV\Tree|\Sabre\DAV\INode|null
     */
    public static function getNodes()
    {
        if (self::getNodesProperty() instanceof Closure) {
            return (self::getNodesProperty())();
        }

        return self::getNodesProperty();
    }

    /**
     * Sets the list of nodes used to create the sabre collection.
     *
     * @param  array|\Sabre\DAV\Tree|\Sabre\DAV\INode|\Closure  $nodes
     * @return static
     */
    public static function nodes($nodes)
    {
        if ($nodes instanceof Closure ||
            $nodes instanceof \Sabre\DAV\Tree ||
            $nodes instanceof \Sabre\DAV\INode) {
            self::setNodesProperty($nodes);
        } else {
            self::setNodesProperty(collect($nodes)->toArray());
        }

        return new self;
    }

    /**
     * Return the list of plugins to add to the sabre server.
     *
     * @return array|null
     */
    public static function getPlugins()
    {
        if (self::getPluginsProperty() instanceof Closure) {
            return (self::getPluginsProperty())();
        }

        return self::getPluginsProperty();
    }

    /**
     * Sets the list of plugins to add to the sabre server.
     *
     * @param  mixed  $plugins
     * @return static
     */
    public static function plugins($plugins)
    {
        if ($plugins instanceof Closure) {
            self::setPluginsProperty($plugins);
        } else {
            self::setPluginsProperty(collect($plugins)->toArray());
        }

        return new self;
    }

    /**
     * Add a plugin to the sabre server.
     *
     * @param  mixed  $plugin
     * @return static
     *
     * @throws InvalidStateException
     */
    public static function plugin($plugin)
    {
        if (self::getPluginsProperty() !== null) {
            self::setPluginsProperty([]);
        }

        if (is_array(self::getPluginsProperty())) {
            self::getPluginsProperty()[] = $plugin;
        } else {
            throw new InvalidStateException('plugins is not an array, impossible to use plugin() function.');
        }

        return new self;
    }

    /**
     * Register the LaravelSabre authentication callback.
     *
     * @return static
     */
    public static function auth(Closure $callback)
    {
        self::setAuthProperty($callback);

        return new self;
    }

    /**
     * Return if the given request can open this dav resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function check($request)
    {
        return (self::getAuthProperty() ?? function (): bool {
            return true;
        })($request);
    }

    /**
     * Clear all datas.
     *
     * @return void
     */
    public static function clear()
    {
        self::setNodesProperty([]);
        self::setPluginsProperty([]);
        self::setAuthProperty(null);
    }
}
