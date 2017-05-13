dddatner.directive("productModalDirective", [productModalDirective]);

function productModalDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/productModal.html'
    };
}