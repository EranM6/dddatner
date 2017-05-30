dddatner.controller("productsCtrl", ["$scope", "$state", "$timeout", "dbModel", "products", productsCtrl]);

function productsCtrl($scope, $state, $timeout, dbModel, products) {


    $scope.newProducts = [];
    $scope.editedProducts = {};
    $scope.addProduct = false;
    $scope.changeProduct = false;

    $scope.vendorProducts = products.data.products ? products.data.products : {};

    $timeout(function () {
        $scope.$parent.toggleLoad();
    }, 1000);

    $scope.editProduct = function (id) {
        $scope.productNewInfo = {};
        $scope.productEdited = id;
        $scope.productNewInfo = angular.copy($scope.vendorProducts[id]);
        jQuery("#productModal").modal('show');
    };

    $scope.editNewProduct = function (index) {
        $scope.productNewInfo = {};
        $scope.newProductEdited = index;
        $scope.productNewInfo = angular.copy($scope.newProducts[index]);
        jQuery("#productModal").modal('show');
    };

    $scope.reEditProduct = function (id) {
        $scope.productNewInfo = {};
        $scope.productReEdited = id;
        $scope.productNewInfo = angular.copy($scope.editedProducts[id]);
        jQuery("#productModal").modal('show');
    };

    $scope.resetProduct = function (id) {
        delete $scope.editedProducts[id];
    };

    $scope.removeNewProduct = function (index) {
        $scope.newProducts.splice(index, 1);
    };


    $scope.openProductScheme = function () {
        $scope.addProduct = true;
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

        $scope.$parent.toggleLoad();

        dbModel.addProducts({newProducts: $scope.newProducts, editProducts: $scope.editedProducts})
            .then(function (data) {
                    if (data.data.new) {
                        var firstId = data.data.new.firstId;
                        for (var i = 0; i < $scope.newProducts.length; i++) {
                            $scope.newProducts[i].id = firstId;
                            $scope.vendorProducts[firstId] = $scope.newProducts[i];
                            firstId++;
                        }
                    }
                    if (data.data.edit) {
                        for (var id in $scope.editedProducts) {
                            $scope.vendorProducts[id] = $scope.editedProducts[id];
                        }
                    }
                    $scope.newProducts = [];
                    $scope.editedProducts = [];
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
                        $scope.$parent.toggleLoad();
                    }, 1000);
                }
            );
    };

    $scope.closeEditProduct = function () {
        jQuery("#productModal").modal('hide')
            .on('hidden.bs.modal', function () {
                $scope.productEdited = null;
            });
    };

    $scope.showEditProduct = function () {
        if ($scope.productEdited !== null) {
            if ($scope.validChange($scope.productNewInfo, $scope.vendorProducts[$scope.productEdited])) {
                $scope.editedProducts[$scope.productEdited] = angular.copy($scope.productNewInfo);
                $scope.editedProducts[$scope.productEdited].vendorId = $scope.activeVendorId;
            }
        } else if ($scope.newProductEdited !== null) {
            if ($scope.validChange($scope.productNewInfo, $scope.newProducts[$scope.newProductEdited]))
                $scope.newProducts[$scope.newProductEdited] = angular.copy($scope.productNewInfo);
        } else if ($scope.productReEdited !== null) {
            if ($scope.validChange($scope.productNewInfo, $scope.editedProducts[$scope.productReEdited])) {
                $scope.editedProducts[$scope.productReEdited] = angular.copy($scope.productNewInfo);
                if (!$scope.validChange($scope.editedProducts[$scope.productReEdited], $scope.vendorProducts[$scope.productReEdited]))
                    delete $scope.editedProducts[$scope.productReEdited];

            }
        }

        $scope.closeEditProduct();
        $scope.productEdited = null;
        $scope.newProductEdited = null;
        $scope.productReEdited = null;
        $scope.productNewInfo = {};

    };

    $scope.$watch('editedProducts', function () {
        if ($scope.editedProducts)
            $scope.productsEditCount = Object.keys($scope.editedProducts).length;
    }, true);

    /*var continueWithoutSaving = function () {
        var clean = true;
        if (($scope.newProducts.length > 0 || $scope.productsEditCount > 0) || ($scope.newReceipts.length > 0 || $scope.receiptsEditCount > 0))
            clean = confirm("השינויים שביצעת יימחקו !! \n לחצ/י OK להמשיך ללא שמירה");
        return clean;
    };

    $scope.$on('$stateChangeStart', function (event, next, current) {
        event.preventDefault();

       if (continueWithoutSaving()){

           $state.transitionTo(next.name, {notify:false});
            return false;
       }else{
           $timeout(function () {
               $scope.$parent.toggleLoad();
           }, 1000);
       }
    });*/
}