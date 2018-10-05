<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Session\Communication;

use Spryker\Shared\Session\Business\Model\SessionFactory;
use Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface;

class SessionHandlerFactory extends SessionFactory
{
    /**
     * @var int
     */
    protected $sessionLifeTime;

    /**
     * @var \Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface
     */
    protected $monitoringService;

    /**
     * @param int $sessionLifeTime
     * @param \Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface $monitoringService
     */
    public function __construct($sessionLifeTime, SessionToMonitoringServiceInterface $monitoringService)
    {
        $this->sessionLifeTime = $sessionLifeTime;
        $this->monitoringService = $monitoringService;
    }

    /**
     * @return int
     */
    protected function getSessionLifetime()
    {
        return $this->sessionLifeTime;
    }
}
