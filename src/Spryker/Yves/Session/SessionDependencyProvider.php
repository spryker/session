<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Session;

use Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceBridge;
use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;

/**
 * @method \Spryker\Yves\Session\SessionConfig getConfig()
 */
class SessionDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const MONITORING_SERVICE = 'monitoring service';

    /**
     * @var string
     */
    public const PLUGINS_SESSION_HANDLER = 'PLUGINS_SESSION_HANDLER';

    /**
     * @var string
     */
    public const CLIENT_SESSION = 'CLIENT_SESSION';

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    public function provideDependencies(Container $container)
    {
        $container = $this->addMonitoringService($container);
        $container = $this->addSessionHandlerPlugins($container);
        $container = $this->addSessionClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addMonitoringService(Container $container)
    {
        $container->set(static::MONITORING_SERVICE, function () use ($container) {
            $sessionToMonitoringServiceBridge = new SessionToMonitoringServiceBridge(
                $container->getLocator()->monitoring()->service(),
            );

            return $sessionToMonitoringServiceBridge;
        });

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addSessionHandlerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_SESSION_HANDLER, function (Container $container) {
            return $this->getSessionHandlerPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Shared\SessionExtension\Dependency\Plugin\SessionHandlerProviderPluginInterface>
     */
    protected function getSessionHandlerPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addSessionClient(Container $container): Container
    {
        $container->set(static::CLIENT_SESSION, function (Container $container) {
            return $container->getLocator()->session()->client();
        });

        return $container;
    }
}
