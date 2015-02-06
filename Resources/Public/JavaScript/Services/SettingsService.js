(function () {

	'use strict';

	angular.module('snowbabel')

		.service('settingsService', function ($http, settings) {

			return {

				/**
				 *
				 */
				init: function () {


				},

				/**
				 * TODO: improve performance by caching (make sure it has no impact)
				 *
				 * @returns {*}
				 */
				getExtensions: function () {

					return $http({
						url: 'ajax.php',
						method: 'GET',
						params: {
							ajaxID: 'Snowbabel::dispatch',
							ajaxToken: settings.ajaxToken,
							pluginName: 'translation',
							controllerName: 'Settings',
							actionName: 'getExtensions'
						}
					}).then(function (result) {
						return result.data;
					}, function (reason) {
						//console.log(reason);
					});

				},

				/**
				 *
				 */
				getSelectedExtensions: function () {

					return $http({
						url: 'ajax.php',
						method: 'GET',
						params: {
							ajaxID: 'Snowbabel::dispatch',
							ajaxToken: settings.ajaxToken,
							pluginName: 'translation',
							controllerName: 'Settings',
							actionName: 'getSelectedExtensions'
						}
					}).then(function (result) {
						return result.data;
					});

				},

				/**
				 *
				 * @param extension
				 * @returns {*}
				 */
				updateExtension: function (extension) {

					// Only send extension settings
					var extensionSettings = {
						key: extension.key
					};

					if (angular.isDefined(extension.selected)) {
						extensionSettings.selected = extension.selected;
					}

					return $http({
						url: 'ajax.php',
						method: 'GET',
						params: {
							ajaxID: 'Snowbabel::dispatch',
							ajaxToken: settings.ajaxToken,
							pluginName: 'translation',
							controllerName: 'Settings',
							actionName: 'updateExtension',
							arguments: extensionSettings
						}
					});

				}
			};
		});

}());