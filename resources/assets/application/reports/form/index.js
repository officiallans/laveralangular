module.exports = function ($app, route) {
    if (route)
        route
            .state('site.reports.form', {
                url: "/form",
                templateProvider: ['$q', function ($q) {
                    var deferred = $q.defer();
                    require.ensure([], function () {
                        require('./style.pcss');
                        deferred.resolve(require('./index.html'));
                    });
                    return deferred.promise;
                }],
                controllerAs: 'report_f',
                controller: 'ReportFormController',
                resolve: {
                    ctrl: ['$q', '$ocLazyLoad', function ($q, $ocLazyLoad) {
                        var deferred = $q.defer();
                        require.ensure([], function () {
                            var module = require('./controller')($app);
                            $ocLazyLoad.load({name: module.name});
                            deferred.resolve(module.controller);
                        });
                        return deferred.promise;
                    }]
                }
            });
};