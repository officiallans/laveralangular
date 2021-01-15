var types = require('../types');
module.exports = function ($app) {
    return $app
        .controller('ReportFormController', ['$timeout', '$scope', '$http', '$state', '$uibModal', function ($timeout, $scope, $http, $state, $uibModal) {
            $scope.reports = {};
            $scope.alerts = [];
            $scope.types = types;
            $scope.typeClass = {
                planned: 'info',
                in_progress: 'warning',
                solved: 'success',
                closed: 'danger'
            };

            $http({
                url: '/api/report/create',
                method: 'get'
            }).then(function (response) {
                var reports = response.data;
                reports.map(function (report) {
                    report.chained = true;
                    report.date = new Date(report.date);
                    return report;
                });

                var _reports = {};
                _.keys(types).forEach(function (type) {
                    _reports[type] = [];
                });

                var data = _(reports).groupBy('type').mapValues(function (reports) {
                    return _.map(reports, function (report) {
                        delete report.type;
                        return report;
                    })
                });
                _reports = _(_reports)
                    .extend(data.value())
                    .value();
                $scope.reports = _reports;
            });

            $scope.dateOptions = {
                startingDay: 1
            };

            $scope.add_report = function (e, type) {
                e.preventDefault();
                $scope.reports[type].push({
                    date: new Date(),
                    type: type
                });
            };

            $scope.drop = function (item) {
                item.date = new Date(item.date);
                return item;
            };

            $scope.onFocus = function (e) {
                e.target.closest(".task").setAttribute("draggable", "false");
                e.target.closest(".panel").setAttribute("draggable", "false");
            };
            $scope.onBlur = function (e) {
                e.target.closest(".task").setAttribute("draggable", "true");
                e.target.closest(".panel").setAttribute("draggable", "true");

            };

            $scope.delete_report = function (e, report, type) {
                e.preventDefault();
                var index = $scope.reports[type].indexOf(report);
                $scope.reports[type].splice(index, 1);
            };

            $scope.unchain_report = function (e, report) {
                e.preventDefault();
                report.chained = !report.chained;
            };

            $scope.submitting = false;
            $scope.submit = function (e) {
                e.preventDefault();

                var reports = _.cloneDeep($scope.reports);
                reports = _(reports).map(function (_reports, type) {
                    _reports.map(function (report) {
                        report.type = type;
                    });
                    return _reports;
                }).flatten().value();

                reports = reports.filter(function (report) {
                    if (!report.hasOwnProperty('id')) return true;

                    return report.chained;
                });
                reports.map(function (report) {
                    report.date = moment(report.date).format("YYYY-MM-DD");
                    return report;
                });

                if (!reports.length) {
                    $scope.alerts.push({
                        'type': "info",
                        'msg': 'Нічого надсилати'
                    });
                    return;
                }
                if ($scope.reportsForm.$invalid) {
                    $scope.alerts.push({
                        'type': "danger",
                        'msg': 'Заповніть коректно форму'
                    });
                    if ($scope.reportsForm.$invalid) {
                        angular.forEach($scope.reportsForm.reportForm.$error, function (field) {
                            angular.forEach(field, function (errorField) {
                                errorField.$setTouched();
                            })
                        });
                    }
                    return;
                }
                if ($scope.submitting) return;
                $scope.submitting = true;

                $http({
                    url: '/api/report',
                    method: 'post',
                    data: {
                        reports: reports
                    }
                }).then(function (response) {
                    var success = $uibModal.open({
                        template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Дані збережено</div>'
                    });
                    $state.go('site.reports.user', {id: 'my'});
                    $timeout(function () {
                        success.close();
                    }, 5000);
                    $scope.submitting = false;
                }).catch(function () {
                    $scope.submitting = false;
                });
            };
        }]);
};