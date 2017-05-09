/**
 * Created by kaka on 03/30/2017.
 */

dddatner.service("dbModel", ["$http", dbModelFunction]);

function dbModelFunction($http) {
    var urlBase = '/dddatner/';

    return {
        getLocation: function () {
            return $http.get(urlBase + "home/getLocation/")
        },
        getVendors: function (place) {
            return $http.get(urlBase + place + "Controller/getVendors")
        },
        getVendor: function (place, id) {
            return $http.get(urlBase + place + "Controller/getVendor/" + id)
        },
        getProductsByVendor: function (place, id) {
            return $http.get(urlBase + place + "Controller/getProductsByVendor/" + id)
        }
    }
}