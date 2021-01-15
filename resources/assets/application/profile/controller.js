module.exports = function ($app) {

    $app.directive('fileModel', ['$parse', function ($parse) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                var model = $parse(attrs.fileModel);
                var modelSetter = model.assign;

                element.bind('change', function(){
                    scope.$apply(function(){
                        modelSetter(scope, element[0].files[0]);
                    });
                });
            }
        };
    }]);


    return $app
        .controller('ProfileController', ['$scope', '$http', '$uibModal', '$timeout', '$state', function ($scope, $http, $uibModal, $timeout, $state) {
            $scope.submitting = false;
            $http({
                'url': '/api/user/profile/my'
            }).then(function (response) {
                $scope.loaded = true;
                $scope.name = response.data.name;
                $scope.email = response.data.email;
                $scope.options = response.data.options;
                $scope.avatar_url = response.data.avatar;
            });

            $scope.$watchGroup(['password', 'password_confirmation'], function () {
                $scope.profileForm.password.$setValidity("server", true);
                $scope.profileForm.password_confirmation.$setValidity("server", true);
            });
            $scope.submit = function (e) {
                e.preventDefault();
                $scope.alerts = [];
                if ($scope.profileForm.$invalid) {

                    $scope.alerts.push({
                        'type': "danger",
                        'msg': 'Заповніть коректно форму'
                    });
                    return;
                }

                if($scope.submitting) return;
                $scope.submitting = true;

                var fd = new FormData();
                fd.append('name', $scope.name);
                fd.append('password', $scope.password ? $scope.password : '');
                fd.append('password_confirmation', $scope.password_confirmation ? $scope.password_confirmation : '');
                fd.append('options', JSON.stringify($scope.options));
                fd.append('avatar', $scope.avatar ? $scope.avatar : '');
                $http.post('/api/user/profile/update', fd, {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                }).then(function (response) {
                    $scope.submitting = false;
                    var success = $uibModal.open({
                        template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Дані збережено</div>'
                    });
                    $state.go($state.current, {}, {reload: true});
                    $timeout(function () {
                        success.close();
                    }, 5000);
                }, function (response) {
                    $scope.submitting = false;
                    Object.keys(response.data).forEach(function (i) {
                        if (!response.data.hasOwnProperty(i)) return;

                        var error = response.data[i];

                        if ($scope.profileForm[i]) $scope.profileForm[i].$setValidity("server", false);
                        error.forEach(function (name) {
                            var message = name;
                            $scope.alerts.push({
                                'type': "danger",
                                'msg': message
                            });
                        });
                    });
                });
            }
        }]);
};