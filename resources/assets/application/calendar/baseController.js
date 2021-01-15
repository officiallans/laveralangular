var types = require('./types.js');
module.exports = function ($app) {
    return $app.controller('CalendarBaseController', ['$scope', '$state', '$http', 'auth', '$uibModal', 'API', function ($scope, $state, $http, auth, $uibModal, API) {
        $scope.parseFloat = parseFloat;
        $scope.minTypeCode = 100; // min typeCode to access all workflow data (main user type)
        $scope.types = types;

        groups: {
            $scope.groups = [];

            API.groups(function (groups) {
                $scope.groups = groups;
            });
        }


        users: {
            $scope.users = [];
            $scope.styles = null;
            $scope.$watch('events', $scope.infoUpdate);
            $scope.$watch('users', function (users) {
                $scope.styles = users.filter(function (user) {
                    return user.avatar;
                }).map(function (user) {
                    return '.cal-month-day:hover .event.user-' + user.id + ', .user-' + user.id + ' .event { background-image: url(' + user.avatar + ');}';
                }).join("\n");
            });

            $scope.info = {
                working_off: null,
                time_off: null,
                balance: null
            };

            $scope.infoUpdate = function () {
                $http({
                    url: baseUrl + 'api/workflow/info',
                    type: 'get'
                }).then(function successCallback(response) {
                    $scope.info = response.data;
                });
            };
            $scope.infoUpdate();

            $scope.getUsersData = function (groupId) {
                var errorCallback = function (response) {
                    if (response.status === 403) {
                        var error = $uibModal.open({
                            template: '<div class="alert alert-danger" role="alert" style="margin-bottom: 0;">Доступ заборонено!</div>'
                        });
                        $state.go('site.home');
                        $timeout(function () {
                            error.close();
                        }, 5000);
                    }
                };

                API.workflowUsers(groupId, function (group, users, author, groupType) {
                    $scope.users = users.map(function (user) {
                        user.checked = true;
                        return user;
                    });
                    $scope.group = group;
                    $scope.groupType = groupType;
                    $scope.author = author;
                }, errorCallback);
            };
            $scope.filterCheckAll = function (value) {
                $scope.users = $scope.users.map(function (user) {
                    user.checked = value;
                    return user;
                });
            };
        }

        events: {
            $scope.events = []; // all events
            $scope.eventsGroupByType = []; // event group by type


            $scope.eventEdited = function (calendarEvent) {
                require('./form')($app, $uibModal, calendarEvent);
            };
            $scope.eventDeleted = function (calendarEvent) {
                API.event.remove(calendarEvent).then(function successCallback(response) {
                    $scope.events.splice($scope.events.indexOf(calendarEvent), 1);
                });
            };
            $scope.newEvent = function (event) {
                if (!event) event = {};
                event.new = true;
                require('./form')($app, $uibModal, event);
            };
            calendar: {
                $scope.calendarView = 'month';
                $scope.calendarDate = new Date();
                $scope.calendarDatePrev = new Date();

                $scope.viewChangeClicked = function (nextView) {
                    // disable day and week View
                    return (nextView !== 'day' && !nextView === 'week');
                };
                $scope.initCalendar = function (group) {
                    if ($scope.hasOwnProperty('calendarDateWatcher'))
                        $scope.calendarDateWatcher();
                    $scope.calendarDateWatcher = $scope.$watch('calendarDate', $scope.updateCalendar.bind(null, group));

                    $scope.$watch('events + userFilter', $scope.filterUser);
                };
                $scope.updateCalendar = function (group) {
                    if ($scope.calendarDate === $scope.calendarDatePrev) return;
                    $scope.calendarDatePrev = $scope.calendarDate;

                    var errorCallback = function (error) {
                        if (error.status === 403) {
                            $uibModal.open({
                                template: '<div class="alert alert-danger" role="alert" style="margin-bottom: 0;">Доступ заборонено!</div>'
                            });
                            $state.go('site.home');
                        }
                    };
                    API.workflow($scope.calendarDate, group, function (events) {
                        events.forEach(function (event) {
                            var isDuplicate = false;
                            angular.forEach($scope.events, function (item, i) {
                                if (angular.equals(item.id, event.id)) {
                                    $scope.events[i] = event;
                                    isDuplicate = true;
                                }
                            });
                            if (!isDuplicate) {
                                $scope.events.push(event);
                            }
                        });
                    }, errorCallback);
                };
            }
        }

        $scope.init = function (group) {
            $scope.initCalendar(group);
            $scope.getUsersData(group);
        }
    }]);
}