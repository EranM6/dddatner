dddatner.controller("receiptsCtrl", ["$scope", "$timeout", "dbModel", "holder", "receipts", receiptsCtrl]);

function receiptsCtrl($scope, $timeout, dbModel, holder, receipts) {

    $scope.selectedMonth = holder.getToday();
    $scope.newReceipts = [];
    $scope.editedReceipts = {};
    $scope.addReceipt = false;
    $scope.changeReceipt = false;

    $scope.vendorReceipts = receipts.data.receipts ? receipts.data.receipts : {};
    $scope.chargedReceipts = receipts.data.charge ? Number(receipts.data.charge) : 0;
    $scope.refundedReceipts = receipts.data.refund ? Number(receipts.data.refund) : 0;
    $scope.canClose = Number(receipts.data.notApproved) === 0;
    $scope.closedMonth = !!receipts.data.closed;

    $timeout(function () {
        $scope.$parent.toggleLoad();
    }, 1000);

    var continueWithoutSaving = function () {
        var clean = true;
        if ($scope.newReceipts.length > 0 || $scope.receiptsEditCount > 0)
            clean = confirm("השינויים שביצעת יימחקו !! \n לחצ/י OK להמשיך ללא שמירה");
        return clean;
    };

    $scope.getReceiptsByVendor = function (month, year) {

        if (continueWithoutSaving()) {

            $scope.newReceipts = [];
            $scope.editedReceipts = {};
            $scope.addReceipt = false;
            $scope.changeReceipt = false;

            $scope.$parent.toggleLoad();
            $scope.selectedMonth = $scope.selectedMonth ? getSelectedMonth(month, year || $scope.selectedMonth.year) : {
                month: month,
                year: year
            };

            var id = $scope.activeVendorId;
            dbModel.getReceiptsByVendor(id, $scope.selectedMonth)
                .then(function (data) {
                        if (data.data.receipts) {
                            $scope.vendorReceipts = data.data.receipts;
                        } else {
                            $scope.vendorReceipts = {};
                            $scope.countOfReceipts = 0;
                        }

                        $scope.chargedReceipts = 0;
                        $scope.refundedReceipts = 0;

                        if (data.data.charge) {
                            $scope.chargedReceipts = Number(data.data.charge);
                            if (data.data.total) {
                                $scope.refundedReceipts = Number(data.data.total) - Number(data.data.charge);
                            }
                        }

                        $scope.canClose = Number(data.data.notApproved) === 0;
                        $scope.closedMonth = !!data.data.closed;
                    }
                )
                .catch(function (err) {
                        $scope.vendorReceipts = {};
                        $scope.chargedReceipts = 0;
                        $scope.refundedReceipts = 0;
                    }
                )
                .finally(function () {
                    $timeout(function () {
                        $scope.$parent.toggleLoad();
                    }, 1000);
                    }
                );
        }
    };

    $scope.editReceipt = function (id){
        $scope.receiptNewInfo = {};
        $scope.receiptEdited = id;
        $scope.receiptNewInfo = angular.copy($scope.vendorReceipts[id]);
        $scope.receiptNewInfo.date = reFormatDate($scope.receiptNewInfo.date);

        jQuery("#receiptModal").modal('show');
    };

    $scope.editNewReceipt = function (index){
        $scope.receiptNewInfo = {};
        $scope.newReceiptEdited = index;
        $scope.receiptNewInfo = angular.copy($scope.newReceipts[index]);
        $scope.receiptNewInfo.date = reFormatDate($scope.receiptNewInfo.date);

        jQuery("#receiptModal").modal('show');
    };

    $scope.reEditReceipt = function (id){
        $scope.receiptNewInfo = {};
        $scope.receiptReEdited = id;
        $scope.receiptNewInfo = angular.copy($scope.editedReceipts[id]);
        $scope.receiptNewInfo.date = reFormatDate($scope.receiptNewInfo.date);

        jQuery("#receiptModal").modal('show');
    };

    $scope.resetReceipt = function(id){
        if (($scope.editedReceipts[id].charge) === '1') {
            $scope.chargedReceipts -= Number($scope.editedReceipts[id].amount);
        } else {
            $scope.refundedReceipts -= Number($scope.editedReceipts[id].amount);
        }
        if (($scope.vendorReceipts[id].charge) === '1') {
            $scope.chargedReceipts += Number($scope.vendorReceipts[id].amount);
        } else {
            $scope.refundedReceipts += Number($scope.vendorReceipts[id].amount);
        }

        delete $scope.editedReceipts[id];
    };

    $scope.removeReceipt = function (id){
        if (confirm("are you sure you want to delete "+$scope.vendorReceipts[id].serial+" ?")) {
            dbModel.removeReceipt(id)
                .then(function(){
                    "use strict";
                    if (($scope.vendorReceipts[id].charge) === '1') {
                        $scope.chargedReceipts -= Number($scope.vendorReceipts[id].amount);
                    } else {
                        $scope.refundedReceipts -= Number($scope.vendorReceipts[id].amount);
                    }
                    delete $scope.vendorReceipts[id];
                    $scope.countOfReceipts--;
                })
                .catch(function (err) {
                    console.log(err);
                });
        }
    };

    $scope.removeNewReceipt = function (index){
        if (($scope.newReceipts[index].charge) === '1') {
            $scope.chargedReceipts -= Number($scope.newReceipts[index].amount);
        } else {
            $scope.refundedReceipts -= Number($scope.newReceipts[index].amount);
        }
        $scope.newReceipts.splice(index, 1);
        $scope.countOfReceipts--;
    };

    $scope.changeReceiptState = function(id){
        if(!$scope.closedMonth) {
            if (!$scope.editedReceipts[id])
                $scope.editedReceipts[id] = angular.copy($scope.vendorReceipts[id]);

            $scope.editedReceipts[id].approved = $scope.editedReceipts[id].approved === '0' ? '1' : '0';
            $scope.editedReceipts[id].vendorId = $scope.activeVendorId;
        }
    };

    $scope.changeNewReceiptState = function(receipt){
        receipt.approved =  receipt.approved === '0' ? '1' : '0';
    };

    $scope.reEditReceiptState = function (receipt) {
        var oldValue = $scope.editedReceipts[receipt.id].approved;
        $scope.editedReceipts[receipt.id].approved = oldValue === '0' ? '1' : '0';
        if (!$scope.validChange($scope.editedReceipts[receipt.id], receipt)){
            delete $scope.editedReceipts[receipt.id];
        }else{
            $scope.editedReceipts[receipt.id].vendorId = $scope.activeVendorId;
        }
    };

    $scope.openReceiptScheme = function(){
        $scope.addReceipt = true;
        $timeout(function(){
            $scope.cleanForm = true;
        }, 100);
    };

    $scope.cancelNewReceipt = function () {
        $scope.addReceipt = false;
        $scope.newReceipt = null;
    };

    $scope.showNewReceipt = function () {
        if ($scope.newReceipt.date.length === 1)
            $scope.newReceipt.date = "0" + $scope.newReceipt.date;
        $scope.newReceipts.push({
            date: formatDate($scope.newReceipt.date),
            serial: $scope.newReceipt.serial,
            amount: $scope.newReceipt.amount,
            charge: $scope.newReceipt.charge,
            comment: $scope.newReceipt.comment || "",
            approved: '0',
            vendorId: $scope.activeVendorId
        });

        if (($scope.newReceipt.charge) === '1') {
            $scope.chargedReceipts += Number($scope.newReceipt.amount);
        } else {
            $scope.refundedReceipts += Number($scope.newReceipt.amount);
        }
        $scope.countOfReceipts ++;
        $scope.newReceipt = null;
    };

    $scope.saveNewReceipts = function () {

        $scope.$parent.toggleLoad();

        dbModel.addReceipts({newReceipts: $scope.newReceipts, editReceipts: $scope.editedReceipts, month: $scope.selectedMonth.month, year: $scope.selectedMonth.year, vendorId:$scope.activeVendorId})
            .then(function (data) {
                    if(data.data.new) {
                        var firstId = data.data.new.firstId;
                        for (var i = 0; i < $scope.newReceipts.length; i++) {
                            $scope.newReceipts[i].id = firstId;
                            $scope.vendorReceipts[firstId] = $scope.newReceipts[i];
                            firstId++;
                        }
                    }
                    if(data.data.edit) {
                        for (var id in $scope.editedReceipts) {
                            $scope.vendorReceipts[id] = $scope.editedReceipts[id];
                        }
                    }

                    $scope.newReceipts = [];
                    $scope.editedReceipts = {};

                    $scope.addReceipt = false;
                    $scope.changeReceipt = false;

                    $scope.canClose = data.data.isApproved;
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

    $scope.submitMonth = function () {
        if ($scope.canClose)
            dbModel.closeMonth({
                month: $scope.selectedMonth.month,
                year: $scope.selectedMonth.year,
                vendorId: $scope.activeVendorId,
                charge: $scope.chargedReceipts,
                refund: $scope.refundedReceipts
            })
                .then(function(data){
                    $scope.closedMonth = !!data.data.closed;
                });
    };

    $scope.closeEditReceipt = function () {
        jQuery("#receiptModal").modal('hide')
            .on('hidden.bs.modal', function () {
                $scope.receiptEdited = null;
            });
    };

    $scope.showEditReceipt = function () {
        if($scope.receiptEdited !== null){
            $scope.receiptNewInfo.date =  formatDate($scope.receiptNewInfo.date);
            if ($scope.validChange($scope.receiptNewInfo, $scope.vendorReceipts[$scope.receiptEdited])) {
                $scope.editedReceipts[$scope.receiptEdited] = angular.copy($scope.receiptNewInfo);
                $scope.editedReceipts[$scope.receiptEdited].vendorId = $scope.activeVendorId;

                if ($scope.vendorReceipts[$scope.receiptEdited].charge === '1') {
                    $scope.chargedReceipts -= Number($scope.vendorReceipts[$scope.receiptEdited].amount);
                } else {
                    $scope.refundedReceipts -= Number($scope.vendorReceipts[$scope.receiptEdited].amount);
                }
                if ($scope.editedReceipts[$scope.receiptEdited].charge === '1') {
                    $scope.chargedReceipts += Number($scope.editedReceipts[$scope.receiptEdited].amount);
                } else {
                    $scope.refundedReceipts += Number($scope.editedReceipts[$scope.receiptEdited].amount);
                }
            }
        }else if ($scope.newReceiptEdited !== null){
            $scope.receiptNewInfo.date =  formatDate($scope.receiptNewInfo.date);
            if ($scope.validChange($scope.receiptNewInfo, $scope.newReceipts[$scope.newReceiptEdited])) {
                if ($scope.newReceipts[$scope.newReceiptEdited].charge === '1') {
                    $scope.chargedReceipts -= Number($scope.newReceipts[$scope.newReceiptEdited].amount);
                } else {
                    $scope.refundedReceipts -= Number($scope.newReceipts[$scope.newReceiptEdited].amount);
                }
                if ($scope.receiptNewInfo.charge === '1') {
                    $scope.chargedReceipts += Number($scope.receiptNewInfo.amount);
                } else {
                    $scope.refundedReceipts += Number($scope.receiptNewInfo.amount);
                }
                $scope.newReceipts[$scope.newReceiptEdited] = angular.copy($scope.receiptNewInfo);
            }
        }else if ($scope.receiptReEdited !== null){
            $scope.receiptNewInfo.date =  formatDate($scope.receiptNewInfo.date);
            if ($scope.validChange($scope.receiptNewInfo, $scope.editedReceipts[$scope.receiptReEdited])) {
                if ($scope.editedReceipts[$scope.receiptReEdited].charge === '1') {
                    $scope.chargedReceipts -= Number($scope.editedReceipts[$scope.receiptReEdited].amount);
                } else {
                    $scope.refundedReceipts -= Number($scope.editedReceipts[$scope.receiptReEdited].amount);
                }

                $scope.editedReceipts[$scope.receiptReEdited] = angular.copy($scope.receiptNewInfo);
                if (!$scope.validChange($scope.editedReceipts[$scope.receiptReEdited], $scope.vendorReceipts[$scope.receiptReEdited])) {
                    delete $scope.editedReceipts[$scope.receiptReEdited];
                    if ($scope.vendorReceipts[$scope.receiptReEdited].charge === '1') {
                        $scope.chargedReceipts += Number($scope.vendorReceipts[$scope.receiptReEdited].amount);
                    } else {
                        $scope.refundedReceipts += Number($scope.vendorReceipts[$scope.receiptReEdited].amount);
                    }
                }else{
                    if ($scope.editedReceipts[$scope.receiptReEdited].charge === '1') {
                        $scope.chargedReceipts += Number($scope.editedReceipts[$scope.receiptReEdited].amount);
                    } else {
                        $scope.refundedReceipts += Number($scope.editedReceipts[$scope.receiptReEdited].amount);
                    }
                }
            }
        }

        $scope.closeEditReceipt();
        $scope.receiptEdited = null;
        $scope.newReceiptEdited = null;
        $scope.receiptReEdited = null;
        $scope.receiptNewInfo = {};
    };

    $scope.$watch('editedReceipts', function(){
        if($scope.editedReceipts)
            $scope.receiptsEditCount = Object.keys($scope.editedReceipts).length;
    }, true);

    $scope.$watch('vendorReceipts', function(){
        if($scope.vendorReceipts)
            $scope.countOfReceipts = Object.keys($scope.vendorReceipts).length;
    }, true);

    $scope.$watch('newReceipt', function () {
        $scope.cleanForm = !$scope.newReceipt;
    }, true);

    var getSelectedMonth = function(month, year){
        if (month === 13){
            month = 1;
            year++;
        }else if (month === 0){
            month = 12;
            year--;
        }

        $scope.lastPosibleDate = new Date(year, month , 0).getDate();
        return {month: month, year: year};
    };

    var formatDate = function(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [day, month, year].join('/');
    };

    var reFormatDate = function(date) {
        var parts = date.split('/');

        return new Date(parts[1] + "/" + parts[0] + "/" + parts[2]);
    };

    $scope.today = function() {
        $scope.dt = new Date();
    };
    $scope.today();

    $scope.clear = function() {
        $scope.dt = null;
    };

    $scope.inlineOptions = {
        customClass: getDayClass,
        minDate: new Date(),
        showWeeks: true
    };

    $scope.dateOptions = {
        // dateDisabled: disabled,
        formatYear: 'yy',
        maxDate: new Date(2020, 5, 22),
        minDate: new Date(),
        startingDay: 0
    };

    /*// Disable weekend selection
    function disabled(data) {
        var date = data.date,
            mode = data.mode;
        return mode === 'day' && (date.getDay() === 0 || date.getDay() === 6);
    }*/

    $scope.toggleMin = function() {
        $scope.inlineOptions.minDate = $scope.inlineOptions.minDate ? null : new Date();
        $scope.dateOptions.minDate = $scope.inlineOptions.minDate;
    };

    $scope.toggleMin();

    $scope.open = function() {
        $scope.popup.opened = true;
    };


    $scope.setDate = function(year, month, day) {
        $scope.dt = new Date(year, month, day);
    };

    $scope.formats = ['dd/MM/yyyy'];
    $scope.format = $scope.formats[0];
    $scope.altInputFormats = ['M!/d!/yyyy'];

    $scope.popup = {
        opened: false
    };

    $scope.file = function () {
        dbModel.getReceiptsFile($scope.activeVendorId, $scope.selectedMonth)
            .then(function (data) {
                    var file = new Blob([data.data], {type: 'application/vnd.ms-excel'});
                    var fileName = $scope.$parent.vendors[$scope.activeVendorId].name;
                    fileName += "-תעודות-" + new Date().getTime();
                    saveAs(file, fileName + '.xls');
                }
            )
            .catch(function (err) {
                    console.log(err);
                }
            );
    };

    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    var afterTomorrow = new Date();
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