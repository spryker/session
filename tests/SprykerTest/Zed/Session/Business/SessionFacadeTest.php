<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Session\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MessageAttributesTransfer;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use ReflectionProperty;
use Spryker\Client\Session\SessionClientInterface;
use Spryker\Shared\Session\SessionConstants;
use Spryker\Shared\SessionExtension\Dependency\Plugin\SessionHandlerProviderPluginInterface;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Session\Business\Exception\NotALockingSessionHandlerException;
use Spryker\Zed\Session\Business\Expander\MessageAttributesExpander;
use Spryker\Zed\Session\Communication\Plugin\Application\SessionApplicationPlugin;
use Spryker\Zed\Session\SessionDependencyProvider;
use Spryker\Zed\SessionExtension\Dependency\Plugin\SessionLockReleaserPluginInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Session
 * @group Business
 * @group Facade
 * @group SessionFacadeTest
 * Add your own group annotations below this line
 * @property \SprykerTest\Zed\Session\SessionBusinessTester $tester
 */
class SessionFacadeTest extends Unit
{
    /**
     * @var string
     */
    protected const SUPPORTING_LOCK_SESSION_HANDLER_NAME = 'SUPPORTING_LOCK_SESSION_HANDLER_NAME';

    /**
     * @var string
     */
    protected const NOT_SUPPORTING_LOCK_SESSION_HANDLER_NAME = 'NOT_SUPPORTING_LOCK_SESSION_HANDLER_NAME';

    /**
     * @var string
     */
    protected const ANOTHER_NOT_SUPPORTING_LOCK_SESSION_HANDLER_NAME = 'ANOTHER_NOT_SUPPORTING_LOCK_SESSION_HANDLER_NAME';

    /**
     * @var \Spryker\Zed\SessionExtension\Dependency\Plugin\SessionLockReleaserPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $supportingLockReleaserPlugin;

    /**
     * @var \Spryker\Zed\SessionExtension\Dependency\Plugin\SessionLockReleaserPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $notSupportingLockReleaserPlugin;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->supportingLockReleaserPlugin = $this->createSupportingLockReleaserPluginMock();
        $this->notSupportingLockReleaserPlugin = $this->createNotSupportingLockReleaserPluginMock();

        $this->setupSessionPluginDependencies();

        $this->tester->addApplicationPlugin(new SessionApplicationPlugin());
    }

    /**
     * @dataProvider supportingLockSessionHandler
     *
     * @param string $sessionHandler
     *
     * @return void
     */
    public function testRemoveYvesSessionLockForReleasesLockWhenHandlerSupportsLocking(string $sessionHandler): void
    {
        $this->supportingLockReleaserPlugin
            ->expects($this->once())
            ->method('release')
            ->with(session_id());

        $this->tester->setConfig(SessionConstants::YVES_SESSION_SAVE_HANDLER, $sessionHandler);

        $sessionFacade = $this->tester->getLocator()->session()->facade();
        $sessionFacade->removeYvesSessionLockFor(session_id());
    }

    /**
     * @dataProvider notSupportingLockSessionHandler
     *
     * @param string $sessionHandler
     *
     * @return void
     */
    public function testRemoveYvesSessionLockForThrowsExceptionWhenSessionHandlerDoesNotSupportLocking(string $sessionHandler): void
    {
        $this->tester->setConfig(SessionConstants::YVES_SESSION_SAVE_HANDLER, $sessionHandler);

        $this->expectException(NotALockingSessionHandlerException::class);

        $sessionFacade = $this->tester->getLocator()->session()->facade();
        $sessionFacade->removeYvesSessionLockFor(session_id());
    }

    /**
     * @dataProvider supportingLockSessionHandler
     *
     * @param string $sessionHandler
     *
     * @return void
     */
    public function testRemoveZedSessionLockForReleasesLockWhenHandlerSupportsLocking(string $sessionHandler): void
    {
        $this->supportingLockReleaserPlugin
            ->expects($this->once())
            ->method('release')
            ->with(session_id());

        $this->tester->setConfig(SessionConstants::ZED_SESSION_SAVE_HANDLER, $sessionHandler);

        $sessionFacade = $this->tester->getLocator()->session()->facade();
        $sessionFacade->removeZedSessionLockFor(session_id());
    }

    /**
     * @dataProvider notSupportingLockSessionHandler
     *
     * @param string $sessionHandler
     *
     * @return void
     */
    public function testRemoveZedSessionLockForThrowsExceptionWhenSessionHandlerDoesNotSupportLocking(string $sessionHandler): void
    {
        $this->tester->setConfig(SessionConstants::ZED_SESSION_SAVE_HANDLER, $sessionHandler);

        $this->expectException(NotALockingSessionHandlerException::class);

        $sessionFacade = $this->tester->getLocator()->session()->facade();
        $sessionFacade->removeZedSessionLockFor(session_id());
    }

    /**
     * @return void
     */
    public function testExpandMessageAttributesSuccessfullyWhenSessionDoesntHaveSessionTrackingIdAndNotStarted(): void
    {
        //Arrange
        $messageAttributesTransfer = $this->tester->getMessageAttributesTransfer();

        $this->tester->setDependency(SessionDependencyProvider::SESSION_CLIENT, function (Container $container) {
            $sessionClient = $this->tester->getLocator()->session()->client();
            $sessionClient->setContainer($container->getApplicationService('session'));

            return $sessionClient;
        });

        //Act
        $expandedMessageAttributesTransfer = $this->tester
            ->getFacade()
            ->expandMessageAttributesWithSessionTrackingId($messageAttributesTransfer);

        //Assert
        $this->assertNotNull($expandedMessageAttributesTransfer->getSessionTrackingId());
    }

    /**
     * @return void
     */
    public function testExpandMessageAttributesSuccessfullyWhenSessionDoesntHaveSessionTrackingIdAndStarted(): void
    {
        //Arrange
        $messageAttributesTransfer = $this->tester->getMessageAttributesTransfer();

        $this->tester->setDependency(SessionDependencyProvider::SESSION_CLIENT, function (Container $container) {
            $sessionClient = $this->tester->getLocator()->session()->client();
            $sessionClient->setContainer($container->getApplicationService('session'));
            $sessionClient->start();

            return $sessionClient;
        });
        $this->removeCachedSessionTrackingId();

        //Act
        $expandedMessageAttributesTransfer = $this->tester
            ->getFacade()
            ->expandMessageAttributesWithSessionTrackingId($messageAttributesTransfer);

        //Assert
        $this->assertNotNull($expandedMessageAttributesTransfer->getSessionTrackingId());
    }

    /**
     * @return void
     */
    public function testExpandMessageAttributesSuccessfullyWhenSessionHasSessionTrackingId(): void
    {
        //Arrange
        $sessionId = Uuid::uuid4()->toString();
        $messageAttributesTransfer = $this->tester->getMessageAttributesTransfer();

        $this->tester->setDependency(SessionDependencyProvider::SESSION_CLIENT, function (Container $container) use ($sessionId) {
            $sessionClient = $this->tester->getLocator()->session()->client();
            $sessionClient->setContainer($container->getApplicationService('session'));
            $sessionClient->set('sessionTrackingId', $sessionId);

            return $sessionClient;
        });
        $this->removeCachedSessionTrackingId();

        //Act
        $expandedMessageAttributesTransfer = $this->tester
            ->getFacade()
            ->expandMessageAttributesWithSessionTrackingId($messageAttributesTransfer);

        //Assert
        $this->assertSame($sessionId, $expandedMessageAttributesTransfer->getSessionTrackingId());
    }

    /**
     * @return void
     */
    public function testMessageAttributesNotExpandedWhenSessionTrackingIdIsPresent(): void
    {
        //Arrange
        $sessionTrackingId = 'test';
        $messageAttributesTransfer = $this->tester->getMessageAttributesTransfer([
            MessageAttributesTransfer::SESSION_TRACKING_ID => $sessionTrackingId,
        ]);

        //Act
        $expandedMessageAttributesTransfer = $this->tester
            ->getFacade()
            ->expandMessageAttributesWithSessionTrackingId($messageAttributesTransfer);

        //Assert
        $this->assertSame(
            $sessionTrackingId,
            $expandedMessageAttributesTransfer->getSessionTrackingId(),
        );
    }

    /**
     * @return array
     */
    public function supportingLockSessionHandler(): array
    {
        return [
            [static::SUPPORTING_LOCK_SESSION_HANDLER_NAME],
        ];
    }

    /**
     * @return array
     */
    public function notSupportingLockSessionHandler(): array
    {
        return [
            [static::NOT_SUPPORTING_LOCK_SESSION_HANDLER_NAME],
            [static::ANOTHER_NOT_SUPPORTING_LOCK_SESSION_HANDLER_NAME],
        ];
    }

    /**
     * @return void
     */
    protected function removeCachedSessionTrackingId(): void
    {
        $messageAttributesExpander = new MessageAttributesExpander(
            $this->createMock(SessionClientInterface::class),
        );
        $reflector = new ReflectionClass($messageAttributesExpander);
        $staticProperties = $reflector->getProperties(ReflectionProperty::IS_STATIC);
        $staticProperties[0]->setAccessible(true);
        $staticProperties[0]->setValue(null);
    }

    /**
     * @return \Spryker\Zed\SessionExtension\Dependency\Plugin\SessionLockReleaserPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSupportingLockReleaserPluginMock(): SessionLockReleaserPluginInterface
    {
        $supportingLockReleaserPluginMock = $this->createMock(SessionLockReleaserPluginInterface::class);
        $supportingLockReleaserPluginMock->method('getSessionHandlerName')
            ->willReturn(static::SUPPORTING_LOCK_SESSION_HANDLER_NAME);

        return $supportingLockReleaserPluginMock;
    }

    /**
     * @return \Spryker\Zed\SessionExtension\Dependency\Plugin\SessionLockReleaserPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createNotSupportingLockReleaserPluginMock(): SessionLockReleaserPluginInterface
    {
        $notSupportingLockReleaserPluginMock = $this->createMock(SessionLockReleaserPluginInterface::class);
        $notSupportingLockReleaserPluginMock->method('getSessionHandlerName')
            ->willReturn(static::NOT_SUPPORTING_LOCK_SESSION_HANDLER_NAME);

        return $notSupportingLockReleaserPluginMock;
    }

    /**
     * @return void
     */
    protected function setupSessionPluginDependencies(): void
    {
        $this->tester->setDependency(SessionDependencyProvider::PLUGINS_YVES_SESSION_LOCK_RELEASER, function (Container $container) {
            return [
                $this->supportingLockReleaserPlugin,
            ];
        });

        $this->tester->setDependency(SessionDependencyProvider::PLUGINS_ZED_SESSION_LOCK_RELEASER, function (Container $container) {
            return [
                $this->supportingLockReleaserPlugin,
            ];
        });

        $this->tester->setDependency(SessionDependencyProvider::PLUGINS_SESSION_HANDLER, function (Container $container) {
            return [
                $this->createMock(SessionHandlerProviderPluginInterface::class),
            ];
        });
    }
}
