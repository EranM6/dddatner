dddatner.directive("newTableModalDirective", [newTableModalDirective]);

function newTableModalDirective () {

    return {
        restrict: 'E',
        templateUrl: 'public/js/views/inventoryPartials/newTableModal.html'
    };
}