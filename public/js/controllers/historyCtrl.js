dddatner.controller("historyCtrl", ["$scope", "$timeout", "history", historyCtrl]);

function historyCtrl($scope, $timeout, history) {

    $scope.records = history.data.records ? history.data.records : {};
    $scope.years = Object.keys($scope.records).reverse();
    $timeout(function () {
        $scope.$parent.toggleLoad();
    }, 1000);
}