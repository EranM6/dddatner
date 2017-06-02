

dddatner.service("holder", ["$http", holderFunction]);

function holderFunction() {

    var activeVendorId = null;
    var selectedMonth = null;
    var locationId = null;

    return {
        setLocationId: function(id){
            locationId = id;
        },
        getLocationId: function(){
            return locationId;
        },
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