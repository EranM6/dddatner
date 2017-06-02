dddatner.directive("vendorModalDirective", [vendorModalDirective]);

function vendorModalDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/vendorsPartials/vendorModal.html'
    };
}