

dddatner.service("holder", ["$http", holderFunction]);

function holderFunction() {

    var activeVendorId = null;
    var today = null;
    var locationId = null;
    var inventoryDate = null;

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
        getToday: function () {
            return today;
        },
        setToday: function (month){
            today = angular.copy(month);
        },
        getInventoryDate: function () {
            return inventoryDate;
        },
        setInventoryDate: function (date){
            inventoryDate = angular.copy(date);
        }
    };
}