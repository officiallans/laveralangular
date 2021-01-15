// Calendar - Index
module.exports = function ($app, route) {
    require('./group')($app, route);
    require('./filters/userChecked')($app);
    if (route) {
        route
            .state('site.calendar', {
                url: "/calendar",
                abstract: true,
                template: '<ui-view />'
            })
            .state('site.calendar.index', {
                url: "/index",
                templateProvider: ['$q', function ($q) {
                    var deferred = $q.defer();
                    require.ensure([], function () {
                        require('./style.pcss');
                        deferred.resolve(require('./index.html'))
                    });
                    return deferred.promise;
                }],
                controller: 'CalendarController',
                controllerAs: 'cld',
                resolve: {
                    ctrl: ['$q', '$ocLazyLoad', function ($q, $ocLazyLoad) {
                        var deferred = $q.defer();
                        require.ensure([], function () {
                            var controllers = [
                                require('./baseController')($app),
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