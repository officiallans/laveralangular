export default function ($app) {
    return $app.factory('authInterceptor', ['$q', '$injector', function ($q, $injector) {
        let error;
        return {
            responseError: function (response) {
                if (response.data.error === 'token_not_provided' || response.data.error === 'token_expired' || response.data.error === 'token_invalid' || response.data.error === 'token_absent') {
                    if (!error) {
                        error = $injector.get('$uibModal').open({
                            template: '<div class="alert alert-danger" role="alert" style="margin-bottom: 0;">Сесія завершилась</div>'
                        });
                        $injector.get('$auth').logout();
                        $injector.get('auth').isAuthenticated();
                        $injector.get('$timeout')(function () {
                            error.close();
                        }, 5000);
                    }
                }
                return $q.reject(response);
            }
        }
    }]);
}
