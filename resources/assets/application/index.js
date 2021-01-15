module.exports = function ($app, $stateProvider) {
    require('../style.pcss');
    require('./controller')($app);

    require('./auth')($app, $stateProvider);
    require('./home')($app, $stateProvider);
    require('./profile')($app, $stateProvider);
    require('./calendar')($app, $stateProvider);
    require('./user-group')($app, $stateProvider);
    require('./reports')($app, $stateProvider);
};