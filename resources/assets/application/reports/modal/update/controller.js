var types = require('../../types');
module.exports = function ($app) {
    return $app
        .controller('ReportUpdateController', ['$scope', '$http', '$state', 'report', '$uibModalInstance', '$uibModal', '$timeout', function ($scope, $http, $state, report, $uibModalInstance, $uibModal, $timeout) {
            $scope.report = {
                id: report.id,
                comment: report.comment,
                name: report.name,
                type: report.type,
                date: new Date(report.date)
            };

            $scope.types = types;
            
            $scope.dateOptions = {
                startingDay: 1
            };

            $scope.calendar = {
                opened: false,
                visible: true
            };
            $scope.showCalendar = function () {
                $scope.calendar.opened = true;
            };

            $scope.cancel = function () {
                $uibModalInstance.close();
            };

            $scope.submitting = false;
            $scope.update = function (e) {
                e.preventDefault();
                if ($scope.reportFormUpdate.$invalid) {
                    if ($scope.reportFormUpdate.$invalid) {
                        angular.forEach($scope.reportFormUpdate.$error, function (field) {
                            angular.forEach(field, function (errorField) {
                                errorField.$setTouched();
                            })
                        });
                    }
                    return;
                }

                if($scope.submitting) return;
                $scope.submitting = true;

                $http({
                    url: '/api/report',
                    method: 'post',
                    data: {
                        reports: [$scope.report],
                        date: moment($scope.report.date).format("YYYY-MM-DD")
                    }
                }).then(function (response) {
                    $uibModalInstance.close();
                    var success = $uibModal.open({
                        template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Дані збережено</div>'
                    });
                    $state.go('site.reports.index', null, {reload: true});
                    $timeout(function () {
                        $scope.submitting = false;
                        success.close();
                    }, 5000);
                }).catch(function () {
                    $scope.submitting = false;
                });
            }
        }]);
};