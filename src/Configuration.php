<?php

namespace Yoga;

/**
 * @method static Configuration service()
 */
class Configuration extends Service {

    public function getProjectName() {
        return 'yoga';
    }

    public function getHttpHost() {
        return 'http://yoga-framework.com';
    }

    /**
     * @return \Yoga\Configuration\Sql
     */
    public function getSqlConfiguration() {
        return (new \Yoga\Configuration\Sql\Mysql)
            ->setHost('local.vagrant')  // 192.168.50.100 local.vagrant <-- in hosts file
            ->setUserName('root')
            ->setPassword('');
    }

    /**
     * @return \Yoga\Configuration\Cache
     */
    public function getCacheConfiguration() {
        return (new \Yoga\Configuration\Cache\Memcache)
            ->setHost('localhost')
            ->setPort(11211);
    }

    /**
     * @return \Yoga\Configuration\Cache\Redis
     */
    public function getRedisConfiguration() {
        return (new \Yoga\Configuration\Cache\Redis)
            ->setHost('local.vagrant')
            ->setPort(6379)
            ->setDb(0);
    }

    public function getAuthenticationConfiguration() {
        return (new \Yoga\Configuration\Authentication\Sso)
            ->setSsoServerUrl('https://local-sso.slyceapp.com')
            ->setIsSslVerify(true);
    }

    /**
     * @return \Yoga\Configuration\Email\Ses
     */
    public function getEmailConfiguration() {
        return (new \Yoga\Configuration\Email\Ses)
            ->setSupportFromAddress('Slyce Support <support@slyce.it>')
            ->setLayout(new \Yoga\Email\Layout\Main);
    }

    public function getDefaultRecordsPerPage() {
        return 20;
    }

    /**
     * This array gets compiled to /public_http/var/js/Constant.js
     * @return array
     */
    public function getJavascriptConstants() {
        return [
            'Authentication' => [
                'ssoServerUrl' => $this->getAuthenticationConfiguration()->getSsoServerUrl(),
                'RESERVED_URL_LOGOUT' => Session::RESERVED_URL_LOGOUT
            ]
        ];
    }

    /**
     * Array of these enums gets compiled to /public_http/var/js/Enum.js
     * @return array
     */
    public function getJavascriptEnumClasses() {
        return [
            \Yoga\Enum\HttpResponseCode::class,
            \Yoga\Enum\YesNo::class,
        ];
    }

    /**
     * @return \Yoga\Configuration\Aws
     */
    public function getAwsConfiguration() {
        return null;
    }

}
