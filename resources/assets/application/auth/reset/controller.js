module.exports = function ($app) {
    return $app
        .controller('ResetController',  ['$scope', '$http', '$uibModal', '$timeout', '$state', function ($scope, $http, $uibModal, $timeout, $state) {
            $scope.alerts = [];
            $scope.remember = function(e) {
                e.preventDefault();
                $state.go('auth.login');
            };

            $scope.submitting = false;
            $scope.submit = function(e) {
                e.preventDefault();
                if ($scope.resetForm.$invalid) {

                    $scope.alerts.push({
                        'type': "danger",
                        'msg': 'Заповніть коректно форму'
                    });
                    return;
                }

                if($scope.submitting) return;
                $scope.submitting = true;

                $http({
                    url: '/api/user/profile/reset',
                    data: {
                        email: $scope.email
                    },
                    method: 'post'
                }).then(function () {
                    $scope.submitting = false;
                    var success = $uibModal.open({
                        template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Якщо ви зареєстровані в системі, то вам надіслано лист з новим паролем</div>'
                    });
                    $state.go('auth.login', {});
                    $timeout(function () {
                        success.close();
                    }, 5000);
                }, function () {
                    $scope.submitting = false;
                    $scope.alerts.push({
                        'type': "danger",
                        'msg': 'Відбулася помилка, перевірте дані'
                    });
                });
            }
        }]);
    return 'ResetController';
};