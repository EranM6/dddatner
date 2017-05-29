/**
 * Created by kaka on 03/30/2017.
 */

dddatner.service("dbModel", ["$http", dbModelFunction]);

function dbModelFunction($http) {
    var urlBase = '/dddatner/';

    return {
        getLocation: function () {
            return $http.get(urlBase + "home/getLocation/");
        },
        setLocation: function (location) {
            return $http.post(urlBase + "home/setLocation/", location);
        },
        getVendors: function () {
            return $http.get(urlBase + "Controller/getVendors");
        },
        getVendor: function (id) {
            return $http.get(urlBase + "Controller/getVendor/" + id);
        },
        addVendor: function (data) {
            return $http.post(urlBase + "Controller/addVendor/" , data);
        },
        updateVendor: function (data) {
            return $http.post(urlBase + "Controller/updateVendor/" , data);
        },
        getProductsByVendor: function (id) {
            return $http.get(urlBase + "Controller/getProductsByVendor/" + id);
        },
        addProducts: function (data) {
            return $http.post(urlBase + "Controller/addProducts/" , data);
        },
        getReceiptsByVendor: function (id, month) {
            return $http.get(urlBase + "Controller/getReceiptsByVendor/" + id + "/" + month.month + "/" + month.year);
        },
        addReceipts: function (data) {
            return $http.post(urlBase + "Controller/addReceipts/" , data);
        },
        closeMonth: function (data) {
            return $http.post(urlBase + "Controller/closeMonth/" , data);
        },
        getHistory: function (id) {
            return $http.get(urlBase + "Controller/getHistory/" + id);
        }
    };
}