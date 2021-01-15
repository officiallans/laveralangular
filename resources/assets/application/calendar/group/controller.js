module.exports = function ($app) {
    return $app.controller('CalendarGroupController', ['$scope', '$state', '$controller', '$http', '$uibModal', '$timeout', 'auth', function ($scope, $state, $controller, $http, $uibModal, $timeout, auth) {
        angular.extend(this, $controller('CalendarBaseController', {$scope: $scope}));

        $scope.minTypeCode = 50;

        $scope.groupId = $state.params.id;

        if (auth.authorized && auth.hasOwnProperty('type')) {
            // if already authorized
            $scope.init($scope.groupId);
        } else {
            $scope.$on('auth:authorizedChange', function () {
                // wait to auth
                if (auth.authorized && auth.hasOwnProperty('type')) {
                    $scope.init($scope.groupId);
                }
            });
        }
    }]);
};