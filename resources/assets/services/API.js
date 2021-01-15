module.exports = function ($app) {
    $app.service('API', ['workflowLoader', 'workflowUsersLoader', 'groupsLoader', 'eventAPI', function (workflowLoader, workflowUsersLoader, groupsLoader, eventAPI) {
        return {
            workflow: workflowLoader,
            workflowUsers: workflowUsersLoader,
            groups: groupsLoader,
            event: eventAPI
        }
    }]);
};