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

TYPO3.Snowbabel.LanguageSelection = Ext.extend(Ext.Panel , {

	border: true,

	initComponent:function() {

			// store
		var LanguageSelectionStore = new Ext.data.DirectStore( {
			storeId: 'LanguageSelectionStore',
			directFn: TYPO3.Snowbabel.ExtDirect.getLanguageSelection,
			paramsAsHash: true,
			root: '',
			baseParams: {
				LanguageKey: ''
			},
			fields: [
				'LanguageId',
				'LanguageName',
				'LanguageNameEn',
				'LanguageNameLocal',
				'LanguageKey',
				'LanguageFlag',
				'LanguageSelected'
			]
		});

			// template
		var LanguageSelectionTpl = new Ext.XTemplate(
			'<ul id="LanguageSelection" class="snowbabel-menu">',
			'<tpl for=".">',
				'<li id="LanguageSelection{LanguageId}" class="snowbabel-menu-item" style="background-image: url({LanguageFlag});">',
					'{LanguageName}',
				'</li>',
				'</li>',
			'</tpl>',
			'</ul>',
			'<div class="x-clear"></div>'
		);

			// Data View
		var LanguageSelectionView = new Ext.DataView({
			id: 'snowbabel-language-menu-view',
			autoScroll: true,
			multiSelect: true,
			simpleSelect: true,
			overClass:'snowbabel-menu-item-over',
			selectedClass: 'snowbabel-menu-item-selected',
			itemSelector:'li.snowbabel-menu-item',
			emptyText: TYPO3.lang.translation_languageselection_DataViewEmptyText,
			store: LanguageSelectionStore,
			tpl: LanguageSelectionTpl,
			listeners: ({
				'click' : function(DataView, Index, Node) {

					var ActionParams	= new Array();
					var Record			= DataView.getRecord(Node);

					ActionParams['ActionKey'] = 'LanguageSelection';
					ActionParams['LanguageId'] = Record.data.LanguageId;
					ActionParams['LoadListView'] = true;

					TYPO3.Snowbabel.Generals.ActionController(ActionParams);

				}
			})
		});

			//config
		var config = {
			itemId: 'snowbabel-language-selection',
			items: LanguageSelectionView
		};

		Ext.apply(this, Ext.apply(this.initialConfig, config));

		LanguageSelectionStore.load({
			callback: function() {

				var TotalCount = LanguageSelectionStore.getTotalCount();
				var Counter = 1;

				LanguageSelectionStore.each(function(Item) {

					var ItemId          = Item.data.LanguageId;
					var ItemSelected    = Item.data.LanguageSelected;
					var ItemClass		= 'LanguageSelection' + ItemId;

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
						LanguageSelectionView.select(ItemClass, true);
					}

					++Counter;
				})

			}
		});

		TYPO3.Snowbabel.LanguageSelection.superclass.initComponent.apply(this, arguments);
	}
});

Ext.reg('TYPO3.Snowbabel.LanguageSelection', TYPO3.Snowbabel.LanguageSelection);