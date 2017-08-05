var dddatner = angular.module('dddatner', ['ngRoute', 'ui.router', 'ngAnimate', 'ngMaterial', 'ui.bootstrap']);

dddatner.filter('capitalize', function () {
    return function (input, all) {
        var reg = (all) ? /([^\W_]+[^\s-]*) */g : /([^\W_]+[^\s-]*)/;
        return (!!input) ? input.r(reg, function (txt) {
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

dddatner.filter('dayName', function() {
    return function (dayNumber) {
        var dayNames = [ 'ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי',
            'שבת'];
        return dayNames[dayNumber -1];
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

dddatner.filter('sumOfValue', function () {
    return function (data, key) {
        console.log(angular.isUndefined(data) || angular.isUndefined(key));
        if (angular.isUndefined(data) || angular.isUndefined(key))
            return 0;
        var sum = 0;
        angular.forEach(data, function (value) {
            sum = sum + parseInt(value[key], 10);
        });
        return sum;
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
                .state('home.vendors', {
                    resolve: {
                        vendors: ['$state', 'dbModel', function ($state, dbModel) {
                            return dbModel.getVendors();
                        }]
                    },
                    controller: "vendorCtrl",
                    params: {
                        displayName: {
                            value: null
                        }
                    },
                    url: "/dddatner/",
                    templateUrl: "./public/js/views/vendors.html"
                })
                    .state('home.vendors.info', {
                        template: '<vendor-info-directive></vendor-info-directive>'
                    })
                    .state('home.vendors.products', {
                        resolve: {
                            products: ['holder','dbModel', function (holder,dbModel) {
                                var id = holder.getActiveVendorId();
                                return dbModel.getProductsByVendor(id);
                            }]
                        },
                        controller: productsCtrl,
                        template: '<product-info-directive></product-info-directive>'
                    })
                    .state('home.vendors.receipts', {
                        resolve: {
                            receipts: ['holder','dbModel', function (holder,dbModel) {
                                var id = holder.getActiveVendorId();
                                var selectedMonth = holder.getToday();
                                return dbModel.getReceiptsByVendor(id, selectedMonth);
                            }]
                        },
                        controller: receiptsCtrl,
                        template: '<receipt-info-directive></receipt-info-directive>'
                    })
                    .state('home.vendors.history', {
                        resolve: {
                            history: ['holder','dbModel', function (holder,dbModel) {
                                var id = holder.getActiveVendorId();
                                return dbModel.getHistory(id);
                            }]
                        },
                        controller: historyCtrl,
                        template: '<history-directive></history-directive>'
                    })
                .state('home.inventory', {
                    resolve: {
                        vendors: ['$state', 'dbModel', function ($state, dbModel) {
                            return dbModel.getVendors();
                        }],
                        entries: ['$state', 'dbModel', function ($state, dbModel) {
                            return dbModel.getEntries();
                        }]
                    },
                    controller: "inventoryCtrl",
                    params: {
                        displayName: {
                            value: null
                        }
                    },
                    url: "/dddatner/",
                    templateUrl: "./public/js/views/inventory.html"
                })
                    .state('home.inventory.table', {
                        resolve: {
                            vendors: ['$state', 'dbModel', function ($state, dbModel) {
                                return dbModel.getVendors();
                            }]
                        },
                        controller: "inventoryCtrl",
                        params: {
                            date: {
                                value: null
                            }
                        },
                        template: "<inventory-table-directive></inventory-table-directive>"
                    })
                .state('home.productTree', {
                    resolve: {
                        data: ['$state', 'dbModel', function ($state, dbModel) {
                            return dbModel.getVendors();
                        }]
                    },
                    controller: "vendorCtrl",
                    params: {
                        displayName: {
                            value: null
                        }
                    },
                    url: "/dddatner/",
                    templateUrl: "./public/js/views/vendors.html"
                })
        .state('exit', {
            controller: "homeCtrl",
            templateUrl: "./public/js/views/exit.html"
        });

}
