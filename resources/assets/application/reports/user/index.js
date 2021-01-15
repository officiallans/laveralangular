export default function ($app, route) {
    if (route)
        route
            .state('site.reports.user', {
                url: '/user/:id',
                templateProvider: ['$q', function ($q) {
                    var deferred = $q.defer();
                    require.ensure([], function () {
                        deferred.resolve(require('./index.html'));
                    });
                    return deferred.promise;
                }],
                controllerAs: 'URC',
                controller: 'UserReportsController',
                resolve: {
                    ctrl: ['$q', '$ocLazyLoad', function ($q, $ocLazyLoad) {
                        var deferred = $q.defer();
                        require.ensure([], function () {
                            var module = require('./controller').default($app);
                            $ocLazyLoad.load({name: module.name});
                            deferred.resolve(module.controller);
                        });
                        return deferred.promise;
                    }]
                }
            });
}