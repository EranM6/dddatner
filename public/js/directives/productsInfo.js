dddatner.directive("productInfoDirective", [productInfoDirective]);

function productInfoDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/productsInfo.html'
    };
}