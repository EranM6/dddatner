

dddatner.service("holder", ["$http", holderFunction]);

function holderFunction() {

    var activeVendorId = null;
    var selectedMonth = null;

    return {
        getActiveVendorId: function () {
            return activeVendorId;
        },
        setActiveVendorId: function (id) {
            activeVendorId = id;
        },
        getSelectedMonth: function () {
            return selectedMonth;
        },
        setSelectedMonth: function (month){
            selectedMonth = angular.copy(month);
        }
    };
}