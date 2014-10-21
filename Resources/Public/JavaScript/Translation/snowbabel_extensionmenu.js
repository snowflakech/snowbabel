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

TYPO3.Snowbabel.ExtensionMenu = Ext.extend(Ext.Panel , {
	border: false,
	margin: {
		top: 0,
		right: 0,
		left: 0
	},

	initComponent:function() {

		var ExtensionMenuStore = new Ext.data.DirectStore({
			directFn: TYPO3.Snowbabel.ExtDirect.getExtensionMenu,
			paramsAsHash: true,
			root: '',
			remoteSort: true,
			fields: [
				'ExtensionId',
				'ExtensionKey',
				'ExtensionTitle',
				'ExtensionDescription',
				'ExtensionCategory',
				'ExtensionIcon',
				'ExtensionLocation',
				'ExtensionCss',
				'ExtensionLoaded'
			]
		});

			// template
		var ExtensionMenuTpl = new Ext.XTemplate(
			'<ul id="ExtensionMenu" class="snowbabel-menu">',
			'<tpl for=".">',
				'<li ext:qtip="<b>' + TYPO3.lang.translation_extensionmenu_QtipKey + ': </b>{ExtensionKey}<br /><b>' + TYPO3.lang.translation_extensionmenu_QtipLocation + ': </b>{ExtensionLocation}<br /><b>' + TYPO3.lang.translation_extensionmenu_QtipDescription + ':</b> {ExtensionDescription}<br /><b>' + TYPO3.lang.translation_extensionmenu_QtipCategory + ':</b> {ExtensionCategory}" class="snowbabel-menu-item {ExtensionCss} loaded{ExtensionLoaded}" style="background-image: url({ExtensionIcon});">',
					'{ExtensionTitle}',
				'</li>',
			'</tpl>',
			'</ul>',
			'<div class="x-clear"></div>'
		);

			// dataview
		var ExtensionMenuView = new Ext.DataView({
			id: 'snowbabel-extension-menu-view',
			autoScroll: true,
			singleSelect: true,
			overClass:'snowbabel-menu-item-over',
			selectedClass: 'snowbabel-menu-item-selected',
			itemSelector:'li.snowbabel-menu-item',
			emptyText: TYPO3.lang.translation_extensionmenu_filterNoResult,
			store: ExtensionMenuStore,
			tpl: ExtensionMenuTpl,
			loadingText: TYPO3.lang.translation_extensionmenu_LoadingText,
			listeners: ({
				'click': function(dataView, index, node, e) {

					var record = dataView.getRecord(node);

						// set params for view
					LoadParams = new Array();
					LoadParams['ExtensionId'] = record.data.ExtensionId;
					LoadParams['ActionKey'] = '';
                    LoadParams['LanguageKey'] = '';
					LoadParams['SearchGlobal'] = false;
					LoadParams['SearchString'] = '';

						// load view
					TYPO3.Snowbabel.Generals.LoadListView(LoadParams);
				}

			})
		});

		var ExtensionFilter = new Ext.Toolbar({
			items: [{
				xtype: 'textfield',
				id: 'ExtensionFilter',
				selectOnFocus: true,
				width: 181,
				listeners: {
					'render': {fn:function(){
						Ext.getCmp('ExtensionFilter').getEl().on('keyup', function(){
							this.filter();
						}, this, {buffer:500})
					}, scope:this}
				}
			}]
		});

			//config
		var config = {
			items: ExtensionMenuView,
			tbar: ExtensionFilter
		};

		Ext.apply(this, Ext.apply(this.initialConfig, config));

			//load store
		ExtensionMenuStore.load();

		TYPO3.Snowbabel.ExtensionMenu.superclass.initComponent.apply(this, arguments);
	},

	filter: function() {

		var ExtensionFilter = Ext.getCmp('ExtensionFilter');
		var View = Ext.getCmp('snowbabel-extension-menu-view');

		View.store.filter('ExtensionTitle', ExtensionFilter.getValue());
	}

});

Ext.reg('TYPO3.Snowbabel.ExtensionMenu', TYPO3.Snowbabel.ExtensionMenu);