<?php namespace Flatline\CfDdns\Config;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Exception\FileLoaderLoadException;

class Config implements ConfigInterface, \ArrayAccess
{
    protected $config_file;

    protected $locator;

    protected $resolver;

    protected $loader;

    protected $items;


    public function __construct(
        $config_file,
        FileLocatorInterface $locator
      , LoaderResolverInterface $resolver
    )
    {
        $this->config_file = $config_file;
        $this->locator = $locator;
        $this->resolver = $resolver;
    }

    public function load()
    {
        // Locate the config file wherever it may be
        $resource = $this->locator->locate($this->config_file, getcwd(), true);

        // Add the Yml loader to the resolver
        $this->resolver->addLoader(new Loader\YmlLoader($this->locator));

        // Try to resolve the locator resource
        if (false === $loader = $this->resolver->resolve($resource)) {
            throw new FileLoaderLoadException($resource);
        }

        // Load the found config file resource
        $this->items = $loader->load($resource);

        return $this;
    }

    /**
     * Get the config array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        $default = microtime(true);

        return $this->get($key, $default) !== $default;
    }

    /**
     * Get a config item using "dot" notation.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_null($key)) return $this->items;

        return array_get($this->items, $key, $default);
    }

    /**
     * Set a config item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return Config
     */
    public function set($key, $value)
    {
        array_set($this->items, $key, $value);

        return $this;
    }

    /**
     * Unset a config item using "dot" notation.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return Config
     */
    public function remove($key)
    {
        array_unset($this->items, $key);

        return $this;
    }

    /* ArrayAccess */

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->remove($key);
    }
}