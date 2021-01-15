var types = require('./types.js');
module.exports = function ($app) {
    return $app.controller('CalendarController', ['$scope', '$http', 'auth', '$controller', function ($scope, $http, auth, $controller) {
        angular.extend(this, $controller('CalendarBaseController', {$scope: $scope}));

        if (auth.authorized && auth.hasOwnProperty('type')) {
            // if already authorized
            $scope.init(null);
        } else {
            $scope.$on('auth:authorizedChange', function () {
                // wait to auth
                if (auth.authorized && auth.hasOwnProperty('type')) {
                    $scope.init(null);
                }
            });
        }
    }]);
};