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
        getVendors: function (place) {
            return $http.get(urlBase + place + "Controller/getVendors");
        },
        getVendor: function (place, id) {
            return $http.get(urlBase + place + "Controller/getVendor/" + id);
        },
        addVendor: function (place, data) {
            return $http.post(urlBase + place + "Controller/addVendor/" , data);
        },
        updateVendor: function (place, data) {
            return $http.post(urlBase + place + "Controller/updateVendor/" , data);
        },
        getProductsByVendor: function (place, id) {
            return $http.get(urlBase + place + "Controller/getProductsByVendor/" + id);
        },
        addProducts: function (place, data) {
            return $http.post(urlBase + place + "Controller/addProducts/" , data);
        },
        getReceiptsByVendor: function (place, id, month) {
            return $http.get(urlBase + place + "Controller/getReceiptsByVendor/" + id + "/" + month.month + "/" + month.year);
        }
    };
}