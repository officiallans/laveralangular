'use strict';
import angular from 'angular';
window.$ = window.jQuery = require('jquery');
require('bootstrap');
window.moment = require('moment');

require('bootstrap/dist/css/bootstrap.css');
require('angular-bootstrap-calendar/dist/css/angular-bootstrap-calendar.css');
require('font-awesome/css/font-awesome.css');
require('angular-i18n/angular-locale_uk-ua.js');
var $app = angular
    .module('reporter', [
        require('angular-bootstrap-calendar'),
        require('angular-ui-bootstrap'),
        require('angular-ui-router'),
        require('angular-loading-bar'),
        require('angular-animate'),
        require('satellizer'),
        (function () {
            require('angular-drag-and-drop-lists');
            return 'dndLists';
        })(),
        require('oclazyload')
    ]);

require('./services')($app);

import components from './components';
components($app);

require('./application')($app, null);
require('./route')($app);
require('./httpAuthInterceptor').default($app);
require('./config')($app);