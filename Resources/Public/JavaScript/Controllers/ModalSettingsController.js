(function () {

	'use strict';

	angular.module('snowbabel')

		.controller('modalSettingsController', function ($scope, $modalInstance, settingsService) {

			/**
			 *
			 */
			settingsService.init();

			/**
			 *
			 */
			settingsService.getExtensions().then(function (extensions) {

				var groupedExtensions = {
					frontend: [],
					backend: [],
					miscellaneous: []
				};

				angular.forEach(extensions, function (extension) {

					switch (extension.category) {
						case ('fe' || 'plugin'):
							groupedExtensions.frontend.push(extension);
							break;
						case ('be' || 'module'):
							groupedExtensions.backend.push(extension);
							break;
						default:
							groupedExtensions.miscellaneous.push(extension);
					}


				});

				$scope.extensionsGroupedByCategory = groupedExtensions;

			});

			/**
			 *
			 * @type {{}}
			 */
			$scope.extensionsGroupedByCategory = {};

			/**
			 *
			 * @param index
			 * @param extension
			 * @param groupedBy
			 */
			$scope.selectExtension = function (index, extension, groupedBy) {

				// Invert value
				extension.selected = !extension.selected;

				// Update view (do not block user)
				setGroupedExtension(index, extension, groupedBy);

				// Save extension selection
				settingsService.updateExtension(extension).then(
					function () {

					},
					function () {
						// Since we do not block the user, we have to revert the view
						extension.selected = !extension.selected;
						setGroupedExtension(index, extension, groupedBy);

						// todo: provide a message to the user
					}
				);

			};

			/**
			 *
			 */
			$scope.ok = function () {
				$modalInstance.close();
			};

			/**
			 *
			 */
			$scope.cancel = function () {
				$modalInstance.dismiss('cancel');
			};

			/**
			 *
			 * @param index
			 * @param extension
			 * @param groupedBy
			 */
			function setGroupedExtension(index, extension, groupedBy) {
				$scope.extensionsGroupedByCategory[groupedBy][index] = extension;
			}

		});

}());