<?php

namespace Yoga;

/**
 * @method static Session service()
 */
class Session extends \Yoga\Service {

    const RESERVED_URL_LOGGED_IN = '/api/v1/logged-in';
    const RESERVED_URL_LOGOUT = '/api/v1/logout';

    /**
     * @param bool $isLoginRequired
     * @param string $permissionRequired
     * @return bool
     */
    public function authenticate($isLoginRequired = false, $permissionRequired = null) {
        $this->isLoginRequired = $isLoginRequired;
        if (!$this->isAccessTokenValid()) {
            $this->setUser(null);
            if (!$isLoginRequired) {
                return false;
            }
            $this->redirectToLoginForm();
            exit;
        }
        if ($permissionRequired) {
            $user = $this->getUser();
            if (!$user || !in_array($permissionRequired, $user->getPermissions())) {
                die('You don\'t have the permission required to access this page.');
            }
        }
        return true;
    }

    private function redirectToLoginForm() {
        $loginFormUrl = \Yoga\Configuration::service()
            ->getAuthenticationConfiguration()
            ->getCustomLoginFormUrl();
        $redirectContext = \Yoga\Session\RedirectContext::createFromGlobals();
        $this->setRedirectContext($redirectContext);
        if (!$loginFormUrl) {
            $loginFormUrl = Urls::service()->appendParameters(
                \Yoga\Configuration::service()
                    ->getAuthenticationConfiguration()
                    ->getSsoServerUrl() .
                    '/login',
                [
                    self::REDIRECT_CONTEXT_KEY_NAME =>
                        $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
                ]
            );
        }
        header('Location: ' . $loginFormUrl);
    }

    private function isAccessTokenValid() {
        $user = $this->getUser();
        if (!$user instanceof \Yoga\Session\User) {
            $user = null;
        }
        if (!($accessToken = $this->getAccessToken())) {
            return false;
        }
        $isUserInfoRequired = !$user;
        $result = Sso::service()
            ->validateAccessTokenAndGetUserInfo($accessToken, $isUserInfoRequired);
        if (!$result) {
            return $result;
        }
        if ($isUserInfoRequired) {
            $this->setUser($result);
        }
        return true;
    }

    public function initLoginForm() {
        if ($this->getRedirectContext()) {
            return;
        }
        $httpReferrer = \Yoga\Arrays::service()->safeGet($_SERVER, 'HTTP_REFERER');
        if ($httpReferrer) {
            $this->setRedirectContext(
                (new \Yoga\Session\RedirectContext)
                    ->setUrl($httpReferrer)
            );
        }
    }

    public function loggedIn() {
        if (isset($_GET['accessToken']) && isset($_GET['refreshToken'])) {
            $this
                ->setAccessToken($_GET['accessToken'])
                ->setRefreshToken($_GET['refreshToken']);
            $redirectContext = $this->getRedirectContext();
            if (!$redirectContext && isset($_GET[self::REDIRECT_CONTEXT_KEY_NAME])) {
                header('Location: ' . $_GET[self::REDIRECT_CONTEXT_KEY_NAME]);
                return;
            }
            if ($redirectContext) {
                $this->setRedirectContext(null);
                $redirectContext->redirect();
                return;
            }
        }
        header('Location: /');
    }

    public function getRedirectBackUrl() {
        $result = \Yoga\Arrays::service()
            ->safeGet($_GET, self::REDIRECT_CONTEXT_KEY_NAME);
        if (!$result) {
            $result = \Yoga\Arrays::service()
                ->safeGet($_SERVER, 'HTTP_REFERER');
        }
        if (!$result) {
            $result = '/';
        }
        return $result;
    }

    public function logout() {
        $urlRedirect = $this->getRedirectBackUrl();
        $this->setUser(null);
        $this->setAccessToken(null);
        $this->setRefreshToken(null);
        header('Location: ' . $urlRedirect);
        exit;
    }

    public function getLogoutFromSsoUrl() {
        return $this->getLogoutUrl(
            \Yoga\Configuration::service()
                ->getAuthenticationConfiguration()
                ->getSsoServerUrl() .
                '/api/v1/authentication/logout?accessToken=' .
                \Yoga\Session::service()->getAccessToken()
        );
    }

    public function getLogoutFromAppUrl() {
        return $this->getLogoutUrl(self::RESERVED_URL_LOGOUT, false);
    }

    public function getLoggedInUrl($url, $isTokensRequired = false, $isAngular = false) {
        if ($isTokensRequired) {
            $parameters = [
                'accessToken' => $this->getAccessToken(),
                'refreshToken' => $this->getRefreshToken()
            ];
        } else {
            $parameters = [];
        }
        if ($isAngular) {
            $resultUrl = $url;
        } else {
            $resultUrl = Urls::service()->getRootUrl($url) . \Yoga\Session::RESERVED_URL_LOGGED_IN;
            if (parse_url($url, PHP_URL_PATH) || parse_url($url, PHP_URL_QUERY)) {
                $redirectUrl = parse_url($url, PHP_URL_PATH);
                if (!$redirectUrl) {
                    $redirectUrl = '/';
                }
                if (parse_url($url, PHP_URL_QUERY)) {
                    $redirectUrl .= '?' . parse_url($url, PHP_URL_QUERY);
                }
                $parameters[self::REDIRECT_CONTEXT_KEY_NAME] = $redirectUrl;
            }
        }
        return \Yoga\Urls::service()->appendParameters(
            $resultUrl,
            $parameters
        );
    }

    /**
     * @return \Yoga\Session\User
     */
    public function getUser() {
        return $this->get(self::USER_KEY_NAME);
    }

    private function set($key, $value) {
        $this->startPhpSession();
        $_SESSION[$key] = $value;
        return $this;
    }

    private function get($key) {
        $this->startPhpSession();
        return \Yoga\Arrays::service()->safeGet($_SESSION, $key);
    }

    private function setAccessToken($accessToken) {
        return $this->set(self::ACCESS_TOKEN_KEY_NAME, $accessToken);
    }

    private function getAccessToken() {
        return $this->get(self::ACCESS_TOKEN_KEY_NAME);
    }

    private function setRedirectContext(
        \Yoga\Session\RedirectContext $redirectContext = null
    ) {
        return $this->set(self::REDIRECT_CONTEXT_KEY_NAME, $redirectContext);
    }

    /**
     * @return \Yoga\Session\RedirectContext
     */
    private function getRedirectContext() {
        return $this->get(self::REDIRECT_CONTEXT_KEY_NAME);
    }

    private function setRefreshToken($refreshToken) {
        return $this->set(self::REFRESH_TOKEN_KEY_NAME, $refreshToken);
    }

    private function getRefreshToken() {
        return $this->get(self::REFRESH_TOKEN_KEY_NAME);
    }

    private function setUser(\Yoga\Session\User $user = null) {
        return $this->set(self::USER_KEY_NAME, $user);
    }

    private function getLogoutUrl($baseUrl, $isAbsoluteUrlRequired = true) {
        if ($this->isLoginRequired) {
            if ($isAbsoluteUrlRequired) {
                $urlRedirect = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
            } else {
                $urlRedirect = '/';
            }
        } else {
            $urlRedirect = null;
        }
        return Urls::service()->appendParameters(
            $baseUrl,
            [self::REDIRECT_CONTEXT_KEY_NAME => $urlRedirect]
        );
    }

    private function startPhpSession() {
        static $isAlready;
        if (!$isAlready) {
            $isAlready = true;
            session_start();
        }
    }

    /**
     * This will remember if current page is protected for round-trip applications
     * @var bool
     */
    private $isLoginRequired;

    const ACCESS_TOKEN_KEY_NAME = '_a';
    const REFRESH_TOKEN_KEY_NAME = '_r';
    const USER_KEY_NAME = '_u';
    const REDIRECT_CONTEXT_KEY_NAME = '_';

}