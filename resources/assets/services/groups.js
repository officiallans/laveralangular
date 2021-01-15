module.exports = function ($app) {
    $app.service('groupsLoader', ['$http', function ($http) {
        var groupsUrl = baseUrl + 'api/user/group';
        var groups = null;
        return function (callback) {
            if (groups) {
                callback.call(null, groups);
            } else {
                $http({
                    url: groupsUrl
                }).then(function (response) {
                    groups = response.data;
                    callback.call(null, response.data);
                });
            }
        }
    }]);
};