module.exports = function ($app) {
    $app.service('cache', ['$http', function ($http) {
        function getData(http, name, successCallback, failCallback) {
            $http(http).then(function () {
                var data = JSON.stringify({
                    response: arguments,
                    added: new Date()
                });
                localStorage.setItem(name, data);
                successCallback.apply(null, arguments)
            }, function () {
                failCallback.apply(null, arguments)
            });
        }

        if (typeof(Storage) !== "undefined" && 0) {
            return function (http, name, exp, successCallback, failCallback) {
                var data = JSON.parse(localStorage.getItem(name));
                if (data && moment(data['added']).add(exp) > moment()) {
                    var response = data.response;
                    response = _.toArray(response);
                    successCallback.apply(null, response);
                } else {
                    getData(http, name, successCallback, failCallback);
                }
            }
        } else {
            return function (http, name, exp, successCallback, failCallback) {
                $http(http).then(successCallback, failCallback);
            }
        }
    }]);
};