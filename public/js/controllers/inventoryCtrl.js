dddatner.controller("inventoryCtrl", ["$scope", "$stateParams", "$timeout", "dbModel", "holder", "vendors", inventoryCtrl]);

function inventoryCtrl($scope, $stateParams, $timeout, dbModel, holder, vendors) {

    $scope.name = $stateParams.displayName;
    $scope.section = 'ספירות מלאי';
    $scope.loading = true;

    $scope.vendors = vendors.data.vendors;
}