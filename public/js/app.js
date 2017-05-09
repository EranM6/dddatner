var dddatner = angular.module('dddatner', ['ngRoute', 'ui.router', 'ngMaterial']);

dddatner.filter('capitalize', function () {
    return function (input, all) {
        var reg = (all) ? /([^\W_]+[^\s-]*) */g : /([^\W_]+[^\s-]*)/;
        return (!!input) ? input.replace(reg, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        }) : '';
    }
});


dddatner.config(["$locationProvider", "$stateProvider", "$urlRouterProvider", routeConfiguration]);

function routeConfiguration($locationProvider, $stateProvider, $urlRouterProvider) {

    $urlRouterProvider.otherwise('/dddatner/');

    $locationProvider.html5Mode({
        enabled: true,
        requireBase: false
    });

    $stateProvider
        .state('home', {
            abstract: true,
            resolve: {
                location: ['$state', 'dbModel', function ($state, dbModel) {
                    return dbModel.getLocation();
                }]
            },
            controller: "homeCtrl",
            templateUrl: "./public/js/views/home.html"
        })
        .state('home.index', {
            url: "/dddatner/",
            controller: "homeCtrl"
        })
        .state('home.place', {
            abstract: true,
            controller: "placeCtrl",
            templateUrl: "./public/js/views/place.html"
        })
        .state('home.place.vendors', {
            resolve: {
                data: ['$state','$stateParams', 'dbModel', function ($state, $stateParams, dbModel) {
                    var place = $stateParams.codeName;
                    return dbModel.getVendors(place);
                }]
            },
            controller: "vendorCtrl",
            params: {
                codeName: {
                    value: null
                },
                displayName: {
                    value: null
                }
            },
            url: "/dddatner/",
            templateUrl: "./public/js/views/vendors.html"
        })
        .state('home.place.inventory', {
            resolve: {
                data: ['$state','$stateParams', 'dbModel', function ($state, $stateParams, dbModel) {
                    var place = $stateParams.codeName;
                    return dbModel.getVendors(place);
                }]
            },
            controller: "vendorCtrl",
            params: {
                codeName: {
                    value: null
                },
                displayName: {
                    value: null
                }
            },
            url: "/dddatner/",
            templateUrl: "./public/js/views/vendors.html"
        })
        .state('home.place.productTree', {
            resolve: {
                data: ['$state','$stateParams', 'dbModel', function ($state, $stateParams, dbModel) {
                    var place = $stateParams.codeName;
                    return dbModel.getVendors(place);
                }]
            },
            controller: "vendorCtrl",
            params: {
                codeName: {
                    value: null
                },
                displayName: {
                    value: null
                }
            },
            url: "/dddatner/",
            templateUrl: "./public/js/views/vendors.html"
        })
}
