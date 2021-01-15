module.exports = function ($app, $uibModal, report, type) {
    $('body').css({'cursor': 'auto'});
    require('./view/style.pcss')
    switch (type) {
        case 'view': {
            $uibModal.open({
                template: require('./view/index.html'),
                controller: 'ReportViewController',
                resolve: {
                    report: function () {
                        return report;
                    },
                    ctrl: ['$q', '$ocLazyLoad', function ($q, $ocLazyLoad) {
                        return $q(function (resolve) {
                            var module = require('./view/controller')($app);
                            $ocLazyLoad.load({name: module.name});
                            resolve(module.controller);
                        });
                    }]
                }
            });
            break;
        }
        case 'update': {
            $uibModal.open({
                template: require('./update/index.html'),
                controller: 'ReportUpdateController',
                resolve: {
                    report: function () {
                        return report;
                    },
                    ctrl: ['$q', '$ocLazyLoad', function ($q, $ocLazyLoad) {
                        return $q(function (resolve) {
                            var module = require('./update/controller')($app);
                            $ocLazyLoad.load({name: module.name});
                            resolve(module.controller);
                        });
                    }]
                }
            });
            break;
        }
        default: alert('error');
    }
};