<?php

namespace Yoga;

/**
 * @method static Sso service()
 */
class Sso extends \Yoga\Service {

    /**
     * @var \Yoga\Session\User
     */
    protected $loggedInUser;

    /**
     * @param string $accessToken
     * @param boolean $isUserInfoRequired
     * @return boolean|\Yoga\Session\User
     */
    public function validateAccessTokenAndGetUserInfo($accessToken, $isUserInfoRequired = false) {
        $ssoServerUrl = \Yoga\Configuration::service()
            ->getAuthenticationConfiguration()
            ->getSsoServerUrl();
        if ($isUserInfoRequired) {
            $accessTokenUrl = '/api/v1/authentication/user-info';
        } else {
            $accessTokenUrl = '/api/v1/authentication/validate';
        }
        $curl = curl_init($ssoServerUrl . $accessTokenUrl);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            ['Content-Type: application/x-www-form-urlencoded']
        );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            'accessToken=' . $accessToken
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $isSslVerify = !!\Yoga\Configuration::service()
            ->getAuthenticationConfiguration()
            ->getIsSslVerify();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $isSslVerify);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $isSslVerify ? 2 : 0);
        $responseJson = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (\Yoga\Enum\HttpResponseCode::OK != $httpStatusCode) {
            return false;
        }
        $userRecord = json_decode($responseJson, true);
        if (!isset($userRecord['id']) || ($isUserInfoRequired && !isset($userRecord['email']))) {
            \Yoga\Logger::service()->debug('Invalid user response from SSO server!');
            \Yoga\Logger::service()->debug($userRecord);
            return false;
        }
        $this->loggedInUser = (new \Yoga\Session\User)
            ->setId($userRecord['id']);
        if ($isUserInfoRequired) {
            $this->loggedInUser
                ->setEmail($userRecord['email'])
                ->setFirstName(\Yoga\Arrays::service()->safeGet($userRecord, 'firstName'))
                ->setLastName(\Yoga\Arrays::service()->safeGet($userRecord, 'lastName'))
                ->setPermissions(\Yoga\Arrays::service()->safeGet($userRecord, 'permissions') ?: []);
        }
        return $this->loggedInUser;
    }

    /**
     * @param string $accessToken
     * @param string $permissionRequired
     * @throws \Yoga\Api\Exception
     */
    public function assertAccessTokenValid($accessToken, $permissionRequired) {
        $isUserInfoRequired = !!$permissionRequired;
        $result = $this->validateAccessTokenAndGetUserInfo($accessToken, $isUserInfoRequired);
        if ($result) {
            if (!!$permissionRequired && !in_array($permissionRequired, $result->getPermissions())) {
                throw new \Yoga\Api\Exception(
                    '`' . $permissionRequired . '` permission is required',
                    \Yoga\Enum\HttpResponseCode::UNAUTHORIZED
                );
            }
            return;
        }
        throw new \Yoga\Api\Exception(
            'Access denied, please sign in',
            \Yoga\Enum\HttpResponseCode::ACCESS_DENIED
        );
    }

    public function getLoggedInUser() {
        return $this->loggedInUser;
    }

}