
dddatner.controller("homeCtrl", ["$scope", "location", "dbModel", "holder", "$timeout", "$interval", homeCtrl]);

function homeCtrl($scope, location, dbModel, holder, $timeout, $interval) {


    $scope.GOD = false;

    if (location.status === 200)
        $scope.GOD = (location.data).length;
        $scope.location = location.data;

    console.clear();

    $scope.setLocation = function(location){
        dbModel.setLocation({location : location});
    };

    var tick = function() {
        $scope.today = new Date();
    };
    tick();
    $interval(tick, 1000);

    $scope.toggleLoad = function(bool){
        $timeout(function () {
            $scope.loading = bool;
        }, 1000);
    };

    $scope.initDate = function(){
        holder.setSelectedMonth({month: $scope.today.getMonth() +1 , year: $scope.today.getFullYear()});
    };

    $scope.initDate();
}