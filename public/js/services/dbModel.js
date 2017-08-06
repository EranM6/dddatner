/**
 * Created by kaka on 03/30/2017.
 */

dddatner.service("dbModel", ["$http", dbModelFunction]);

function dbModelFunction($http) {
    var api = '/dddatner/';

    return {
        getOut: function(){
            return $http.get(api + "home/getOut/");
        },
        getLocation: function () {
            return $http.get(api + "home/getLocation/");
        },
        setLocation: function (location) {
            return $http.post(api + "home/setLocation/", location);
        },
        getVendors: function () {
            return $http.get(api + "vendors/getVendors");
        },
        getVendor: function (id) {
            return $http.get(api + "vendors/getVendor/" + id);
        },
        addVendor: function (data) {
            return $http.post(api + "vendors/addVendor/" , data);
        },
        updateVendor: function (data) {
            return $http.post(api + "vendors/updateVendor/" , data);
        },
        getProductsByVendor: function (id) {
            return $http.get(api + "vendors/getProductsByVendor/" + id);
        },
        addProducts: function (data) {
            return $http.post(api + "vendors/addProducts/" , data);
        },
        getReceiptsByVendor: function (id, month) {
            return $http.get(api + "vendors/getReceiptsByVendor/" + id + "/" + month.month + "/" + month.year);
        },
        addReceipts: function (data) {
            return $http.post(api + "vendors/addReceipts/" , data);
        },
        removeReceipt: function (id) {
            return $http.get(api + "vendors/removeReceipt/" + id);
        },
        closeMonth: function (data) {
            return $http.post(api + "vendors/closeMonth/" , data);
        },
        getHistory: function (id) {
            return $http.get(api + "vendors/getHistory/" + id);
        },
        saveRecord: function (data){
            return $http.post(api + "inventory/saveRecord/" , data);
        },
        getVendorInventory: function (id, month, year){
            return $http.get(api + "inventory/getVendorInventory/" + id + "/" + month + "/" + year);
        },
        getEntries: function (){
            return $http.get(api + "inventory/getEntries");
        },
        getFile: function (vendorId) {
            return $http.get(api + "excelFile/index/" + vendorId, {responseType:'arraybuffer'});
        }
    };
}