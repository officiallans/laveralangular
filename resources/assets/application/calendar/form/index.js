module.exports = function ($app, $uibModal, calendarEvent) {
    $('body').css({'cursor': 'auto'});
    require('./style.pcss');
    var modalEdit = $uibModal.open({
        template: require('./index.html'),
        controller: 'CalendarEditController',
        controllerAs: 'FormWF',
        resolve: {
            event: function () {
                return calendarEvent;
            },
            ctrl: ['$q', '$ocLazyLoad', function ($q, $ocLazyLoad) {
                return $q(function (resolve) {
                    var module = require('./controller')($app);
                    $ocLazyLoad.load({name: module.name});
                    resolve(module.controller);
                });
            }]
        }
    });
}