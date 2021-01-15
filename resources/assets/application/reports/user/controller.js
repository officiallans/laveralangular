import types from '../types';
export class UserReports {
    constructor($scope, $http, $state) {
        $scope.types = types;
        $scope.latest_reports = null;
        $scope.user = null;

        $scope.date = new Date();

        $scope.dateOptions = {
            startingDay: 1
        };

        $scope.$watch('date', this.initDate.bind(this));
        this.$scope = $scope;
        this.$http = $http;
        this.$state = $state;
    }

    initDate() {
        let $http = this.$http;
        let $state = this.$state;
        let $scope = this.$scope;

        let date = $scope.date;
        date = moment(date).format('YYYY-MM-DD');
        let id = $state.params.id;
        $http({
            url: '/api/report/user/' + id + '?date=' + date,
            method: 'get'
        }).then(function (response) {
            $scope.latest_reports = response.data.reports;
            $scope.user = response.data.user;
        });
    }
}
UserReports.$inject = ['$scope', '$http', '$state'];

export default function ($app) {
    return $app.controller('UserReportsController', UserReports);
}