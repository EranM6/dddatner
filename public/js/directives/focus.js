dddatner.directive('focusMe', [focusFunc]);

function focusFunc() {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            scope.$watch(attrs.focusMe, function(value) {
                if(value === true) {
                    element[0].focus();
                    scope[attrs.focusMe] = false;
                }
            });
        }
    };
}