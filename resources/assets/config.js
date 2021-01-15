module.exports = function ($app) {
    $app
        .config(['calendarConfig',
            function (calendarConfig) {
                var moment = require('moment');
                moment.locale('uk');
                calendarConfig.dateFormatter = 'moment';
                calendarConfig.displayEventEndTimes = true;
                calendarConfig.allDateFormats.moment.date.time = 'L';
                calendarConfig.allDateFormats.moment.date.datetime = 'L';
            }])
        .config(['$httpProvider', function ($httpProvider) {
            $httpProvider.interceptors.push('authInterceptor');
        }])
        .config(['cfpLoadingBarProvider', function (cfpLoadingBarProvider) {
            cfpLoadingBarProvider.includeBar = false;
            cfpLoadingBarProvider.spinnerTemplate = '<div class="loader"><i class="fa fa-spin fa-spinner"></i></div>';
            cfpLoadingBarProvider.latencyThreshold = 1000;
        }]);
};
