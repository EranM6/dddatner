dddatner.directive("newReceiptDirective", [newReceiptDirective]);

function newReceiptDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/newReceiptScheme.html'
    };
}