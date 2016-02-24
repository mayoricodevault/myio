'use strict';

angular.module('pixie.dashboard').controller('FavoritesController', ['$rootScope', '$scope', 'favorites', function($rootScope, $scope, favorites) {

    $scope.items = favorites.items;

    var unbind = $rootScope.$on('activity.happened', function(e, type, itemType, items) {
        if (type === 'unfavorited') {
            $scope.items = favorites.items = favorites.items.filter(function(item) {
                return items.indexOf(item) === -1;
            });
        }
    });

    if ( ! favorites.loaded) {
        favorites.getFavoritedItems().success(function() {
            $scope.items = favorites.items;
        })
    }

    $scope.$on('$destroy', function() {
        unbind();
    });
}]);