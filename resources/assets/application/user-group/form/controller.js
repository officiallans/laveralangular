module.exports = function ($app) {
    return $app.controller('CreateUserGroupController', ['$scope', '$state', '$http', '$uibModal', '$timeout', function ($scope, $state, $http, $uibModal, $timeout) {
        $scope.users = [];
        $scope.userFilter = '';
        $scope.usersCheckedLength = function () {
            return _.filter($scope.users, function (elem) {
                return elem.checked;
            }).length;
        };
        if ($state.params.id) {
            $scope.formUrl = baseUrl + 'api/user/group/' + $state.params.id;
            $scope.method = 'PUT';
            $scope.edit = true;
            $http({url: $scope.formUrl + '/edit'})
                .then(
                    function (response) {
                        $scope.title = response.data.name;
                        response.data.users.map(function (elem) {
                            $scope.users.push(elem);
                        });
                        $scope.title = response.data.name;
                    },
                    function (response) {
                        if (response.status === 403) {
                            $state.go('^.index');
                            var error = $uibModal.open({
                                template: '<div class="alert alert-danger" role="alert" style="margin-bottom: 0;">Доступ заборонено!</div>'
                            });
                            $timeout(function () {
                                error.close();
                            }, 5000);
                        }
                    }
                );
        } else {
            $scope.formUrl = baseUrl + 'api/user/group';
            $scope.method = 'POST';
            $scope.edit = false;
            $http({
                url: baseUrl + 'api/user/group/create'
            }).then(function (repsonse) {
                    repsonse.data.map(function (elem) {
                        $scope.users.push(elem);
                    });
                },
                function (response) {
                    if (response.status === 403) {
                        var error = $uibModal.open({
                            template: '<div class="alert alert-danger" role="alert" style="margin-bottom: 0;">Доступ заборонено!</div>'
                        });
                        $state.go('^.index');
                        $timeout(function () {
                            error.close();
                        }, 5000);
                    }
                });
        }

        $scope.submitting = false;
        $scope.save = function () {
            if ($scope.submitting) return;
            $scope.submitting = true;
            $http({
                url: $scope.formUrl,
                method: $scope.method,
                data: {
                    title: $scope.title,
                    users: $scope.users
                }
            }).then(function (repsonse) {
                $scope.submitting = false;
                $state.go('site.user-group.edit', {'id': repsonse.data.id}, {reload: true});
            });
        }
    }]);
};