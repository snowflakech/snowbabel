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

///////////////////////////////////////////////////////
// Viewport -> Nested views
///////////////////////////////////////////////////////

TYPO3.Snowbabel.ViewportNorth = {
	region: 'north',
	height: 49,
	html: TYPO3.Snowbabel.Generals.Typo3Header
};

TYPO3.Snowbabel.ViewportCenter = {
	xtype: 'TYPO3.Snowbabel.ListView',
	id: 'snowbabel-list-view',
	region: 'center',
	layout: 'fit',
	margins: {
		top: 5,
		right: 10,
		bottom: 5,
		left: 10
	}
};

TYPO3.Snowbabel.ViewportWest = {
	region: 'west',
	width: 200,
	margins: {
		top: 5,
		right: 0,
		bottom: 5,
		left: 5
	},
	layout: 'border',
	items: [
		{
			xtype: 'TYPO3.Snowbabel.ExtensionMenu',
			id: 'snowbabel-extension-menu',
			region: 'center',
			layout: 'fit'
		}
	]
};

TYPO3.Snowbabel.ViewportEast = {
	region: 'east',
	width: 200,
	border: false,
	layout: 'border',
	margins: {
		top: 5,
		bottom: 5,
		right: 5
	},
	items: [
		{
			xtype: 'TYPO3.Snowbabel.LanguageSelection',
			id: 'snowbabel-language-menu',
			region: 'center',
			layout: 'fit',
			margins: {
				bottom: 5
			}
		},{
			xtype: 'TYPO3.Snowbabel.ColumnSelection',
			id: 'snowbabel-column-menu',
			region: 'south',
			height: 48
		}
	]
};