module.exports = function ($app, route) {
    require('./reset')($app, route);
    if (route) {
        route.state('auth', {
                url: "/auth",
                abstract: true,
                template: require('../../main.html')
            })
            .state('auth.login', {
                url: "/login",
                templateProvider: ['$q', function ($q) {
                    var deferred = $q.defer();
                    require.ensure([], function () {
                        require('./style.pcss');
                        deferred.resolve(require('./index.html'))
                    });
                    return deferred.promise;
                }],
                controller: 'LoginController',
                controllerAs: 'auth',
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
            .state('auth.logout', {
                url: "/logout",
                onEnter: ['$auth', 'auth', '$state', function ($auth, auth, $state) {
                    $auth.logout();
                    auth.isAuthenticated();
                    $state.go('auth.login', {});
                }]
            });
    }
};