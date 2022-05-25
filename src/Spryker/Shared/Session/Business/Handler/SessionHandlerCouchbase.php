<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Session\Business\Handler;

use Couchbase;
use SessionHandlerInterface;
use Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface;

class SessionHandlerCouchbase implements SessionHandlerInterface
{
    /**
     * @var string
     */
    public const METRIC_SESSION_DELETE_TIME = 'Couchbase/Session_delete_time';

    /**
     * @var string
     */
    public const METRIC_SESSION_WRITE_TIME = 'Couchbase/Session_write_time';

    /**
     * @var string
     */
    public const METRIC_SESSION_READ_TIME = 'Couchbase/Session_read_time';

    /**
     * @var \Couchbase
     */
    protected $connection;

    /**
     * e.g. ['127.0.0.1:8091']
     *
     * @var array
     */
    protected $hosts = [];

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $bucketName;

    /**
     * @var bool
     */
    protected $persistent;

    /**
     * @var string
     */
    protected $keyPrefix = 'session:';

    /**
     * @var int
     */
    protected $lifetime;

    /**
     * @var \Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface
     */
    protected $monitoringService;

    /**
     * @param \Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface $monitoringService
     * @param array $hosts
     * @param string|null $user
     * @param string|null $password
     * @param string $bucketName
     * @param bool $persistent
     * @param int $lifetime
     */
    public function __construct(
        SessionToMonitoringServiceInterface $monitoringService,
        $hosts = ['127.0.0.1:8091'],
        $user = null,
        $password = null,
        $bucketName = 'default',
        $persistent = true,
        $lifetime = 600
    ) {
        $this->monitoringService = $monitoringService;
        $this->hosts = $hosts;
        $this->user = $user;
        $this->password = $password;
        $this->bucketName = $bucketName;
        $this->persistent = $persistent;
        $this->lifetime = $lifetime;
    }

    /**
     * @param string $savePath
     * @param string $sessionName
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        $this->connection = new Couchbase(
            $this->hosts,
            $this->user,
            $this->password,
            $this->bucketName,
            $this->persistent,
        );

        return $this->connection ? true : false;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        unset($this->connection);

        return true;
    }

    /**
     * @param string $sessionId
     *
     * @return string|null
     */
    public function read($sessionId): ?string
    {
        $key = $this->keyPrefix . $sessionId;

        $startTime = microtime(true);
        $result = $this->connection->getAndTouch($key, $this->lifetime);
        $this->monitoringService->addCustomParameter(static::METRIC_SESSION_READ_TIME, microtime(true) - $startTime);

        return $result ? json_decode($result, true) : '';
    }

    /**
     * @param string $sessionId
     * @param string $sessionData
     *
     * @return bool
     */
    public function write($sessionId, $sessionData): bool
    {
        $key = $this->keyPrefix . $sessionId;

        if (strlen($sessionData) < 1) {
            return false;
        }

        $startTime = microtime(true);
        $result = $this->connection->set($key, json_encode($sessionData), $this->lifetime);
        $this->monitoringService->addCustomParameter(static::METRIC_SESSION_WRITE_TIME, microtime(true) - $startTime);

        return $result ? true : false;
    }

    /**
     * @param string|int $sessionId
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $key = $this->keyPrefix . $sessionId;

        $startTime = microtime(true);
        $this->connection->delete($key);
        $this->monitoringService->addCustomParameter(static::METRIC_SESSION_DELETE_TIME, microtime(true) - $startTime);

        return true;
    }

    /**
     * @param int $maxLifetime
     *
     * @return bool
     */
    public function gc($maxLifetime): bool
    {
        return true;
    }
}
