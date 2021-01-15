module.exports = function ($app) {
    return $app
        .controller('UserGroupController', ['$scope', '$http', 'groupsLoader', function ($scope, $http, groupsLoader) {
            $scope.groups = [];
            $scope.isLoaded = false;
            groupsLoader(function (groups) {
                $scope.groups = groups;
                $scope.isLoaded = true;
            });
        }]);
};