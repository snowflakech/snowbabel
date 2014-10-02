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

TYPO3.Snowbabel.ColumnSelection = Ext.extend(Ext.Panel , {
	border: true,
	margin: {
		top: 0,
		right: 0,
		left: 0
	},

	initComponent:function() {

		// store
		var ColumnSelectionStore = new Ext.data.DirectStore( {
			storeId: 'ColumnSelectionStore',
			directFn: TYPO3.Snowbabel.ExtDirect.getColumnSelection,
			paramsAsHash: true,
			root: '',
			fields: ['ColumnId', 'ColumnName', 'ColumnSelected']
		});

			// template
		var ColumnSelectionTpl = new Ext.XTemplate(
			'<ul id="ColumnSelection" class="snowbabel-menu">',
			'<tpl for=".">',
				'<li id="ColumnSelection{ColumnId}" class="snowbabel-menu-item">',
					'{ColumnName}',
				'</li>',
				'</li>',
			'</tpl>',
			'</ul>',
			'<div class="x-clear"></div>'
		);

			// dataview
		var ColumnSelectionView = new Ext.DataView({
			id: 'snowbabel-column-menu-view',
			autoHeight:true,
			multiSelect: true,
			simpleSelect: true,
			overClass:'snowbabel-menu-item-over',
			selectedClass: 'snowbabel-menu-item-selected',
			itemSelector:'li.snowbabel-menu-item',
			store: ColumnSelectionStore,
			tpl: ColumnSelectionTpl,
			listeners: ({
				'click' : function(DataView, Index, Node) {

					var ActionParams	= new Array();
					var Record			= DataView.getRecord(Node);

					ActionParams['ActionKey'] = 'ColumnSelection';
					ActionParams['ColumnId'] = Record.data.ColumnId;
					ActionParams['LoadListView'] = true;

					TYPO3.Snowbabel.Generals.ActionController(ActionParams);

				}
			})
		});

			//config
		var config = {
			itemId: 'snowbabel-column-selection',
			items: ColumnSelectionView
		};

		Ext.apply(this, Ext.apply(this.initialConfig, config));

		ColumnSelectionStore.load({
			callback: function() {

				var TotalCount = ColumnSelectionStore.getTotalCount();
				var Counter = 1;

				ColumnSelectionStore.each(function(Item) {

					var ItemId          = Item.data.ColumnId;
					var ItemSelected    = Item.data.ColumnSelected;
					var ItemClass		= 'ColumnSelection' + ItemId;

						// Add Class 'First'
					if(Counter == 1) {
						Ext.get(ItemClass).addClass('first');
					}

						// Add Class 'Last'
					if(Counter == TotalCount) {
						Ext.get(ItemClass).addClass('last');
					}

						// Add Class 'Selected'
					if(ItemSelected) {
						ColumnSelectionView.select(ItemClass, true);
					}

					++Counter;
				})

			}
		});

		TYPO3.Snowbabel.ColumnSelection.superclass.initComponent.apply(this, arguments);
	}
});

Ext.reg('TYPO3.Snowbabel.ColumnSelection', TYPO3.Snowbabel.ColumnSelection);