<?php

namespace Yoga\Compiler\Api;

class Angular extends \Yoga\Compiler\Api {

    protected function getOutputFileName() {
        return 'Api.angular.js';
    }

    protected function compileHeader() {
        return '\'use strict\';

            angular.module(\'Main\')
            .service(\'Api\', [\'$http\', \'$q\', \'AuthService\', \'pendingRequests\', function ($http, $q, AuthService, pendingRequests) {
                this.Url = {};
        ';
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
                    $result .= 'this.' . $jsNamespace . ' = {};' . "\n";
                    $result .= 'this.Url.' . $jsNamespace . ' = {};' . "\n";
                }
            }
            $jsPath = $jsNamespace . '.' . $class;
        } else {
            $jsPath = $class;
        }
        $url = $reflection->getUrlPattern();
        $result .= 'this.Url.' . $jsPath . ' = "' . $url . '";';
        foreach ($reflection->getRouteParameters() as $routeParameter) {
//            $url = str_replace('{' . $routeParameter . '}', '" + (typeof(data["' . $routeParameter . '"]) == "undefined" ? "" : data["' . $routeParameter . '"]) + "', $url);
            $url = preg_replace(
                '/{' . $routeParameter . '(:.+)?}/',
                '" + (typeof(data["' . $routeParameter . '"]) == "undefined" ? "" : data["' . $routeParameter . '"]) + "',
                $url
            );
        }
        if (is_array($reflection->getMethod())) {
            $method = $reflection->getMethod()[0];
        } else {
            $method = $reflection->getMethod();
        }
        $accessTokenLine = $reflection->getIsLoginRequired()
            ? 'angular.extend(data, { accessToken: AuthService.getAccessToken() });'
            : '';
        $transformParametersForPutRequestLine = 'PUT' == $method
            ? 'data = Slyce.Application.transformParametersForPutRequest(data);'
            : '';
        $result .= '
            this.' . $jsPath . ' = function(data, additionalParameters, isSkipAddToPendingRequests) {
                if (!data) {
                    data = {};
                }
                ' . $accessTokenLine . '
                ' . $transformParametersForPutRequestLine . '
                var deferredAbort = $q.defer();
                var parameters = {
                    url: "' . $url . '",
                    method: "' . $method . '",
                    ' . ('POST' == $method ? 'data' : 'params') . ': data,
                    timeout: deferredAbort.promise
                };
                angular.extend(parameters, additionalParameters);
                var request = $http(parameters);

                var url = parameters.url;

                if (!isSkipAddToPendingRequests) {
                    pendingRequests.add({
                        url: url,
                        canceller: deferredAbort
                    });
                }

                var promise = request.then(
                    function (response) {
                        return response.data;
                    },
                    function (response) {
                        return $q.reject(response);
                    }
                );

                promise.abort = function() {
                    deferredAbort.resolve();
                };

                promise.finally(
                    function() {
                        pendingRequests.remove(url);
                        promise.abort = angular.noop;
                        deferredAbort = request = promise = null;
                    }
                );
                return promise;
            };
        ';
        return $result;
    }

    protected function compileSessionEndpoints() {
        return <<<EOF
this.Session = {

    Login: function(username, password) {
        return \$http({
            url: Constant.Authentication.ssoServerUrl + '/api/v1/authentication/oauth2',
            method: 'POST',
            params: {
                client_id: 'slyce',
                redirect_uri: '',
                client_secret: '',
                grant_type: 'password',
                username: username,
                password: password
            }
        });
    }

}
EOF;
    }

    protected function compileFooter() {
        return '}]);';
    }

}