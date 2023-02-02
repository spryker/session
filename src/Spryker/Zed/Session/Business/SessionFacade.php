<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Session\Business;

use Generated\Shared\Transfer\HealthCheckServiceResponseTransfer;
use Generated\Shared\Transfer\MessageAttributesTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\Session\Business\SessionBusinessFactory getFactory()
 */
class SessionFacade extends AbstractFacade implements SessionFacadeInterface
{
    /**
     * @api
     *
     * @inheritDoc
     *
     * @param string $sessionId
     *
     * @return void
     */
    public function removeYvesSessionLockFor($sessionId)
    {
        $this
            ->getFactory()
            ->createYvesSessionLockReleaser()
            ->release($sessionId);
    }

    /**
     * @api
     *
     * @inheritDoc
     *
     * @param string $sessionId
     *
     * @return void
     */
    public function removeZedSessionLockFor($sessionId)
    {
        $this
            ->getFactory()
            ->createZedSessionLockReleaser()
            ->release($sessionId);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\HealthCheckServiceResponseTransfer
     */
    public function executeSessionHealthCheck(): HealthCheckServiceResponseTransfer
    {
        return $this->getFactory()->createSessionHealthChecker()->executeHealthCheck();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\MessageAttributesTransfer $messageAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\MessageAttributesTransfer
     */
    public function expandMessageAttributesWithSessionTrackingId(
        MessageAttributesTransfer $messageAttributesTransfer
    ): MessageAttributesTransfer {
        return $this->getFactory()->createMessageAttributesExpander()->expand($messageAttributesTransfer);
    }
}
