
dddatner.controller("homeCtrl", ["$scope", "location", homeCtrl]);

function homeCtrl($scope, location) {
    $scope.GOD = false;

    if (location.status === 200)
        $scope.GOD = (location.data).length;
        $scope.location = location.data;

    console.clear();
}