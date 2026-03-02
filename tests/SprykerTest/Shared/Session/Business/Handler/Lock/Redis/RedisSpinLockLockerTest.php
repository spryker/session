<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Session\Business\Handler\Lock\Redis;

use Codeception\Test\Unit;
use PHPUnit\Framework\ExpectationFailedException;
use Predis\Client;
use Spryker\Shared\Session\Business\Handler\KeyGenerator\Redis\RedisLockKeyGenerator;
use Spryker\Shared\Session\Business\Handler\KeyGenerator\Redis\RedisSessionKeyGenerator;
use Spryker\Shared\Session\Business\Handler\Lock\Redis\RedisSpinLockLocker;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group Session
 * @group Business
 * @group Handler
 * @group Lock
 * @group Redis
 * @group RedisSpinLockLockerTest
 * Add your own group annotations below this line
 */
class RedisSpinLockLockerTest extends Unit
{
    public function testLockBlocksUntilLockIsAcquired(): void
    {
        $redisClientMock = $this->getRedisClientMock();
        $callIndex = 0;
        $redisClientMock
            ->expects($this->exactly(3))
            ->method('__call')
            ->with($this->equalTo('set'), $this->anything())
            ->willReturnCallback(function ($name, $args) use (&$callIndex) {
                // Simulate consecutive returns: 0, 0, 1
                $values = [0, 0, 1];
                $return = $values[$callIndex] ?? end($values);
                $callIndex++;

                return $return;
            });

        $locker = new RedisSpinLockLocker($redisClientMock, new RedisLockKeyGenerator(new RedisSessionKeyGenerator()));
        $locker->lock('session_id');
    }

    public function testUnlockUsesGeneratedKeyFromStoredSessionId(): void
    {
        if (!method_exists($this, 'contains')) {
            $this->markTestSkipped('Contains method is not supported in PHPUnit 9, this tests needs refactoring.');
        }

        $sessionId = 'test_session_id';
        $expectedGeneratedKey = "session:{$sessionId}:lock";
        $redisClientMock = $this->getRedisClientMock();
        $callIndex = 0;
        $redisClientMock
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(function ($name, $args) use (&$callIndex, $expectedGeneratedKey) {
                if ($callIndex === 0) {
                    // first call should be set
                    if ($name !== 'set') {
                        throw new ExpectationFailedException(sprintf('Expected first call to be "set", got "%s"', $name));
                    }
                } else {
                    // second call should be eval and contain expected key
                    if ($name !== 'eval') {
                        throw new ExpectationFailedException(sprintf('Expected second call to be "eval", got "%s"', $name));
                    }
                    $haystack = is_array($args) && isset($args[0]) ? (string)$args[0] : '';
                    if (strpos($haystack, $expectedGeneratedKey) === false) {
                        throw new ExpectationFailedException(sprintf('Expected eval argument to contain "%s"', $expectedGeneratedKey));
                    }
                }

                $callIndex++;

                return 1;
            });

        $locker = new RedisSpinLockLocker($redisClientMock, new RedisLockKeyGenerator(new RedisSessionKeyGenerator()));
        $locker->lock($sessionId);
        $locker->unlockCurrent();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Predis\Client
     */
    private function getRedisClientMock(): Client
    {
        return $this
            ->getMockBuilder(Client::class)
            ->getMock();
    }
}
