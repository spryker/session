<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Shared\Session\Communcation\Plugin\ServiceProvider;

use Silex\Application;
use Silex\Provider\SessionServiceProvider as SilexSessionServiceProvider;
use SprykerFeature\Shared\Library\Config;
use SprykerFeature\Shared\Library\Session as SessionHelper;
use SprykerFeature\Shared\Session\SessionConfig;
use SprykerFeature\Shared\System\SystemConfig;
use SprykerFeature\Shared\Yves\YvesConfig;

trait SessionServiceProviderPluginTrait
{

    /**
     * @param Application $app
     * @throws \Exception
     */
    public function register(Application $app)
    {
        SilexSessionServiceProvider::register($app);

        $saveHandler = Config::get(YvesConfig::YVES_SESSION_SAVE_HANDLER);

        if ($saveHandler != SessionConfig::SESSION_HANDLER_COUCHBASE
            && $saveHandler != SessionConfig::SESSION_HANDLER_MYSQL
            && $saveHandler != SessionConfig::SESSION_HANDLER_REDIS
        ) {

            if (Config::get(YvesConfig::YVES_SESSION_SAVE_HANDLER) && $this->getSavePath($saveHandler)) {
                ini_set('session.save_handler', Config::get(YvesConfig::YVES_SESSION_SAVE_HANDLER));
                session_save_path($this->getSavePath($saveHandler));
            }
        }

        $app['session.storage.options'] = [
            'cookie_httponly' => true,
        ];

        $options = [];
        if (($name = Config::get(YvesConfig::YVES_SESSION_NAME))) {
            $options['name'] = $name;
        }
        if (($cookie_domain = Config::get(YvesConfig::YVES_SESSION_COOKIE_DOMAIN))) {
            $options['cookie_domain'] = $cookie_domain;
        }
        $app['session.storage.options'] = $options;

        /**
         * We manually register our own couchbase session handler, for all other handlers we use the generic one
         */
        switch ($saveHandler) {
            case SessionConfig::SESSION_HANDLER_COUCHBASE:
                $couchbaseSessionHandler = SessionHelper::registerCouchbaseSessionHandler($this->getSavePath($saveHandler));
                $app['session.storage.handler'] = $app->share(function () use ($couchbaseSessionHandler) {
                    return $couchbaseSessionHandler;
                });
                break;

            case SessionConfig::SESSION_HANDLER_MYSQL:
                $mysqlSessionHandler = SessionHelper::registerMysqlSessionHandler($this->getSavePath($saveHandler));
                $app['session.storage.handler'] = $app->share(function () use ($mysqlSessionHandler) {
                    return $mysqlSessionHandler;
                });
                break;

            case SessionConfig::SESSION_HANDLER_REDIS:
                $redisSessionHandler = SessionHelper::registerRedisSessionHandler($this->getSavePath($saveHandler));
                $app['session.storage.handler'] = $app->share(function () use ($redisSessionHandler) {
                    return $redisSessionHandler;
                });
                break;

            default:
                $app['session.storage.handler'] = $app->share(function () {
                    return new \SessionHandler();
                });
        }
    }

    /**
     * @param string $saveHandler
     *
     * @return string
     * @throws \Exception
     */
    protected function getSavePath($saveHandler)
    {
        $path = null;
        switch ($saveHandler) {
            case SessionConfig::SESSION_HANDLER_REDIS:
                $path = Config::get(SystemConfig::YVES_STORAGE_SESSION_REDIS_PROTOCOL)
                    . '://' . Config::get(SystemConfig::YVES_STORAGE_SESSION_REDIS_HOST)
                    . ':' . Config::get(SystemConfig::YVES_STORAGE_SESSION_REDIS_PORT);
                break;
            default:
                throw new \Exception('Needs implementation for mysql and couchbase!');
        }

        return $path;
    }

}
