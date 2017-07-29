dddatner.directive("entriesDirective", [entriesDirective]);

function entriesDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/inventoryPartials/entries.html'
    };
}