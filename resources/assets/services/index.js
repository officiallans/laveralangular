module.exports = function($app) {
    require('./cache')($app);
    require('./groups')($app);
    require('./workflow')($app);
    require('./workflow/users')($app);
    require('./event').default($app);
    require('./API')($app);
};