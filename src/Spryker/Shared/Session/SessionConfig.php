<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Session;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class SessionConfig extends AbstractSharedConfig
{
    /**
     * @api
     *
     * @var string
     */
    public const SESSION_HANDLER_COUCHBASE = 'couchbase';

    /**
     * @api
     *
     * @deprecated Use {@link \Spryker\Shared\SessionRedis\SessionRedisConfig::SESSION_HANDLER_REDIS} instead.
     *
     * @var string
     */
    public const SESSION_HANDLER_REDIS = 'redis';

    /**
     * @api
     *
     * @deprecated Use {@link \Spryker\Shared\SessionRedis\SessionRedisConfig::SESSION_HANDLER_REDIS_LOCKING} instead.
     *
     * @var string
     */
    public const SESSION_HANDLER_REDIS_LOCKING = 'redis_locking';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_HANDLER_MYSQL = 'mysql';

    /**
     * @api
     *
     * @deprecated Use {@link \Spryker\Shared\SessionFile\SessionFileConfig::SESSION_HANDLER_FILE} instead.
     *
     * @var string
     */
    public const SESSION_HANDLER_FILE = 'file';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_LIFETIME_BROWSER_SESSION = '0';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_LIFETIME_1_HOUR = '3600';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_LIFETIME_0_5_HOUR = '1800';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_LIFETIME_1_DAY = '86400';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_LIFETIME_2_DAYS = '172800';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_LIFETIME_3_DAYS = '259200';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_LIFETIME_30_DAYS = '2592000';

    /**
     * @api
     *
     * @var string
     */
    public const SESSION_LIFETIME_1_YEAR = '31536000';
}
