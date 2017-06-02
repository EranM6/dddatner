dddatner.directive("newProductDirective", [newProductDirective]);

function newProductDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/newProductScheme.html'
    };
}