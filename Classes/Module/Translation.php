<?php
namespace Snowflake\Snowbabel\Module;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Plugin 'Snowbabel' for the 'Snowbabel' extension.
 *
 * @author        Daniel Alder <info@snowflake.ch>
 * @package       TYPO3
 * @subpackage    tx_snowbabel
 */
class Translation {


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
	 * @param tx_mod1_snowbabel $parentObj
	 */
	public function __construct(tx_mod1_snowbabel $parentObj) {

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
		$this->pageRenderer->addExtDirectCode();

		// add extdirect server
		$this->pageRenderer->addJsFile($this->parentObj->doc->backPath . 'ajax.php?ajaxID=ExtDirect::getAPI&namespace=TYPO3.Snowbabel', null, false);

		// add resPath
		$this->resPath = $this->parentObj->doc->backPath . ExtensionManagementUtility::extRelPath('snowbabel') . 'Resources/';

		// add jsPath
		$this->jsPath = $this->resPath . 'Public/Js/Translation/';
		$this->jsPathMiscellaneous = $this->resPath . 'Public/Js/Miscellaneous/';

		// add jsExtensionPath
		$this->jsExtensionPath = $this->resPath . 'Public/Js/Ux/';

		// add localization file path
		$this->languagePath = 'Resources/Private/Language/';

		// Add XLIFF file
		$this->languageFile = 'locallang_translation.xlf';

	}


	/**
	 * @return void
	 */
	public function render() {

		// extjs inline translation
		$this->pageRenderer->addInlineLanguageLabelFile(ExtensionManagementUtility::extPath('snowbabel') . $this->languagePath . $this->languageFile);

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

		if(GeneralUtility::_GET('debug')) {
			$this->pageRenderer->enableDebugMode();
		}

	}
}