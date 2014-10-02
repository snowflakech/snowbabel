<?php
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
 * Plugin 'Snowbabel' for the 'Snowbabel' extension.
 *
 * @author	Daniel Alder <info@snowflake.ch>
 * @package	TYPO3
 * @subpackage	tx_snowbabel
 */
class tx_snowbabel_Application_Translation {

	/**
	 * @var \tx_mod1_snowbabel
	 */
	private $parentObj;

	/**
	 * @var t3lib_PageRenderer
	 */
	private $pageRenderer;

	/**
	 * @var
	 */
	private $resPath;

	/**
	 * @var
	 */
	private $jsPath;

	/**
	 * @var
	 */
	private $jsPathMiscellaneous;

	/**
	 * @var
	 */
	private $jsExtensionPath;

	/**
	 * @var
	 */
	private $languagePath;

	/**
	 * @var
	 */
	private $languageFile;

	/**
	 * @var
	 */
	private $version;

	/**
	 * @param tx_mod1_snowbabel $parentObj
	 */
	public function __construct(tx_mod1_snowbabel $parentObj) {

			// Get Typo3 Version
		$this->version = class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) : t3lib_div::int_from_ver(TYPO3_version);

			// add parent object
		$this->parentObj = $parentObj;

			// generate pageRender
		$this->pageRenderer = $this->parentObj->doc->getPageRenderer();

	}

	/**
	 * @return void
	 */
	public function init() {
			// load extjs
		$this->pageRenderer->loadExtJS();

			// add direct code
		if ($this->version < 4005000) {
				// there is no such function available in older typo3 version
			$this->addExtDirectCode();
		}
		else {
				// since 4.5 it will be used CSRF protection, so it's recommended to use the implemented function
			$this->pageRenderer->addExtDirectCode();
		}

			// add extdirect server
		$this->pageRenderer->addJsFile($this->parentObj->doc->backPath . 'ajax.php?ajaxID=ExtDirect::getAPI&namespace=TYPO3.Snowbabel', NULL, FALSE);

			// add resPath
		$this->resPath = $this->parentObj->doc->backPath . t3lib_extMgm::extRelPath('snowbabel') . 'Resources/';

			// add jsPath
		$this->jsPath = $this->resPath . 'Public/Js/Translation/';
		$this->jsPathMiscellaneous = $this->resPath . 'Public/Js/Miscellaneous/';

			// add jsExtensionPath
		$this->jsExtensionPath = $this->resPath . 'Public/Js/Ux/';

			// add localization file path
		$this->languagePath = 'Resources/Private/Language/';

			// Typo3 4.6 & Above
		if ($this->version >= 4006000) {
			$this->languageFile = 'locallang_translation.xlf';
		}
			// Lower Then Typo3 4.6
		else {
			$this->languageFile = 'locallang_translation.xml';
		}

	}

	/**
	 * @return void
	 */
	public function render() {

			// extjs inline translation
		$this->pageRenderer->addInlineLanguageLabelFile(t3lib_extMgm::extPath('snowbabel') .$this->languagePath . $this->languageFile);

		$this->pageRenderer->addCssFile($this->resPath . 'Public/Css/Translation.css');

			// plugins
		$this->pageRenderer->addJsFile($this->jsExtensionPath . 'SearchField.js');
		$this->pageRenderer->addJsFile($this->jsExtensionPath . 'Spotlight.js');

			// functions
		$this->pageRenderer->addJsFile($this->jsPathMiscellaneous . 'snowbabel_generals.js');

			// scripts
		$this->pageRenderer->addJsFile($this->jsPath . 'snowbabel_listview.js');
		$this->pageRenderer->addJsFile($this->jsPath . 'snowbabel_columnselection.js');
		$this->pageRenderer->addJsFile($this->jsPath . 'snowbabel_languageselection.js');
		$this->pageRenderer->addJsFile($this->jsPath . 'snowbabel_extensionmenu.js');
		$this->pageRenderer->addJsFile($this->jsPath . 'snowbabel_viewports.js');

			// start main app
		$this->pageRenderer->addJsFile($this->jsPath . 'snowbabel_app.js');

		if(t3lib_div::_GET('debug')) {
			$this->pageRenderer->enableDebugMode();
		}

	}

	/**
	 * Code from pagerenderer lib in typo3 4.5 !!!
	 *
	 * @return void
	 */
	private function addExtDirectCode() {
			// Note: we need to iterate thru the object, because the addProvider method
			// does this only with multiple arguments
		$this->pageRenderer->addExtOnReadyCode(
			'for (var api in Ext.app.ExtDirectAPI) {
				Ext.Direct.addProvider(Ext.app.ExtDirectAPI[api]);
			}
			',
			TRUE
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Application/class.tx_snowbabel_application_translation.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Application/class.tx_snowbabel_application_translation.php']);
}

?>