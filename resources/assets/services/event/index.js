import remove from './remove.js';

export default function ($app) {
    $app.service('eventAPI', ['$http', function ($http) {
        return {
            remove: remove.bind(null, $http)
        };
    }]);
};