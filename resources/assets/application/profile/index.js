module.exports = function ($app, route) {
    if (route)
        route.state('site.profile', {
                url: "/profile",
                abstract: true,
                template: '<ui-view />'
            })
            .state('site.profile.index', {
                    url: "/index",
                    templateProvider: ['$q', function ($q) {
                        var deferred = $q.defer();
                        require.ensure([], function () {
                            deferred.resolve(require('./index.html'))
                        });
                        return deferred.promise;
                    }],
                    controllerAs: 'profile',
                    controller: 'ProfileController',
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
                }
            )
}
;
