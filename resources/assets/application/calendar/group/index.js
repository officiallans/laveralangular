module.exports = function ($app, route) {
    if (route) {
        route
            .state('site.calendar.group', {
                url: '/group/:id',
                controller: 'CalendarGroupController',
                controllerAs: 'cld',
                templateProvider: ['$q', function ($q) {
                    var deferred = $q.defer();
                    require.ensure([], function () {
                        require('./style.pcss');
                        deferred.resolve(require('../index.html'))
                    });
                    return deferred.promise;
                }],
                resolve: {
                    ctrl: ['$q', '$ocLazyLoad', function ($q, $ocLazyLoad) {
                        var deferred = $q.defer();
                        require.ensure([], function () {
                            var controllers = [
                                require('../baseController')($app),
                                require('./controller')($app)
                            ];
                            controllers.forEach(function (controller) {
                                $ocLazyLoad.load({name: controller.name});
                                deferred.resolve(controller.controller);
                            });
                        });
                        return deferred.promise;
                    }]
                }
            });
    }
};