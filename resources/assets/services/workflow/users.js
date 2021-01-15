module.exports = function ($app) {
    $app.service('workflowUsersLoader', ['$http', 'auth', function ($http, auth) {
        var minTypeCode = 100;
        return function (groupId, successCallback, errorCallback) {
            var isMain = auth.typeCode && auth.typeCode >= minTypeCode;
            var url, users, group, groupType, author;
            users = [];
            group = null;
            author = null;
            groupType = null;

            if (groupId === 'all') {
                url = baseUrl + 'api/user/profile/index';
            } else if (groupId === null) {
                if (isMain) {
                    url = baseUrl + 'api/user/profile/index';
                } else {
                    url = baseUrl + 'api/user/profile/my';
                }
            } else {
                url = baseUrl + 'api/user/group/' + groupId;
            }

            $http({
                url: url
            }).then(function (response) {
                var data = response.data;

                if (groupId === 'all' || (groupId === null && isMain)) {
                    users = data;
                    group = 'Усі події';
                    groupType = 'all';
                } else if (groupId === null) {
                    users = [data];
                    group = 'Мої події';
                    groupType = 'my';
                } else {
                    groupType = 'group';
                    group = data.name;
                    users = data.users;
                    author = data.author;
                }

                successCallback.call(null, group, users, author, groupType);

            }, errorCallback);
        }
    }]);
};