/**
 * Created by kaka on 03/30/2017.
 */

dddatner.service("dbModel", ["$http", dbModelFunction]);

function dbModelFunction($http) {
    var urlBase = '/dddatner/';

    return {
        getOut: function(){
            return $http.get(urlBase + "home/getOut/");
        },
        getLocation: function () {
            return $http.get(urlBase + "home/getLocation/");
        },
        setLocation: function (location) {
            return $http.post(urlBase + "home/setLocation/", location);
        },
        getVendors: function () {
            return $http.get(urlBase + "vendors/getVendors");
        },
        getVendor: function (id) {
            return $http.get(urlBase + "vendors/getVendor/" + id);
        },
        addVendor: function (data) {
            return $http.post(urlBase + "vendors/addVendor/" , data);
        },
        updateVendor: function (data) {
            return $http.post(urlBase + "vendors/updateVendor/" , data);
        },
        getProductsByVendor: function (id) {
            return $http.get(urlBase + "vendors/getProductsByVendor/" + id);
        },
        addProducts: function (data) {
            return $http.post(urlBase + "vendors/addProducts/" , data);
        },
        getReceiptsByVendor: function (id, month) {
            return $http.get(urlBase + "vendors/getReceiptsByVendor/" + id + "/" + month.month + "/" + month.year);
        },
        addReceipts: function (data) {
            return $http.post(urlBase + "vendors/addReceipts/" , data);
        },
        removeReceipt: function (id) {
            return $http.get(urlBase + "vendors/removeReceipt/" + id);
        },
        closeMonth: function (data) {
            return $http.post(urlBase + "vendors/closeMonth/" , data);
        },
        getHistory: function (id) {
            return $http.get(urlBase + "vendors/getHistory/" + id);
        },
        saveRecord: function (data){
            return $http.post(urlBase + "inventory/saveRecord/" , data);
        },
        getVendorInventory: function (id, month, year){
            return $http.get(urlBase + "inventory/getVendorInventory/" + id + "/" + month + "/" + year);
        },
        getEntries: function (){
            return $http.get(urlBase + "inventory/getEntries");
        }
    };
}