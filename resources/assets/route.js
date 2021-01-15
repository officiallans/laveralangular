module.exports = function ($app) {
    $app.config(['$urlRouterProvider', '$locationProvider', '$authProvider', '$stateProvider',
        function ($urlRouterProvider, $locationProvider, $authProvider, $stateProvider) {
            $locationProvider
                .html5Mode({enabled:true, requireBase:false})

            $authProvider.loginUrl = baseUrl + 'api/auth/login';
            $authProvider.signupUrl = baseUrl + 'api/auth/signup';
            $urlRouterProvider.otherwise('/index');
            $stateProvider
                .state('site', {
                    url: "",
                    abstract: true,
                    template: require('./main.html')
                });
            require('./application')($app, $stateProvider);
        }
    ]);
};
