module.exports = function ($app) {
    require('./service')($app);
    return $app
        .controller('LoginController', ['$scope', '$auth', 'auth', '$state', function ($scope, $auth, auth, $state) {
            $scope.selectedForm = 'login';
            $scope.reset = function (e) {
                e.preventDefault();
                $state.go('auth.reset');
            };

            $scope.submitting = false;

            $scope.login = {
                alerts: [],
                submit: function () {
                    if ($scope.loginForm.$invalid) {
                        $scope.login.alerts.push({
                            'type': "danger",
                            'msg': 'Заповніть форму'
                        });
                        return false;
                    }
                    if ($scope.submitting) return;
                    $scope.submitting = true;

                    var credentials = {
                        email: $scope.login.email,
                        password: $scope.login.password
                    };
                    $auth.login(credentials).then(function () {
                        auth.isAuthenticated();
                        $scope.submitting = false;
                        $state.go('site.home', {}, {reload: true});
                    }).catch(function (data) {
                        $scope.submitting = false;
                        $scope.login.alerts.push({
                            'type': "danger",
                            'msg': 'Перевірте дані'
                        });
                    });
                }
            };
            $scope.register = {
                alerts: [],
                submit: function () {
                    if ($scope.registerForm.$invalid) {
                        $scope.register.alerts.push({
                            'type': "danger",
                            'msg': 'Заповніть форму'
                        });
                        return false;
                    }
                    if ($scope.submitting) return;
                    $scope.submitting = true;

                    var credentials = {
                        name: $scope.register.name,
                        email: $scope.register.email,
                        password: $scope.register.password
                    };
                    $auth.signup(credentials).then(function () {
                        $auth.login(credentials).then(function () {
                            $scope.submitting = false;
                            auth.isAuthenticated();
                            $state.go('site.home', {}, {reload: true});
                        });
                    }).catch(function (data) {
                        $scope.submitting = false;
                        $scope.login.alerts.push({
                            'type': "danger",
                            'msg': 'Щось пішло не так'
                        });
                    });
                }
            };

        }]);
    return 'LoginController';
};