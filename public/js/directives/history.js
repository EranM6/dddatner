dddatner.directive("historyDirective", [historyDirective]);

function historyDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/history.html'
    };
}