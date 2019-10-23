<?php

namespace Yoga\Compiler\Api;

class Jquery extends \Yoga\Compiler\Api {

    protected function getOutputFileName() {
        return 'Api.jquery.js';
    }

    protected function compileHeader() {
        return 'var Api = {};';
    }

    protected function compileOneEndpoint(\Yoga\Api\Reflection $reflection) {
        static $isNamespaceTouched = [];
        $result = '';
        $fullClassName = substr($reflection->getClass(), 4);  // remove `Api\` part
        $p = strrpos($fullClassName, '\\');
        $namespace = substr($fullClassName, 0, $p);
        if ($p) {
            $class = substr($fullClassName, $p + 1);
        } else {
            $class = $fullClassName;
        }
        if ($namespace) {
            $jsNamespace = '';
            foreach (explode('\\', $namespace) as $namespacePart) {
                if ($jsNamespace) {
                    $jsNamespace .= '.';
                }
                $jsNamespace .= $namespacePart;
                if (!isset($isNamespaceTouched[$jsNamespace])) {
                    $isNamespaceTouched[$jsNamespace] = true;
                    $result .= 'Api.' . $jsNamespace . ' = {};' . "\n";
                }
            }
            $jsPath = $jsNamespace . '.' . $class;
        } else {
            $jsPath = $class;
        }
        $url = $reflection->getUrlPattern();
        foreach ($reflection->getRouteParameters() as $routeParameter) {
            $url = str_replace('{p_' . $routeParameter . '}', '" + data["' . $routeParameter . '"] + "', $url);
        }
        if (is_array($reflection->getMethod())) {
            $method = $reflection->getMethod()[0];
        } else {
            $method = $reflection->getMethod();
        }
        $result .= '
            Api.' . $jsPath . ' = function(data, onSuccess, onError) {
                $.ajax({
                    url: "' . $url . '",
                    type: "' . $method . '",
                    data: data,
                    dataType: "json"
                })
                    .done(function (response) {
                        onSuccess(response);
                    })
                    .error(function (jqXHR) {
                        var errorCode, errorDescription;
                        if (!jqXHR.responseJSON || !jqXHR.responseJSON.errorDescription) {
                            errorDescription = "Invalid data received from the server... please reload the page and try again.";
                            errorCode = null;
                        } else {
                            errorDescription = jqXHR.responseJSON.errorDescription;
                            errorCode = jqXHR.responseJSON.errorCode;
                        }
                        onError(errorCode, errorDescription);
                    });
            }


        ';
        return $result;
    }

    protected function compileSessionEndpoints() {
        return <<<EOF
Api.Session = {

    Login: function (username, password, onError, onSuccess) {
        $.ajax({
            type: 'POST',
            url: Constant.Authentication.ssoServerUrl + '/api/v1/authentication/oauth2',
            data: {
                client_id: 'slyce',
                redirect_uri: '',
                client_secret: '',
                grant_type: 'password',
                username: username,
                password: password
            },
            dataType: 'json'
        })
            .success(function (response) {
                if (onSuccess) {
                    onSuccess(response.access_token, response.refresh_token);
                    return;
                }
                var url;
                url = '/api/v1/logged-in?accessToken=' + response.access_token +
                    '&refreshToken=' + response.refresh_token;
                window.location.replace(url);
            })
            .error(function (jqXHR) {
                var errorMessage;
                if (!jqXHR.responseJSON || !jqXHR.responseJSON.error_description) {
                    errorDescription = 'Cannot log you in... please refresh the page and try again.';
                } else {
                    errorDescription = jqXHR.responseJSON.error_description;
                }
                onError(null, errorDescription);
            })
        ;
    }

}
EOF;
    }

    protected function compileFooter() {
        return '';
    }

}