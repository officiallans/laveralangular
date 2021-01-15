module.exports = function ($app, route) {
    if (route) {
        route.state('site.user-group.create', {
            controller: 'CreateUserGroupController',
            url: '/create',
            templateProvider: ['$q', function ($q) {
                var deferred = $q.defer();
                require.ensure([], function () {
                    require('./style.pcss');
                    deferred.resolve(require('./index.html'))
                });
                return deferred.promise;
            }],
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
        route.state('site.user-group.edit', {
            controller: 'CreateUserGroupController',
            url: '/edit/:id',
            templateProvider: ['$q', function ($q) {
                var deferred = $q.defer();
                require.ensure([], function () {
                    require('./style.pcss');
                    deferred.resolve(require('./index.html'))
                });
                return deferred.promise;
            }],
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
    }
}