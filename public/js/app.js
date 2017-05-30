var dddatner = angular.module('dddatner', ['ngRoute', 'ui.router', 'ngAnimate', 'navWindow']);

dddatner.filter('capitalize', function () {
    return function (input, all) {
        var reg = (all) ? /([^\W_]+[^\s-]*) */g : /([^\W_]+[^\s-]*)/;
        return (!!input) ? input.replace(reg, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        }) : '';
    };
});

dddatner.filter('monthName', function() {
    return function (monthNumber) {
        var monthNames = [ 'ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',
            'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר' ];
        return monthNames[monthNumber - 1];
    };
});

dddatner.filter('orderObjectBy', function() {
    return function(items, field, reverse) {
        var filtered = [];
        angular.forEach(items, function(item) {
            filtered.push(item);
        });
        filtered.sort(function (a, b) {
            return (a[field] > b[field] ? 1 : -1);
        });
        if(reverse) filtered.reverse();
        return filtered;
    };
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
                    .state('home.place.vendors.info', {
                        template: '<vendor-info-directive></vendor-info-directive>'
                    })
                    .state('home.place.vendors.products', {
                        resolve: {
                            products: ['holder','dbModel', function (holder,dbModel) {
                                var id = holder.getActiveVendorId();
                                return dbModel.getProductsByVendor(id);
                            }]
                        },
                        controller: productsCtrl,
                        template: '<product-info-directive></product-info-directive>'
                    })
                    .state('home.place.vendors.receipts', {
                        resolve: {
                            receipts: ['holder','dbModel', function (holder,dbModel) {
                                var id = holder.getActiveVendorId();
                                var selectedMonth = holder.getSelectedMonth();
                                return dbModel.getReceiptsByVendor(id, selectedMonth);
                            }]
                        },
                        controller: receiptsCtrl,
                        template: '<receipt-info-directive></receipt-info-directive>'
                    })
                    .state('home.place.vendors.history', {
                        resolve: {
                            history: ['holder','dbModel', function (holder,dbModel) {
                                var id = holder.getActiveVendorId();
                                return dbModel.getHistory(id);
                            }]
                        },
                        controller: historyCtrl,
                        template: '<history-directive></history-directive>'
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
                });
}
