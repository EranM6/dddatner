dddatner.controller("inventoryCtrl", ["$scope", "$stateParams", "$state", "dbModel", "holder", "vendors", "entries", inventoryCtrl]);

function inventoryCtrl($scope, $stateParams, $state, dbModel, holder, vendors, entries) {
    "use strict";

    $scope.name = $stateParams.displayName;
    $scope.section = 'ספירות מלאי';
    $scope.loading = true;
    $scope.inventoryObj = {};
    $scope.month = holder.getToday();
    $scope.inventoryDate = holder.getInventoryDate() || new Date();
    $scope.selectedVendorId = null;
    $scope.amountPerVendor = {};
    $scope.closedRecords = {};
    $scope.savedRecords = {};
    $scope.displayTable = /.table/.test($state.current.name);

    $scope.records = entries.data.records ? entries.data.records : {};
    $scope.years = Object.keys($scope.records).reverse();
    $scope.vendors = vendors.data.vendors;

    $scope.showModal = function () {
        jQuery("#inventoryModal").modal('show');
    };

    $scope.cancelNewInventory = function(){
        jQuery("#inventoryModal").modal('hide');
    };

    $scope.saveNewInventory = function () {
        holder.setInventoryDate($scope.inventoryDate);
        jQuery("#inventoryModal").modal('hide');
        $state.go('home.inventory.table');
        $scope.displayTable = true;
    };

    $scope.getVendorInventory = function(id){

       if(!$scope.amountPerVendor[id])
           $scope.amountPerVendor[id] = 0;
        $scope.selectedVendorId = id;
        if (!$scope.inventoryObj || !$scope.inventoryObj[id])
            dbModel.getVendorInventory(id, $scope.inventoryDate.getMonth() + 1, $scope.inventoryDate.getFullYear())
                .then(function(data){
                    $scope.inventoryObj[id] = data.data.products;
                    $scope.amountPerVendor[id] = data.data.totalAmount || 0;
                    $scope.closedRecords[id] = Number(data.data.close);
                });
    };

    $scope.changeWorth = function(productId, newValue){

        var newWorth = null;
        var vendorId = $scope.selectedVendorId;
        var oldWorth = $scope.inventoryObj[vendorId][productId].worth;
        if (newValue) {
            if (!isNaN(newValue)) {
                newWorth = $scope.inventoryObj[vendorId][productId].price * newValue;
                $scope.inventoryObj[vendorId][productId].worth = newWorth;
                changeTotal(vendorId, newWorth, oldWorth);
            }
        }else{
            $scope.inventoryObj[vendorId][productId].worth = newWorth;
            changeTotal(vendorId, newWorth, oldWorth);
        }
    };

    var changeTotal = function(vendorId, newWorth, oldWorth){

        $scope.amountPerVendor[vendorId] -= (oldWorth || 0);
        $scope.amountPerVendor[vendorId] += (newWorth || 0);
    };

    $scope.cleanValue = function (productId, value) {
        var vendorId = $scope.selectedVendorId;
        if (value) {
            $scope.inventoryObj[vendorId][productId].amount = value * 1;
        }
    };

    $scope.saveRecord = function (vendorId, close) {

        var newInventory = {
            month: $scope.inventoryDate.getMonth() + 1,
            year: $scope.inventoryDate.getFullYear(),
            vendorId: vendorId,
            totalAmount: $scope.amountPerVendor[vendorId],
            productsList: $scope.inventoryObj[vendorId],
            close: close
        };
        console.log("sending", newInventory);
        dbModel.saveRecord(newInventory)
            .then(function (data) {
                $scope.savedRecords[vendorId] = data.data.saved;
                if (data.data.saved)
                    $scope.closedRecords[vendorId] = !!data.data.closed;
                console.log($scope.closedRecords[vendorId]);
            })
            .catch(function (err) {
                console.log(err);
            });

    };

    $scope.getTotal = function(month, year){
        var total = 0;
        for(var entry in $scope.records[year][month]){
            total += Number($scope.records[year][month][entry].total);
        }
        return total;
    };

    var checkForExistingRecord = function (date) {
        dbModel.getInventory(date.month, date.year)
            .then(function(data){
                console.log(data);
            })
            .catch(function(err){
                console.log(err);
            });
        return true;
    };

    $scope.monthFile = function (month, year) {
        dbModel.getEntriesFile(month, year)
            .then(function (data) {
                    var file = new Blob([data.data], {type: 'application/vnd.ms-excel'});
                    var fileName = "-ספירות-" + new Date().getTime();
                    saveAs(file, fileName + '.xls');
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            );
    };

    $scope.vendorFile = function (id) {
        dbModel.getEntryFile(id)
            .then(function (data) {
                    var file = new Blob([data.data], {type: 'application/vnd.ms-excel'});
                    var fileName = "-ספירות-" + new Date().getTime();
                    saveAs(file, fileName + '.xls');
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            );
    };

    $scope.today = function() {
        $scope.dt = new Date();
    };
    $scope.today();

    $scope.clear = function() {
        $scope.dt = null;
    };

    $scope.options = {
        customClass: getDayClass,
        minDate: new Date(),
        showWeeks: true,
        minMode: 'month'
    };

    // Disable weekend selection
    function disabled(data) {
        var date = data.date,
            mode = data.mode;
        return mode === 'day' && (date.getDay() === 0 || date.getDay() === 6);
    }

    $scope.toggleMin = function() {
        $scope.options.minDate = $scope.options.minDate ? null : new Date();
    };

    $scope.toggleMin();

    $scope.setDate = function(year, month, day) {
        $scope.dt = new Date(year, month, day);
    };

    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    var afterTomorrow = new Date(tomorrow);
    afterTomorrow.setDate(tomorrow.getDate() + 1);
    $scope.events = [
        {
            date: tomorrow,
            status: 'full'
        },
        {
            date: afterTomorrow,
            status: 'partially'
        }
    ];

    function getDayClass(data) {
        var date = data.date,
            mode = data.mode;
        if (mode === 'day') {
            var dayToCheck = new Date(date).setHours(0,0,0,0);

            for (var i = 0; i < $scope.events.length; i++) {
                var currentDay = new Date($scope.events[i].date).setHours(0,0,0,0);

                if (dayToCheck === currentDay) {
                    return $scope.events[i].status;
                }
            }
        }

        return '';
    }
}