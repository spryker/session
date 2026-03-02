<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Session\Business\Handler;

use Codeception\Test\Unit;
use Spryker\Service\Monitoring\MonitoringServiceInterface;
use Spryker\Shared\Session\Business\Handler\SessionHandlerFile;
use Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceBridge;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group Session
 * @group Business
 * @group Handler
 * @group SessionHandlerFileTest
 * Add your own group annotations below this line
 */
class SessionHandlerFileTest extends Unit
{
    /**
     * @var int
     */
    public const LIFETIME = 20;

    /**
     * @var string
     */
    public const SESSION_NAME = 'sessionName';

    /**
     * @var string
     */
    public const SESSION_ID = 'sessionId';

    /**
     * @var string
     */
    public const SESSION_ID_2 = 'anotherSessionId';

    /**
     * @var string
     */
    public const SESSION_DATA = 'sessionData';

    public function tearDown(): void
    {
        if (is_dir($this->getFixtureDirectory())) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->getFixtureDirectory());
        }
    }

    private function getFixtureDirectory(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures';
    }

    protected function getSavePath(): string
    {
        return $this->getFixtureDirectory() . DIRECTORY_SEPARATOR . 'Sessions';
    }

    public function testCallOpenMustCreateDirectoryIfNotExists(): void
    {
        $this->assertFalse(is_dir($this->getSavePath()));

        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);

        $this->assertTrue(is_dir($this->getSavePath()));
    }

    public function testCallOpenMustReturnTrue(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $result = $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);

        $this->assertTrue($result);
    }

    public function testCallCloseMustReturnTrue(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $result = $sessionHandlerFile->close();

        $this->assertTrue($result);
    }

    public function testCallWriteMustReturnFalseIfNoDataPassed(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);
        $result = $sessionHandlerFile->write(static::SESSION_ID, '');

        $this->assertFalse($result);
    }

    public function testCallWriteMustReturnTrueWhenDataCanBeWrittenToFile(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);
        $result = $sessionHandlerFile->write(static::SESSION_ID, static::SESSION_DATA);

        $this->assertTrue($result);
    }

    public function testWriteMustAllowZeroValue(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);
        $result = $sessionHandlerFile->write(static::SESSION_ID, '0');

        $this->assertTrue($result);
    }

    public function testCallReadMustReturnContentOfSessionForGivenSessionId(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);
        $sessionHandlerFile->write(static::SESSION_ID, static::SESSION_DATA);

        $result = $sessionHandlerFile->read(static::SESSION_ID);

        $this->assertSame(static::SESSION_DATA, $result);
    }

    public function testCallDestroyMustReturnTrueIfNoFileExistsForSessionId(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);

        $result = $sessionHandlerFile->destroy(static::SESSION_ID);

        $this->assertTrue($result);
    }

    public function testCallDestroyMustReturnTrueIfFileExistsForSessionId(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);
        $sessionHandlerFile->write(static::SESSION_ID, static::SESSION_DATA);

        $result = $sessionHandlerFile->destroy(static::SESSION_ID);

        $this->assertTrue($result);
    }

    public function testCallGcMustDeleteFilesWhichAreOlderThenMaxLifetime(): void
    {
        $sessionHandlerFile = new SessionHandlerFile($this->getSavePath(), static::LIFETIME, $this->createMonitoringServiceMock());
        $sessionHandlerFile->open($this->getSavePath(), static::SESSION_NAME);
        $sessionHandlerFile->write(static::SESSION_ID, static::SESSION_DATA);
        $this->makeFileOlderThanItIs();
        $sessionHandlerFile->write(static::SESSION_ID_2, static::SESSION_DATA);
        $this->makeFileNewerThanItIs();

        $finder = new Finder();
        $finder->in($this->getSavePath());

        $this->assertCount(2, $finder);

        $sessionHandlerFile->gc(1);
        $this->assertCount(1, $finder);

        unlink($this->getSavePath() . '/session:' . static::SESSION_ID_2);
        rmdir($this->getSavePath());
    }

    protected function makeFileOlderThanItIs(): void
    {
        touch($this->getSavePath() . DIRECTORY_SEPARATOR . 'session:' . static::SESSION_ID, time() - 200);
    }

    protected function makeFileNewerThanItIs(): void
    {
        touch($this->getSavePath() . DIRECTORY_SEPARATOR . 'session:' . static::SESSION_ID_2, time() + 200);
    }

    protected function createMonitoringServiceMock(): SessionToMonitoringServiceBridge
    {
        $mock = $this->getMockBuilder(MonitoringServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sessionToMonitoringServiceBridge = new SessionToMonitoringServiceBridge($mock);

        return $sessionToMonitoringServiceBridge;
    }
}
