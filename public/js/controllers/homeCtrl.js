
dddatner.controller("homeCtrl", ["$scope", "$state", "location", "dbModel", "holder", "$timeout", "$interval", "$route", homeCtrl]);

function homeCtrl($scope, $state, location, dbModel, holder, $timeout, $interval, $route) {


    $scope.GOD = false;

    $scope.GOD = (location.data).length;
    $scope.location = location.data;

    console.clear();

    $scope.setLocation = function(location){
        dbModel.setLocation({placeId : location});
        holder.setLocationId(location);
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
        holder.setToday({month: $scope.today.getMonth() +1 , year: $scope.today.getFullYear()});
    };

    $scope.initDate();

    $scope.getOut = function(){
        $state.go('exit');
        dbModel.getOut()
            .finally(function(){

            });
    };
}