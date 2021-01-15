import template from './template.html';
import styles from './style.pcss';
export default function ($app) {
    $app.component('userLink', {
        bindings: {
            user: '<'
        },
        template: template,
        controller: UserLink
    });
}

export class UserLink {
    constructor($scope, auth) {
        this.$scope = $scope;
        $scope.typeCode = auth.typeCode;
    }

    $onInit() {
        this.$scope.user = this.user;
    }

    $onChanges(changes) {
        if (changes.user) {
            this.$scope.user = changes.user.currentValue;
        }
    }
}
UserLink.$inject = ['$scope', 'auth'];