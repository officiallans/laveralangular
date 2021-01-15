var types = require('./types');
module.exports = function ($app) {
    $app.service('workflowLoader', ['$http', 'auth', function ($http, auth) {
        return function (date, group, successCallback, errorCallback) {
            var updateDateUrl, url, minTypeCode;
            minTypeCode = 100;
            updateDateUrl = 'api/workflow';
            if (group) {
                minTypeCode = 50;
                updateDateUrl = 'api/workflow/group/' + group;
            }
            url = baseUrl + updateDateUrl;

            var params = {
                date: moment(date).format("YYYY-MM-DD")
            };
            if (auth.typeCode && auth.typeCode >= minTypeCode) {
                params.all = true;
            }

            $http({
                url: url,
                params: params,
                type: 'get'
            }).then(function (response) {
                var data = response.data;
                var events = data.map(function (elem) {
                    var event = {};
                    var classes = [];
                    var dates = [];

                    event.id = elem.id;
                    event.type = elem.type;
                    classes.push(elem.type);
                    event.duration = elem.duration;
                    event.startsAt = new Date(elem.start_at);
                    event.start = moment(event.startsAt).format('DD.MM.YYYY');
                    dates.push(event.start);
                    if (elem.end_at) {
                        event.endsAt = new Date(elem.end_at);
                        event.end = moment(event.endsAt).format('DD.MM.YYYY');
                        dates.push(event.end);
                    }

                    classes.push('user-' + elem.author_id);
                    event.comment = elem.comment;
                    event.confirmed = elem.confirmed;
                    if (!event.confirmed) classes.push(event.type + '-partial');
                    if (event.duration !== 480 && ['vacation', 'sick_leave'].indexOf(elem.type) < 0)  classes.push(event.type + '-not-full');
                    event.draggable = false;
                    event.editable = elem.hasAccess;
                    event.deletable = elem.hasAccess;
                    event.author = elem.author;
                    event.title = types[event.type] + ' ' + event.author.name;
                    event.incrementsBadgeTotal = false;

                    event.cssClass = classes.join(' ');
                    event.dates = dates.join(' - ');
                    return event;
                });

                successCallback.call(null, events);
            }, errorCallback);
        }
    }]);
};