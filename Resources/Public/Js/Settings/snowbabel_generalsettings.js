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

TYPO3.Snowbabel.GeneralSettings = Ext.extend(Ext.form.FormPanel , {

	padding: 10,

	initComponent:function() {

			// All Available System Languages
		var LanguagesStore = new Ext.data.DirectStore( {
			directFn: TYPO3.Snowbabel.ExtDirect.getGeneralSettingsLanguages,
			paramsAsHash: true,
			root: '',
			sortInfo: {
				field: 'LanguageName',
				direction: 'ASC'
			},
			fields: ['LanguageId', 'LanguageName', 'LanguageKey']
		});

			// Added System Languages
		var LanguagesAddedStore = new Ext.data.DirectStore( {
			directFn: TYPO3.Snowbabel.ExtDirect.getGeneralSettingsLanguagesAdded,
			paramsAsHash: true,
			root: '',
			sortInfo: {
				field: 'LanguageName',
				direction: 'ASC'
			},
			fields: ['LanguageId', 'LanguageName', 'LanguageKey']
		});

			// All Available Extensions
		var WhitelistedExtensionsStore = new Ext.data.DirectStore( {
			directFn: TYPO3.Snowbabel.ExtDirect.getGeneralSettingsWhitelistedExtensions,
			paramsAsHash: true,
			root: '',
			sortInfo: {
				field: 'ExtensionKey',
				direction: 'ASC'
			},
			fields: ['ExtensionKey']
		});

			// Added Extensions
		var WhitelistedExtensionsAddedStore = new Ext.data.DirectStore( {
			directFn: TYPO3.Snowbabel.ExtDirect.getGeneralSettingsWhitelistedExtensionsAdded,
			paramsAsHash: true,
			root: '',
			sortInfo: {
				field: 'ExtensionKey',
				direction: 'ASC'
			},
			fields: ['ExtensionKey']
		});

			//config
		var config = {
			labelWidth: 150,
			items: {
				xtype: 'tabpanel',
				activeTab: 0,
				deferredRender: false,
				defaults:{autoHeight:true, bodyStyle:'padding:10px'},
				items: [{
					title: TYPO3.lang.settings_formtab_Pathes,
					layout: 'form',
					defaultType: 'textfield',
					items: [{
						fieldLabel: TYPO3.lang.settings_formlabel_LocalExtensionPath,
						name: 'LocalExtensionPath',
						anchor: '80%',
						allowBlank:false
					},{
						fieldLabel: TYPO3.lang.settings_formlabel_SystemExtensionPath,
						name: 'SystemExtensionPath',
						anchor: '80%',
						allowBlank:false
					},{
						fieldLabel: TYPO3.lang.settings_formlabel_GlobalExtensionPath,
						name: 'GlobalExtensionPath',
						anchor: '80%',
						allowBlank:false
					}],
					buttonAlign: 'left',
					buttons: [{
						iconCls: 'silk-disk',
						text: TYPO3.lang.settings_formbutton_Save,
						tooltip: TYPO3.lang.settings_formtooltip_Save,
						handler: function(){
							TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
						}
					}]
				},{
					title: TYPO3.lang.settings_formtab_Blacklists,
					layout: 'form',
					defaultType: 'textfield',
					items: [{
						xtype: 'textfield',
						anchor: '80%',
						fieldLabel: TYPO3.lang.settings_formlabel_BlacklistedExtensions,
						name: 'BlacklistedExtensions'
					},{
						xtype: 'textfield',
						anchor: '80%',
						fieldLabel: TYPO3.lang.settings_formlabel_BlacklistedCategories,
						name: 'BlacklistedCategories'
					}],
					buttonAlign: 'left',
					buttons: [{
						iconCls: 'silk-disk',
						text: TYPO3.lang.settings_formbutton_Save,
						tooltip: TYPO3.lang.settings_formtooltip_Save,
						handler: function(){
							TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
						}
					}]
				},{
					title: TYPO3.lang.settings_formtab_Whitelists,
					layout: 'form',
					defaultType: 'textfield',
					items: [{
						xtype: 'checkbox',
						fieldLabel: TYPO3.lang.settings_formlabel_WhitelistedActivated,
						name: 'WhitelistedActivated'
					},{
						xtype: 'itemselector',
						fieldLabel: TYPO3.lang.settings_formlabel_AddedExtensions,
						name: 'WhitelistedExtensions',
						imagePath: '../Resources/Public/Images/Silk/',
						drawUpIcon: false,
						drawDownIcon: false,
						drawTopIcon: false,
						drawBotIcon: false,
						iconUp: 'arrow_up.png',
						iconDown: 'arrow_down.png',
						iconLeft: 'arrow_left.png',
						iconRight: 'arrow_right.png',
						iconTop: 'arrow_up.png',
						iconBottom: 'arrow_down.png',
						ddReorder: true,
						width: 550,
						multiselects: [{
							legend: TYPO3.lang.settings_formlabel_AddedExtensions_Available,
							style: 'background-color:white',
							width: 250,
							height: 200,
							store: WhitelistedExtensionsStore,
							displayField: 'ExtensionKey',
							valueField: 'ExtensionKey'
						},{
							legend: TYPO3.lang.settings_formlabel_AddedExtensions_Added,
							style: 'background-color:white',
							width: 250,
							height: 200,
							store: WhitelistedExtensionsAddedStore,
							displayField: 'ExtensionKey',
							valueField: 'ExtensionKey'
						}]
					}],
					buttonAlign: 'left',
					buttons: [{
						iconCls: 'silk-disk',
						text: TYPO3.lang.settings_formbutton_Save,
						tooltip: TYPO3.lang.settings_formtooltip_Save,
						handler: function(){
							TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
						}
					}]
				},{
					title: TYPO3.lang.settings_formtab_DisplayOptions,
					layout: 'form',
					defaultType: 'checkbox',
					items: [{
						xtype: 'checkbox',
						fieldLabel: TYPO3.lang.settings_formlabel_ShowOnlyLoadedExtensions,
						name: 'ShowOnlyLoadedExtensions'
					},{
						xtype: 'checkbox',
						fieldLabel: TYPO3.lang.settings_formlabel_ShowTranslatedLanguages,
						name: 'ShowTranslatedLanguages'
					},{
						xtype: 'checkbox',
						fieldLabel: TYPO3.lang.settings_formlabel_ShowLocalExtensions,
						name: 'ShowLocalExtensions'
					},{
						xtype: 'checkbox',
						fieldLabel: TYPO3.lang.settings_formlabel_ShowSystemExtensions,
						name: 'ShowSystemExtensions'
					},{
						xtype: 'checkbox',
						fieldLabel: TYPO3.lang.settings_formlabel_ShowGlobalExtensions,
						name: 'ShowGlobalExtensions'
					}],
					buttonAlign: 'left',
					buttons: [{
						iconCls: 'silk-disk',
						text: TYPO3.lang.settings_formbutton_Save,
						tooltip: TYPO3.lang.settings_formtooltip_Save,
						handler: function(){
							TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
						}
					}]
				},{
					title: TYPO3.lang.settings_formtab_Editing,
					layout: 'form',
					defaultType: 'checkbox',
					items: [{
						xtype: 'checkbox',
						fieldLabel: TYPO3.lang.settings_formlabel_CopyDefaultLanguage,
						name: 'CopyDefaultLanguage'
					}],
					buttonAlign: 'left',
					buttons: [{
						iconCls: 'silk-disk',
						text: TYPO3.lang.settings_formbutton_Save,
						tooltip: TYPO3.lang.settings_formtooltip_Save,
						handler: function(){
							TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
						}
					}]
				},{
					title: TYPO3.lang.settings_formtab_Languages,
					layout: 'form',
					items: [{
						xtype: 'itemselector',
						fieldLabel: TYPO3.lang.settings_formlabel_AddedLanguages,
						name: 'AddedLanguages',
						imagePath: '../Resources/Public/Images/Silk/',
						drawUpIcon: false,
						drawDownIcon: false,
						drawTopIcon: false,
						drawBotIcon: false,
						iconUp: 'arrow_up.png',
						iconDown: 'arrow_down.png',
						iconLeft: 'arrow_left.png',
						iconRight: 'arrow_right.png',
						iconTop: 'arrow_up.png',
						iconBottom: 'arrow_down.png',
						ddReorder: true,
						width: 550,
						multiselects: [{
							legend: TYPO3.lang.settings_formlabel_AddedLanguages_Available,
							style: 'background-color:white',
							width: 250,
							height: 200,
							store: LanguagesStore,
							displayField: 'LanguageName',
							valueField: 'LanguageId'
						},{
							legend: TYPO3.lang.settings_formlabel_AddedLanguages_Added,
							style: 'background-color:white',
							width: 250,
							height: 200,
							store: LanguagesAddedStore,
							displayField: 'LanguageName',
							valueField: 'LanguageId'
						}]
					}],
					buttonAlign: 'left',
					buttons: [{
						iconCls: 'silk-disk',
						text: TYPO3.lang.settings_formbutton_Save,
						tooltip: TYPO3.lang.settings_formtooltip_Save,
						handler: function(){
							TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
						}
					}]
				}]
//				},{
//					title: TYPO3.lang.settings_formtab_Groups,
//					items: [{
//						layout: 'form',
//						items: [{
//							xtype: 'textfield',
//							anchor: '80%',
//							fieldLabel: TYPO3.lang.settings_formlabel_GroupsNew,
//							name: 'GroupsNew'
//						}],
//						buttonAlign: 'left',
//						buttons: [{
//							iconCls: 'silk-add',
//							text: TYPO3.lang.settings_formbutton_GroupsNew,
//							tooltip: TYPO3.lang.settings_formtooltip_GroupsNew,
//							handler: function(){
//								//TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
//							}
//						}]
//					},{
//						layout: 'form',
////						items: [{
////							xtype: 'textfield',
////							anchor: '80%',
////							fieldLabel: TYPO3.lang.settings_formlabel_GroupsNew,
////							name: 'GroupsNew'
////						}],
//						buttonAlign: 'left',
//						buttons: [{
//							iconCls: 'silk-disk',
//							text: TYPO3.lang.settings_formbutton_GroupsNew,
//							tooltip: TYPO3.lang.settings_formtooltip_GroupsNew,
//							handler: function(){
//								//TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
//							}
//						}]
//					}]
//				}]
			},
			api: {
				load: TYPO3.Snowbabel.ExtDirect.getGeneralSettings,
				submit: TYPO3.Snowbabel.ExtDirect.submitGeneralSettings
			},
			paramsAsHash: false,
		    keys: [{
				key: [Ext.EventObject.ENTER], fn: function(){
					TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit();
				}
		    }],
			listeners: ({
				'afterrender': function () {
					this.getForm().load();
					LanguagesStore.load();
					LanguagesAddedStore.load();
					WhitelistedExtensionsStore.load();
					WhitelistedExtensionsAddedStore.load();
				}
			})
		};

		Ext.apply(this, Ext.apply(this.initialConfig, config));

		TYPO3.Snowbabel.GeneralSettings.superclass.initComponent.apply(this, arguments);


	}
});

Ext.reg('TYPO3.Snowbabel.GeneralSettings', TYPO3.Snowbabel.GeneralSettings);