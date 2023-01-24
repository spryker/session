<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Session\Plugin\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @deprecated Use {@link \Spryker\Yves\Session\Plugin\Application\SessionApplicationPlugin} instead.
 * @deprecated Use {@link \Spryker\Yves\Session\Plugin\EventDispatcher\SessionEventDispatcherPlugin} instead.
 *
 * @method \Spryker\Yves\Session\SessionFactory getFactory()
 * @method \Spryker\Client\Session\SessionClientInterface getClient()
 */
class SessionServiceProvider extends AbstractPlugin implements ServiceProviderInterface
{
    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function register(Application $app)
    {
        $this->setSessionStorageOptions($app);
        $this->setSessionStorageHandler($app);
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function boot(Application $app)
    {
        $session = $this->getSession($app);

        $this->getClient()->setContainer($session);

        /** @var \Spryker\Shared\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = $app['dispatcher'];
        $dispatcher->addListener(KernelEvents::RESPONSE, [
            $this,
            'extendCookieLifetime',
        ], -128);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     *
     * @return void
     */
    public function extendCookieLifetime(ResponseEvent $event): void
    {
        if ($this->isMainRequest($event) === false || !$event->getRequest()->hasSession()) {
            return;
        }

        $session = $event->getRequest()->getSession();

        if ($session->isStarted() === false) {
            return;
        }

        $params = session_get_cookie_params();

        $event->getResponse()->headers->setCookie(new Cookie(
            $session->getName(),
            $session->getId(),
            $params['lifetime'] === 0 ? 0 : time() + $params['lifetime'],
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly'],
        ));
    }

    /**
     * @param \Silex\Application $application
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession(Application $application)
    {
        return $application['session'];
    }

    /**
     * @param \Silex\Application $application
     *
     * @return void
     */
    protected function setSessionStorageOptions(Application $application)
    {
        $application['session.storage.options'] = $this->getFactory()->createSessionStorage()->getOptions();
    }

    /**
     * @param \Silex\Application $application
     *
     * @return void
     */
    protected function setSessionStorageHandler(Application $application)
    {
        $application['session.storage.handler'] = function () {
            return $this->getFactory()->createSessionStorage()->getAndRegisterHandler();
        };
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\KernelEvent $event
     *
     * @return bool
     */
    protected function isMainRequest(KernelEvent $event): bool
    {
        if (method_exists($event, 'isMasterRequest')) {
            return $event->isMasterRequest();
        }

        return $event->isMainRequest();
    }
}
