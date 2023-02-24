<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Session;

use Spryker\Client\Kernel\AbstractClient;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionClient extends AbstractClient implements SessionClientInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null
     */
    protected static $container;

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $container
     *
     * @return void
     */
    public function setContainer(SessionInterface $container)
    {
        static::$container = $container;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface|null
     */
    protected function getContainer()
    {
        return static::$container;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name The attribute name
     * @param mixed $default The default value if not found.
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->getContainer()->get($name, $default);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function set($name, $value)
    {
        $this->getContainer()->set($name, $value);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove($name)
    {
        return $this->getContainer()->remove($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return bool True if session started.
     */
    public function start()
    {
        return $this->getContainer()->start();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string The session ID.
     */
    public function getId()
    {
        return $this->getContainer()->getId();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->getContainer()->setId($id);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return mixed The session name.
     */
    public function getName()
    {
        return $this->getContainer()->getName();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->getContainer()->setName($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int|null $lifetime Sets the cookie lifetime for the session cookie. A null value
     *   will leave the system settings unchanged, 0 sets the cookie
     *   to expire with browser session. Time is in seconds, and is
     *   not a Unix timestamp.
     *
     * @return bool True if session invalidated, false if error.
     */
    public function invalidate($lifetime = null)
    {
        return $this->getContainer()->invalidate($lifetime);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param bool $destroy Whether to delete the old session or leave it to garbage collection.
     * @param int|null $lifetime Sets the cookie lifetime for the session cookie. A null value
     *   will leave the system settings unchanged, 0 sets the cookie
     *   to expire with browser session. Time is in seconds, and is
     *   not a Unix timestamp.
     *
     * @return bool True if session migrated, false if error.
     */
    public function migrate($destroy = false, $lifetime = null)
    {
        return $this->getContainer()->migrate($destroy, $lifetime);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return mixed
     */
    public function save()
    {
        return $this->getContainer()->save();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name The attribute name
     *
     * @return bool true if the attribute is defined, false otherwise
     */
    public function has($name)
    {
        if (!$this->getContainer()) {
            return false;
        }

        return $this->getContainer()->has($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array Attributes
     */
    public function all()
    {
        return $this->getContainer()->all();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $attributes Attributes
     *
     * @return void
     */
    public function replace(array $attributes)
    {
        $this->getContainer()->replace($attributes);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return mixed
     */
    public function clear()
    {
        return $this->getContainer()->clear();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return bool
     */
    public function isStarted()
    {
        if (!$this->getContainer()) {
            return false;
        }

        return $this->getContainer()->isStarted();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionBagInterface $bag
     *
     * @return void
     */
    public function registerBag(SessionBagInterface $bag)
    {
        $this->getContainer()->registerBag($bag);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     *
     * @return \Symfony\Component\HttpFoundation\Session\SessionBagInterface
     */
    public function getBag($name)
    {
        return $this->getContainer()->getBag($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
     */
    public function getMetadataBag()
    {
        return $this->getContainer()->getMetadataBag();
    }
}
