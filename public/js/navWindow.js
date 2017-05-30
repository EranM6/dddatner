var navWindow = angular.module('navWindow', ['ngMaterial']);


navWindow.controller('AppCtrl', ["$scope", AppCtrl]);

function AppCtrl($scope) {
    $scope.currentNavItem = 'page1';
}


