module.exports = function ($app) {
    var auth = require('./auth/service')($app);
    return $app.controller('ApplicationController', ['$scope', 'auth', '$state', '$http', function ($scope, auth, $state, $http) {
        $scope.isAuthorized = auth.authorized;
        var infoUpdate = function () {
            console.log('auth is', auth.authorized);
            $scope.isAuthorized = auth.authorized;
            $scope.type = auth.type;
            $scope.typeCode = auth.typeCode;
            if (!auth.authorized) {
                $state.go('auth.login', null, {reload: true});
            }
        };
        $scope.$on('auth:authorizedChange', infoUpdate);
        if ($scope.isAuthorized) {
            infoUpdate();
        }
        $scope.$on('$stateChangeSuccess', function (event, toState, toParams, fromState, fromParams) {
            if (!auth.authorized && toState.name.indexOf('auth') < 0) {
                $state.go('auth.login');
            }
        });
    }]);
}