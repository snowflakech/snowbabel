/***************************************************************
*  Copyright notice
*
*  (c) 2011 Daniel Alder <info@snowflake.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Extjs for the 'Snowbabel' extension.
 *
 * @author	Daniel Alder <info@snowflake.ch>
 * @package	TYPO3
 * @subpackage	tx_snowbabel
 */
Ext.ns('TYPO3.Snowbabel', 'TYPO3.Snowbabel.ExtDirect');

Ext.onReady(function() {

		// init quicktips
	Ext.QuickTips.init();

		// start main app
	var Snowbabel = new TYPO3.Snowbabel.Translation.init();

});

TYPO3.Snowbabel.Translation = {

	init : function() {

		/**
		 *
		 */
		var ActionController = new Ext.data.DirectStore({
			storeId: 'ActionControllerStore',
			directFn: TYPO3.Snowbabel.ExtDirect.ActionController,
			paramsAsHash: true,
			root: '',
			fields: []
		});

			// create viewport
		var Viewport = new Ext.Viewport({
			layout: 'border',
			items:[
				TYPO3.Snowbabel.ViewportNorth,
				TYPO3.Snowbabel.ViewportCenter,
				TYPO3.Snowbabel.ViewportWest,
				TYPO3.Snowbabel.ViewportEast
			],
			listeners: ({
				'afterrender': function() {

					var ActionParams	= new Array();
					ActionParams['ActionKey'] = 'CheckScheduler';

					TYPO3.Snowbabel.Generals.ActionController(ActionParams);

					var ActionParams	= new Array();
					ActionParams['ActionKey'] = 'ConfigurationChanged';

					TYPO3.Snowbabel.Generals.ActionController(ActionParams);

				}
			})
		});

	}
};