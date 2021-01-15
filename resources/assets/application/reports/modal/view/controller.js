var types = require('../../types');
var modal = require('../index.js');
module.exports = function ($app) {
    return $app
        .controller('ReportViewController', ['$scope', '$http', '$state', 'report', '$uibModal', '$uibModalInstance', '$timeout', function ($scope, $http, $state, report, $uibModal, $uibModalInstance, $timeout) {
            $scope.comment = report.comment;
            $scope.name = report.name;
            $scope.fine = report.fine;
            $scope.updateReport = function (e) {
                e.preventDefault();
                modal($app, $uibModal, report, 'update');
                $uibModalInstance.close();
            };

            $scope.deleteReport = function (e) {
                e.preventDefault();
                $uibModalInstance.close();
                $http({
                    url: '/api/report/' + report.id,
                    method: 'delete'
                }).then(function (response) {
                    var success = $uibModal.open({
                        template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Успішно видалено</div>'
                    });
                    $state.go('site.reports.index', null, {reload: true});
                    $timeout(function () {
                        success.close();
                    }, 5000);
                });
            };

            $scope.close = function (e) {
                e.preventDefault();
                $uibModalInstance.close();
            };
        }]);
};