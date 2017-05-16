dddatner.directive("vendorInfoDirective", [vendorInfoDirective]);

function vendorInfoDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/vendorInfo.html'
    };
}