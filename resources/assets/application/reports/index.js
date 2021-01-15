import user from './user';

module.exports = function ($app, route) {
    require('./form')($app, route);
    user($app, route);
    if (route)
        route
            .state('site.reports', {
                url: "/reports",
                abstract: true,
                template:'<ui-view />'
            })
            .state('site.reports.index', {
                url: "/index",
                templateProvider: ['$q', function ($q) {
                    var deferred = $q.defer();
                    require.ensure([], function () {
                        require('./style.pcss');
                        deferred.resolve(require('./index.html'));
                    });
                    return deferred.promise;
                }],
                controllerAs: 'report_i',
                controller: 'ReportIndexController',
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