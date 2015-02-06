(function () {

	'use strict';

	angular.module('snowbabel')

		.controller('modalController', function ($scope, $modal) {

			$scope.open = function (key) {

				var modalInstance = $modal.open({
				      templateUrl: key + '.html',
				      controller: key + 'Controller',
				      size: 'lg'
				    });

			};


		});

}());