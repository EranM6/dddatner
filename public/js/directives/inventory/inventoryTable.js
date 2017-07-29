dddatner.directive("inventoryTableDirective", [inventoryTableDirective]);

function inventoryTableDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/inventoryPartials/inventoryTable.html'
    };
}