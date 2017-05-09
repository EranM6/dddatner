dddatner.controller("vendorCtrl", ["$scope", "$stateParams", "$timeout", "dbModel", "data", vendorCtrl]);

function vendorCtrl($scope, $stateParams, $timeout, dbModel, data) {

    var place = $stateParams.codeName;
    $scope.name = $stateParams.displayName;
    $scope.section = 'ספקים';

    $scope.loading = false;
    $scope.activeVendorId = null;
    $scope.vendorNewInfo = null;
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
        $timeout(function() {
            angular.element(mainTab).triggerHandler('click');
        });
        $scope.toggleListItem = {item: id};
        $scope.activeVendorId = id;
        dbModel.getVendor(place, id)
            .then(function (data) {

                    $scope.vendorInfo = data.data.vendor;
                    currentData.info = id;
                    $scope.loading = false;
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            );
        $scope.loading = true;
    };

    $scope.editVendor = function(){
        $scope.editMode = true;
        $scope.vendorNewInfo = angular.copy($scope.vendorInfo);
        console.log($scope.vendorNewInfo);
    };

    $scope.cancelEditVendor = function(){
        $scope.editMode = false;
        $scope.vendorNewInfo = null;
    };

    $scope.saveEditVendor = function(){
        $scope.editMode = false;
        $scope.vendorInfo = angular.copy($scope.vendorNewInfo);
        $scope.vendorNewInfo = null;
        $scope.loading = true;
        console.log($scope.vendorInfo);
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
    }
}