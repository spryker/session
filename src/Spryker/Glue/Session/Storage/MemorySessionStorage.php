<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Session\Storage;

use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * This class is used as workaround for Clients which depend on session, this will provide in memory storage that means after request complected it's discarded.
 * When using SessionClient within Glue application context, it will use this storage.
 */
class MemorySessionStorage implements SessionStorageInterface
{
    /**
     * @var string
     */
    protected const SESSION_ID = 'MOCKSESSID';

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $started = false;

    /**
     * @var bool
     */
    protected $closed = false;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
     */
    protected static $metadataBag;

    /**
     * @var array<\Symfony\Component\HttpFoundation\Session\SessionBagInterface>
     */
    protected static $bags;

    /**
     * @param string $name
     * @param \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag|null $metaBag MetadataBag instance
     */
    public function __construct(string $name = self::SESSION_ID, ?MetadataBag $metaBag = null)
    {
        $this->name = $name;
        $this->setMetadataBag($metaBag);
    }

    /**
     * @param array $array
     *
     * @return void
     */
    public function setSessionData(array $array): void
    {
        $this->data = $array;
    }

    /**
     * @return bool
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }

        if (!$this->id) {
            $this->id = $this->generateId();
        }

        $this->loadSession();

        return true;
    }

    /**
     * @param bool $destroy
     * @param int|null $lifetime
     *
     * @return bool
     */
    public function regenerate(bool $destroy = false, ?int $lifetime = null): bool
    {
        if (!$this->started) {
            $this->start();
        }

        static::$metadataBag->stampNew($lifetime);
        $this->id = $this->generateId();

        return true;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @throws \LogicException
     *
     * @return void
     */
    public function setId($id): void
    {
        if ($this->started) {
            throw new LogicException('Cannot set session ID after the session has started.');
        }

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @throws \RuntimeException
     *
     * @return void
     */
    public function save(): void
    {
        if (!$this->started || $this->closed) {
            throw new RuntimeException('Trying to save a session that was not started yet or was already closed');
        }

        $this->closed = false;
        $this->started = false;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        foreach (static::$bags as $bag) {
            $bag->clear();
        }

        // clear out the session
        $this->data = [];

        // reconnect the bags to the session
        $this->loadSession();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionBagInterface $bag
     *
     * @return void
     */
    public function registerBag(SessionBagInterface $bag): void
    {
        static::$bags[$bag->getName()] = $bag;
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\Session\SessionBagInterface
     */
    public function getBag(string $name): SessionBagInterface
    {
        if (!isset(static::$bags[$name])) {
            throw new InvalidArgumentException(sprintf('The SessionBagInterface %s is not registered.', $name));
        }

        if (!$this->started) {
            $this->start();
        }

        return static::$bags[$name];
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag|null $bag
     *
     * @return void
     */
    public function setMetadataBag(?MetadataBag $bag = null): void
    {
        if ($bag === null) {
            $bag = new MetadataBag();
        }

        static::$metadataBag = $bag;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
     */
    public function getMetadataBag(): MetadataBag
    {
        return static::$metadataBag;
    }

    /**
     * @return string
     */
    protected function generateId(): string
    {
        return hash('sha256', uniqid('ss_mock_', true));
    }

    /**
     * @return void
     */
    protected function loadSession(): void
    {
        $bags = array_merge(static::$bags, [static::$metadataBag]);

        foreach ($bags as $bag) {
            $key = $bag->getStorageKey();
            $this->data[$key] = $this->data[$key] ?? [];
            $bag->initialize($this->data[$key]);
        }

        $this->started = true;
        $this->closed = false;
    }
}
