var moment = require('moment');
var types = require('./types');
var modal = require('./modal');
module.exports = function ($app) {
    return $app
        .controller('ReportIndexController', ['$scope', '$state', '$http', '$uibModal', '$timeout', function ($scope, $state, $http, $uibModal, $timeout) {
            $scope.loading = false;
            $scope.types = types;
            $scope.type = 'all';
            $scope.search = '';
            $scope.reports = [];

            $scope.pagination = {
                totalItems: null,
                perPage: null,
                currentPage: null,
                numPages: null
            };
            $scope.pageInit = function () {
                if($scope.loading) return;
                $scope.loading = true;
                var url;
                url = '/api/report?search=' + $scope.search + '&type=' + $scope.type;
                if ($scope.pagination.currentPage) {
                    url += '&page=' + $scope.pagination.currentPage;
                }
                $http({
                    url: url,
                    method: 'get'
                }).then(function (response) {
                    $scope.pagination.totalItems = response.data.total;
                    $scope.pagination.currentPage = response.data.current_page;
                    $scope.pagination.perPage = response.data.per_page;

                    $scope.reports = response.data.data;

                    $scope.reports.map(function (report) {
                        report.fine = {};
                        report.fine.date = moment(report.date).format('DD.MM.YYYY');
                        report.fine.type = types[report.type];
                        return report;
                    });

                    $scope.loading = false;
                });
            };

            $scope.pageInit();

            $scope.$watch('type', function () {
                if(!$scope.loading) $scope.pageInit();
            });


            $scope.showReport = function (e, report) {
                e.preventDefault();
                modal($app, $uibModal, report, 'view');
            };

            $scope.updateReport = function (e, report) {
                e.preventDefault();
                modal($app, $uibModal, report, 'update');
            };

            $scope.deleteReport = function (e, report) {
                e.preventDefault();
                $http({
                    url: '/api/report/' + report.id,
                    method: 'delete'
                }).then(function () {
                    var success = $uibModal.open({
                        template: '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">Успішно видалено</div>'
                    });
                    $state.go('site.reports.index', null, {reload: true});
                    $timeout(function () {
                        success.close();
                    }, 5000);
                });
            }
        }]);
};
