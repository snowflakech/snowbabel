(function () {

	'use strict';

	angular.module('snowbabel')

		.controller('extensionListController', function ($scope, settingsService) {

			/**
			 *
			 */
			settingsService.init();

			/**
			 *
			 */
			settingsService.getSelectedExtensions().then(function (extensions) {

				$scope.extensions = extensions;

			});

			/**
			 *
			 * @type {Array}
			 */
			$scope.extensions = [];


		});

}());