dddatner.controller("vendorCtrl", ["$scope", "$stateParams", "$timeout", "dbModel", "data", vendorCtrl]);

function vendorCtrl($scope, $stateParams, $timeout, dbModel, data) {

    var place = $stateParams.codeName;
    $scope.name = $stateParams.displayName;
    $scope.section = 'ספקים';

    $scope.loading = false;
    $scope.activeVendorId = null;
    $scope.vendorNewInfo = null;
    $scope.newVendorInfo = null;
    $scope.editMode = false;
    $scope.addProduct = false;
    $scope.changeProduct = false;
    $scope.newProduct = null;
    $scope.productEdited = null;
    $scope.newProducts = [];
    var productEdited = null;
    $scope.editedProducts = [];
    $scope.modalObject = null;
    $scope.toggleEditedClass = {};
    $scope.toggleListItem = {item: -1};
    $scope.toggleTabItem = {item: 0};

    var currentData = {
        info: null,
        products: null
    };

    if (data.status === 200) {
        if (data.data.category === "vendors") {
            $scope.vendors = data.data.vendors;
        }
    }

    $scope.getVendor = function (id) {
        $scope.loading = true;
        var mainTab = jQuery(document).find('.md-tab.ng-scope.ng-isolate-scope.md-ink-ripple')[0];
        $timeout(function () {
            angular.element(mainTab).triggerHandler('click');
        });
        $scope.newProduct = null;
        $scope.newProducts = [];
        $scope.addProduct = false;
        $scope.editMode = false;
        $scope.vendorProducts = {};
        $scope.toggleListItem = {item: id};
        $scope.activeVendorId = id;
        dbModel.getVendor(place, id)
            .then(function (data) {
                    $scope.vendorInfo = data.data.vendor;
                    currentData.info = id
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            )
            .finally(function () {
                    $timeout(function () {
                        $scope.loading = false;
                    }, 1000)
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
        $scope.modalObject = "vendor";
        jQuery("#vendorModal").modal('show');
    };

    $scope.cancelAddVendor = function () {
        jQuery("#vendorModal").modal('hide')
            .on('hidden.bs.modal', function () {
                $scope.modalObject = null;
                $scope.newVendorInfo = null;
            })
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
                    }, 1000)
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
                        }, 1000)
                    }
                );
        }
    };

    $scope.getProductsByVendor = function () {

        if (!currentData.products || currentData.products !== $scope.activeVendorId) {
            $scope.loading = true;
            var id = $scope.activeVendorId;
            dbModel.getProductsByVendor(place, id)
                .then(function (data) {
                        if (data.data.products)
                            $scope.vendorProducts = data.data.products;
                        currentData.products = id;
                    }
                )
                .catch(function (err) {
                        console.log(err);
                    }
                )
                .finally(function () {
                        $timeout(function () {
                            $scope.loading = false;
                        }, 1000)
                    }
                );
        }
    };

    $scope.cancelNewProduct = function () {
        $scope.addProduct = false;
        $scope.newProduct = null;
    };

    $scope.showNewProduct = function () {
        $scope.newProducts.push({
            name: $scope.newProduct.name,
            price: $scope.newProduct.price,
            measurement: $scope.newProduct.measurement,
            vendorId: $scope.activeVendorId
        });
        $scope.newProduct = null;
    };

    $scope.saveNewProducts = function () {
        $scope.loading = true;
        dbModel.addProducts(place, {newProducts: $scope.newProducts, editProducts: $scope.editedProducts})
            .then(function (data) {
                    console.log(data.data);
                    if(data.data.new) {
                        var firstId = data.data.firstId;
                        for (var i = 0; i < $scope.newProducts.length; i++) {
                            $scope.vendorProducts[firstId] = $scope.newProducts[i];
                            firstId++
                        }
                    }
                    $scope.newProducts = [];
                    $scope.editedProducts = [];
                    $scope.toggleEditedClass = {};
                    $scope.addProduct = false;
                    $scope.changeProduct = false;
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            )
            .finally(function () {
                    $timeout(function () {
                        $scope.loading = false;
                    }, 1000)
                }
            );
    };

    $scope.editProduct = function (id) {
        $scope.productNewInfo = {};
        productEdited = id;
        $scope.productNewInfo = angular.copy($scope.vendorProducts[id]);
        $scope.modalObject = "product";
        jQuery("#vendorModal").modal('show');
    };

    $scope.closeEditProduct = function () {
        jQuery("#vendorModal").modal('hide')
            .on('hidden.bs.modal', function () {
                productEdited = null;
                $scope.modalObject = null;
                console.log($scope.toggleEditedClass);
            })
    };

    $scope.showEditProduct = function () {
        $scope.toggleEditedClass[productEdited] = true;
        $scope.editedProducts.push({
            id: productEdited,
            name: $scope.productNewInfo.name,
            price: $scope.productNewInfo.price,
            measurement: $scope.productNewInfo.measurement,
            vendorId: $scope.activeVendorId
        });
        $scope.vendorProducts[productEdited] = $scope.productNewInfo;
        $scope.productNewInfo = null;
        $scope.changeProduct = true;
        $scope.closeEditProduct()
    };

    var valid = function (oldObject, newObject) {
        return true;
    }
}