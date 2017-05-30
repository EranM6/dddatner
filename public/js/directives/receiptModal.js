dddatner.directive("receiptModalDirective", [receiptModalDirective]);

function receiptModalDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/receiptModal.html'
    };
}