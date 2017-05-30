dddatner.controller("vendorCtrl", ["$scope", "$stateParams", "$timeout", "dbModel", "holder", "data", vendorCtrl]);

function vendorCtrl($scope, $stateParams, $timeout, dbModel, holder, data) {

    // var place = $stateParams.codeName;
    $scope.name = $stateParams.displayName;
    $scope.section = 'ספקים';
    $scope.loading = true;

    $scope.vendors = {};

    var reset = function () {
        $scope.vendorNewInfo = null;
        $scope.newVendorInfo = null;
        $scope.editMode = false;
        $scope.addProduct = false;
        $scope.changeProduct = false;
        $scope.newProduct = null;
        $scope.productEdited = null;
        $scope.newProducts = [];
        $scope.editedProducts = {};
        $scope.addReceipt = false;
        $scope.changeReceipt = false;
        $scope.newReceipt = null;
        $scope.receiptEdited = null;
        $scope.newReceipts = [];
        $scope.editedReceipts = {};
        $scope.editCount = 0;
        $scope.countOfReceipts = 0;
        $scope.chargedReceipts = 0;
        $scope.refundedReceipts = 0;
        $scope.canClose = false;
        $scope.newReceiptEdited = null;
        $scope.receiptReEdited = null;
        $scope.selectedMonth = {};
        $scope.toggleListItem = {item: -1};
        $scope.toggleTabItem = {item: 0};
    };

    if (data.status === 200) {
        if (data.data.category === "vendors") {
            $scope.vendors = data.data.vendors;
        }
    }

    $scope.getVendor = function (id) {
        $scope.loading = true;
        reset();
        var mainTab = jQuery(document).find('.md-nav-item.ng-scope.ng-isolate-scope')[0];
        $timeout(function () {
            angular.element(mainTab).triggerHandler('click');
        });
        $scope.newProduct = null;
        $scope.newProducts = [];
        $scope.addProduct = false;
        $scope.editMode = false;
        $scope.vendorProducts = {};
        $scope.toggleListItem = {item: id};
        holder.setActiveVendorId(id);
        $scope.activeVendorId = id;

        dbModel.getVendor(id)
            .then(function (data) {
                    $scope.vendorInfo = data.data.vendor;
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            )
            .finally(function () {
                $scope.$parent.initDate();
                    $timeout(function () {
                        $scope.loading = false;
                    }, 1000);
                }
            );
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
        jQuery("#vendorModal").modal('hide')
            .on('hidden.bs.modal', function () {
                $scope.newVendorInfo = null;
            });
    };

    $scope.saveAddVendor = function () {
        $scope.loading = true;
        dbModel.addVendor($scope.newVendorInfo)
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
                    }, 1000);
                }
            );
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
        if ($scope.validChange($scope.vendorInfo, $scope.vendorNewInfo)) {
            dbModel.updateVendor($scope.vendorNewInfo)
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
                        }, 1000);
                    }
                );
        }
    };

    $scope.validChange = function (oldObject, newObject) {
        return !angular.equals(oldObject, newObject);
    };

    $scope.toggleLoad = function () {
        $scope.loading = !$scope.loading;
    };
}