module.exports = function ($app) {
    return $app.service('auth', ['$auth', '$rootScope', '$http', function ($auth, $rootScope, $http) {
        this.isAuthenticated = function () {
            this.authorized = $auth.isAuthenticated();
            if (this.authorized) {
                $http({
                    url: baseUrl + 'api/user/profile/my'
                }).then(function (response) {
                    this.type = response.data.type;
                    switch (this.type) {
                        case 'main':
                        {
                            this.typeCode = 100;
                            break;
                        }
                        case 'manager':
                        {
                            this.typeCode = 50;
                            break;
                        }
                        default:
                        case 'participant':
                        {
                            this.typeCode = 10;
                            break;
                        }
                    }
                    $rootScope.$broadcast('auth:authorizedChange');
                }.bind(this));
            } else {
                this.type = 'guest';
                this.typeCode = 0;
                $rootScope.$broadcast('auth:authorizedChange');
            }
        }.bind(this);

        this.isAuthenticated();
    }]);
}