var types = require('../types.js');
module.exports = function ($app) {
    return $app.controller('CalendarEditController', ['$timeout', '$scope', '$state', '$uibModalInstance', '$uibModal', '$http', 'event', 'auth', 'API', function ($timeout, $scope, $state, $uibModalInstance, $uibModal, $http, event, auth, API) {
        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
            // $state.go($state.current, {}, {reload: true});
        };
        $scope.typeDisabled = false;
        $scope.needConfirm = false;
        $scope.planned = false;
        $scope.submitting = false;

        $scope.types = types;

        if (!event.new) {
            $scope.id = event.id;
            $scope.start_at = event.startsAt;
            $scope.end_at = event.endsAt;
            $scope.comment = event.comment;
            $scope.confirmed = event.confirmed;
            $scope.needConfirm = !$scope.confirmed && (moment($scope.start_at).format('YYYY-MM-DD') === moment().format('YYYY-MM-DD') || auth.typeCode >= 50);
            $scope.type = event.type;
            $scope.typeDisabled = true;
            var hour = Math.floor(event.duration / 60);
            var minute = event.duration - hour * 60;
            $scope.duration = moment().set('minute', minute).set('hour', hour).toDate();
        } else {
            $scope.duration = moment().set('minute', 0).set('hour', 8).toDate();
            if (event.planned) $scope.planned = event.planned;
            if (event.type) $scope.type = event.type;
            if (event.typeDisabled) $scope.typeDisabled = event.typeDisabled;
        }

        $scope.dateOptions = {
            startingDay: 1
        };

        $scope.calendarStart = {
            label: null,
            opened: false,
            show: true,
            required: true,
            min: null,
            open: function () {
                this.opened = true;
            }
        };
        $scope.calendarEnd = {
            label: null,
            opened: false,
            show: false,
            required: false,
            min: null,
            open: function () {
                this.opened = true;
            }
        };

        $scope.durationSetting = {
            max: moment().set('minute', 0).set('hour', 10).toDate()
        };

        $scope.$watch('start_at', function (value) {
            $scope.calendarEnd.min = moment(value).add(1, 'days').format("YYYY-MM-DD");
        });

        $scope.alerts = [];
        if ($scope.planned && $scope.type === 'working_off') {
            var alert = {
                'type': "success",
                'msg': 'Дані збережено'
            };
            var index = $scope.alerts.push(alert);
            alert = {
                'type': "info",
                'msg': 'Заплануйте відпрацювання'
            };
            $scope.alerts.push(alert);
            $timeout(function () {
                $scope.alerts.splice(index, 1);
            }, 3500);
        }

        $scope.$watch('type', function (type) {
            $scope.calendarStart.label = null;
            $scope.calendarEnd.label = null;
            $scope.calendarEnd.show = false;
            switch (type) {
                case 'sick_leave':
                case 'vacation': {
                    $scope.calendarStart.label = 'Дата початку';
                    $scope.calendarEnd.label = 'Дата кінця';
                    $scope.calendarEnd.show = true;
                    break;
                }
                case 'time_off':
                case 'working_off':
                default: {
                    $scope.calendarStart.label = 'Дата';
                }
            }
        });

        $scope.$watch('start_at', function () {
            $scope.eventForm.start_at.$setValidity("server", true);
        });
        $scope.$watch('end_at', function () {
            $scope.eventForm.end_at.$setValidity("server", true);
        });

        if (event.new) {
            $scope.submitLink = 'api/workflow';
            $scope.method = 'POST';
        } else {
            $scope.submitLink = 'api/workflow/' + $scope.id;
            $scope.method = 'PUT';
        }

        $scope.submit = function () {
            $scope.alerts = [];
            if ($scope.eventForm.$invalid) {
                if ($scope.type) {
                    $scope.alerts.push({
                        'type': "danger",
                        'msg': 'Заповніть коректно форму'
                    });
                } else {
                    $scope.alerts.push({
                        'type': "danger",
                        'msg': 'Оберіть тип'
                    });
                }
                return;
            }

            if ($scope.submitting) return;
            $scope.submitting = true;

            var start_at = moment($scope.start_at);
            var end_at;
            if ($scope.end_at) end_at = moment($scope.end_at);
            var duration = ($scope.duration.getHours() * 60 + $scope.duration.getMinutes());
            $http({
                url: baseUrl + $scope.submitLink,
                data: {
                    type: $scope.type,
                    comment: $scope.comment,
                    start_at: start_at && start_at.isValid() ? start_at.format("YYYY-MM-DD") : null,
                    end_at: end_at && end_at.isValid() ? end_at.format("YYYY-MM-DD") : null,
                    duration: duration
                },
                method: $scope.method
            }).then(function successCallback(response) {
                $uibModalInstance.close();
                $scope.submitting = false;
                if ($scope.type === 'time_off') {
                    var event = {
                        new: true,
                        type: 'working_off',
                        planned: true,
                        typeDisabled: true
                    };
                    require('./index.js')($app, $uibModal, event);
                } else {
                    var success = $uibModal.open({
                        template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Дані збережено</div>'
                    });
                    $state.go($state.current, {}, {reload: true});
                    $timeout(function () {
                        success.close();
                    }, 5000);
                }
            }, function errorCallback(response) {
                $scope.submitting = false;
                switch (response.status) {
                    case 403: {
                        $scope.alerts.push({
                            'type': "danger",
                            'msg': 'Помилка доступу'
                        });
                        break;
                    }
                    case 304: {
                        $scope.alerts.push({
                            'type': "info",
                            'msg': 'Немає змін'
                        });
                        break;
                    }
                    case 422: {
                        Object.keys(response.data).forEach(function (i) {
                            if (!response.data.hasOwnProperty(i)) return;
                            var error = response.data[i];
                            $scope.eventForm[i].$setValidity("server", false);
                            error.forEach(function (message) {
                                $scope.alerts.push({
                                    'type': "danger",
                                    'msg': message
                                });
                            });
                        });
                        break;
                    }
                }
            });
        };

        $scope.remove = function () {
            if (!$scope.id) return false;
            if ($scope.submitting) return;
            $scope.submitting = true;
            API.event.remove($scope.id).then(function successCallback(response) {
                    $uibModalInstance.close();
                    $scope.submitting = false;
                    var success = $uibModal.open({
                        template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Подію видалено</div>'
                    });
                    $state.go($state.current, {}, {reload: true});
                    $timeout(function () {
                        success.close();
                    }, 5000);
                }
                , function errorCallback(response) {
                    $uibModalInstance.close();
                    $scope.submitting = false;
                    var success = $uibModal.open({
                        template: '<div class="alert alert-warning" role="alert" style="margin-bottom: 0;">Щось пішло не так</div>'
                    });
                    $state.go($state.current, {}, {reload: true});
                    $timeout(function () {
                        success.close();
                    }, 5000);
                }
            )
        };

        $scope.confirm = function () {
            if (!$scope.id) return false;
            if ($scope.submitting) return;
            $scope.submitting = true;
            var start_at = moment($scope.start_at);
            var end_at;
            if ($scope.end_at) end_at = moment($scope.end_at);
            var duration = ($scope.duration.getHours() * 60 + $scope.duration.getMinutes());
            $http({
                url: baseUrl + $scope.submitLink,
                data: {
                    type: $scope.type,
                    comment: $scope.comment,
                    start_at: start_at.format("YYYY-MM-DD"),
                    end_at: end_at && end_at.isValid() ? end_at.format("YYYY-MM-DD") : null,
                    confirmed: true,
                    duration: duration,
                    _method: $scope.method
                },
                method: "POST"
            }).then(function successCallback(response) {
                $uibModalInstance.close();
                $scope.submitting = false;
                var success = $uibModal.open({
                    template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Дані збережено</div>'
                });
                $state.go($state.current, {}, {reload: true});
                $timeout(function () {
                    success.close();
                }, 5000);
            }, function errorCallback(response) {
                $uibModalInstance.close();
                $scope.submitting = false;
                var success = $uibModal.open({
                    template: '<div class="alert alert-warning" role="alert" style="margin-bottom: 0;">Щось пішло не так</div>'
                });
                $state.go($state.current, {}, {reload: true});
                $timeout(function () {
                    success.close();
                }, 5000);
            });
        }
    }])
};
