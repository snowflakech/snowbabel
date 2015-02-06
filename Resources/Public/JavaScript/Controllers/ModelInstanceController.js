(function () {

	'use strict';

	angular.module('snowbabel')

		.controller('modalInstanceController', function ($scope, $modalInstance) {

			$scope.ok = function () {
				$modalInstance.close();
			};

			$scope.cancel = function () {
				$modalInstance.dismiss('cancel');
			};
		});

}());