(function() {
    'use strict';

    var navWindow = angular.module('navWindow', ['ngMaterial'])
        .controller('AppCtrl', AppCtrl);

    function AppCtrl($scope) {
        $scope.currentNavItem = 'page1';
    }
})();

