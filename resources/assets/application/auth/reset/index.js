module.exports = function ($app, route) {
    if (route) {
        route
            .state('auth.reset', {
                url: "/reset",
                templateProvider: ['$q', function ($q) {
                    var deferred = $q.defer();
                    require.ensure([], function () {
                        require('./style.pcss');
                        deferred.resolve(require('./index.html'))
                    });
                    return deferred.promise;
                }],
                controller: 'ResetController',
                controllerAs: 'reset',
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
                },
                onEnter: ['$auth', '$state', function ($auth, $state) {
                    if ($auth.isAuthenticated()) $state.go('site.home', {});
                }]
            })
    }
};