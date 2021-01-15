module.exports = function ($app) {
    $app.filter('userChecked', function () {
        return function (events, users) {
            return events.filter(function (event) {
                return users.filter(function (user) {
                        return user.checked;
                    }).map(function (user) {
                        return user.id;
                    }).indexOf(event.author.id) >= 0;
            });
        };
    });
};