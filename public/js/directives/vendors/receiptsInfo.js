dddatner.directive("receiptInfoDirective", [receiptInfoDirective]);

function receiptInfoDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/receiptInfo.html'
    };
}