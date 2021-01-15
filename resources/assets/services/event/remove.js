export default function ($http, event) {
    if(event.id) event = event.id;
    return $http({
        url: baseUrl + 'api/workflow/' + event,
        method: 'DELETE'
    });
};