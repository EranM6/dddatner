dddatner.controller("vendorCtrl", ["$scope", "$stateParams", "$timeout", "dbModel", "data", vendorCtrl]);

function vendorCtrl($scope, $stateParams, $timeout, dbModel, data) {

    var place = $stateParams.codeName;
    $scope.name = $stateParams.displayName;
    $scope.section = 'ספקים';

    $scope.loading = false;
    $scope.activeVendorId = null;
    $scope.vendorNewInfo = null;
    $scope.newVendorInfo = null;
    var currentData = {
        info: null,
        products: null
    };
    $scope.editMode = false;

    $scope.toggleListItem = {item: -1};
    $scope.toggleTabItem = {item: 0};

    console.log(data);
    if (data.status === 200) {
        if (data.data.category === "vendors") {
            $scope.vendors = data.data.vendors;
        }
    }

    $scope.getVendor = function (id) {
        var mainTab = jQuery(document).find('.md-tab.ng-scope.ng-isolate-scope.md-ink-ripple')[0];
        $timeout(function () {
            angular.element(mainTab).triggerHandler('click');
        });
        $scope.toggleListItem = {item: id};
        $scope.activeVendorId = id;
        dbModel.getVendor(place, id)
            .then(function (data) {
                    $scope.vendorInfo = data.data.vendor;
                    currentData.info = id;
                    $timeout(function () {
                        $scope.loading = false;
                    }, 1000)
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            );
        $scope.loading = true;
    };

    $scope.addVendor = function () {
        $scope.newVendorInfo = {
            name: null,
            agent: {
                name: null,
                phoneNumber: null
            },
            driver: {
                name: null,
                phoneNumber: null
            },
            orders: {
                phoneNumber: null,
                minimum: null
            },
            discount: null
        };

        jQuery("#vendorModal").modal('show');
    };

    $scope.cancelAddVendor = function () {
        $scope.newVendorInfo = null;
        jQuery("#vendorModal").modal('hide');
    };

    $scope.saveAddVendor = function () {
        $scope.loading = true;
        dbModel.addVendor(place, $scope.newVendorInfo)
            .then(function (data) {
                    var newId = data.data.newId;
                console.log(newId);
                $scope.vendors[newId] = $scope.newVendorInfo.name;
                    $scope.newVendorInfo = null;
                    jQuery("#vendorModal").modal('hide');
                    console.log($scope.vendors);
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            )
            .finally(function () {
                    $timeout(function () {
                        $scope.loading = false;
                    }, 3000)
                }
            )
    };

    $scope.editVendor = function () {
        $scope.editMode = true;
        $scope.vendorNewInfo = angular.copy($scope.vendorInfo);
    };

    $scope.cancelEditVendor = function () {
        $scope.editMode = false;
        $scope.vendorNewInfo = null;
    };

    $scope.saveEditVendor = function () {
        $scope.loading = true;
        if (valid($scope.vendorInfo, $scope.vendorNewInfo)) {
            dbModel.updateVendor(place, $scope.vendorNewInfo)
                .then(function (status) {
                        $scope.vendorInfo = angular.copy($scope.vendorNewInfo);
                        $scope.vendorNewInfo = null;
                        $scope.editMode = false;
                    }
                )
                .catch(function (err) {
                        console.log(err);
                    }
                )
                .finally(function () {
                        $timeout(function () {
                            $scope.loading = false;
                        }, 3000)
                    }
                )
        }
    };

    $scope.getProductsByVendor = function () {

        if (!currentData.products || currentData.products !== $scope.activeVendorId) {
            var id = $scope.activeVendorId;
            dbModel.getProductsByVendor(place, id)
                .then(function (data) {
                        $scope.vendorProducts = data.data.products;
                        console.log(data);
                        currentData.products = id;
                        $scope.loading = false;
                    }
                )
                .catch(function (err) {
                        console.log(err);
                    }
                );
            $scope.loading = true;
        }
    };

    var valid = function (oldObject, newObject) {
        return true;
    }
}