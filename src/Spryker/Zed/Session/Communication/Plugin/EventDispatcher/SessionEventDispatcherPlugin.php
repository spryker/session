<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Session\Communication\Plugin\EventDispatcher;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @method \Spryker\Zed\Session\SessionConfig getConfig()
 * @method \Spryker\Zed\Session\Communication\SessionCommunicationFactory getFactory()
 * @method \Spryker\Zed\Session\Business\SessionFacadeInterface getFacade()
 */
class SessionEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    /**
     * @var string
     */
    protected const SERVICE_SESSION = 'session';

    /**
     * @var string
     */
    protected const FLAG_SESSION_TEST = 'session.test';

    /**
     * @var int
     */
    protected const EVENT_PRIORITY_EARLY_KERNEL_REQUEST = 128;

    /**
     * @var int
     */
    protected const EVENT_PRIORITY_KERNEL_REQUEST = 192;

    protected const EVENT_PRIORITY_KERNEL_RESPONSE = -128;

    /**
     * {@inheritDoc}
     * - Adds early request event listener that adds session to request.
     * - Adds kernel request event listener that gets session id from cookie or migrate old one. Works only with `session.test` service enabled.
     * - Adds kernel response event listener that saves session and create a session cookie.
     *
     * @api
     *
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    public function extend(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher = $this->addEarlyKernelRequestEventListener($eventDispatcher, $container);
        $eventDispatcher = $this->addKernelResponseEventListener($eventDispatcher, $container);

        if ($this->isSessionTestEnabled($container)) {
            $eventDispatcher = $this->addKernelRequestEventListener($eventDispatcher, $container);
        }

        return $eventDispatcher;
    }

    /**
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    protected function addEarlyKernelRequestEventListener(
        EventDispatcherInterface $eventDispatcher,
        ContainerInterface $container
    ): EventDispatcherInterface {
        $eventDispatcher->addListener(KernelEvents::REQUEST, function (RequestEvent $event) use ($container) {
            $event->getRequest()->setSession($this->getSession($container));
        }, static::EVENT_PRIORITY_EARLY_KERNEL_REQUEST);

        return $eventDispatcher;
    }

    /**
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    protected function addKernelRequestEventListener(
        EventDispatcherInterface $eventDispatcher,
        ContainerInterface $container
    ): EventDispatcherInterface {
        $eventDispatcher->addListener(KernelEvents::REQUEST, function (RequestEvent $event) use ($container) {
            if (!$this->isMainRequest($event)) {
                return;
            }

            $cookies = $event->getRequest()->cookies;
            $session = $this->getSession($container);

            if ($cookies->has($session->getName())) {
                $session->setId((string)$cookies->get($session->getName()));
            } else {
                $session->migrate(false);
            }
        }, static::EVENT_PRIORITY_KERNEL_REQUEST);

        return $eventDispatcher;
    }

    /**
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    protected function addKernelResponseEventListener(
        EventDispatcherInterface $eventDispatcher,
        ContainerInterface $container
    ): EventDispatcherInterface {
        $eventDispatcher->addListener(KernelEvents::RESPONSE, function (ResponseEvent $event) {
            if (!$this->isMainRequest($event)) {
                return;
            }

            $session = $event->getRequest()->getSession();
            if ($session->isStarted()) {
                $session->save();

                $event->getResponse()->headers->setCookie($this->createSessionCookie($session->getName(), $session->getId(), session_get_cookie_params()));
            }
        }, static::EVENT_PRIORITY_KERNEL_RESPONSE);

        return $eventDispatcher;
    }

    /**
     * @param string $sessionName
     * @param string $sessionId
     * @param array<string, mixed> $params
     *
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function createSessionCookie(string $sessionName, string $sessionId, array $params): Cookie
    {
        $cookieLifetime = $params['lifetime'] === 0 ? 0 : time() + $params['lifetime'];

        return new Cookie(
            $sessionName,
            $sessionId,
            $cookieLifetime,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly'],
            false,
            $params['samesite'] ?? Cookie::SAMESITE_LAX,
        );
    }

    /**
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected function getSession(ContainerInterface $container): SessionInterface
    {
        return $container->get(static::SERVICE_SESSION);
    }

    /**
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return bool
     */
    protected function isSessionTestEnabled(ContainerInterface $container): bool
    {
        return $container->has(static::FLAG_SESSION_TEST) && $container->get(static::FLAG_SESSION_TEST);
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
