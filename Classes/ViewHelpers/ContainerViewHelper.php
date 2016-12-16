<?php
namespace Snowflake\Snowbabel\ViewHelpers;

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

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Class ContainerViewHelper
 *
 * @package Snowflake\Snowbabel\ViewHelpers
 */
class ContainerViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper {


	/**
	 * Render start page with \TYPO3\CMS\Backend\Template\DocumentTemplate and pageTitle
	 *
	 * @param string  $pageTitle           title tag of the module. Not required by default, as BE modules are shown in a frame
	 * @param boolean $loadExtJs           specifies whether to load ExtJS library. Defaults to FALSE
	 * @param boolean $loadExtJsTheme      whether to load ExtJS "grey" theme. Defaults to FALSE
	 * @param string  $extJsAdapter        load alternative adapter (ext-base is default adapter)
	 * @param boolean $enableExtJsDebug    if TRUE, debug version of ExtJS is loaded. Use this for development only
	 * @param boolean $loadJQuery          whether to load jQuery library. Defaults to FALSE
	 * @param array   $includeCssFiles     List of custom CSS file to be loaded
	 * @param array   $includeJsFiles      List of custom JavaScript file to be loaded
	 * @param string  $addJsInlineFile     XML file to add to JavaScript inline labels
	 * @param array   $addJsInlineLabels   Custom labels to add to JavaScript inline labels
	 * @param boolean $includeCsh          flag for including CSH
	 * @return string
	 * @see \TYPO3\CMS\Backend\Template\DocumentTemplate
	 * @see \TYPO3\CMS\Core\Page\PageRenderer
	 */
	public function render($pageTitle = '', $loadExtJs = false, $loadExtJsTheme = true, $extJsAdapter = '', $enableExtJsDebug = false, $loadJQuery = false, $includeCssFiles = null, $includeJsFiles = null, $addJsInlineFile = null, $addJsInlineLabels = null, $includeCsh = true) {

		$doc = $this->getDocInstance();
		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$pageRenderer->addExtDirectCode(
         array('TYPO3.Snowbabel')
        );
        
		$doc->JScode .= $doc->wrapScriptTags($doc->redirectUrls());

		// Load various standard libraries
		if($loadExtJs) {
			$pageRenderer->loadExtJS(true, $loadExtJsTheme, $extJsAdapter);
			if($enableExtJsDebug) {
				$pageRenderer->enableExtJsDebug();
			}
		}

		if($loadJQuery) {
			$pageRenderer->loadJquery(null, null, $pageRenderer::JQUERY_NAMESPACE_DEFAULT_NOCONFLICT);
		}

		// Include custom CSS and JS files
		if(is_array($includeCssFiles) && count($includeCssFiles) > 0) {
			foreach($includeCssFiles as $addCssFile) {
				$pageRenderer->addCssFile($addCssFile);
			}
		}
		if(is_array($includeJsFiles) && count($includeJsFiles) > 0) {
			foreach($includeJsFiles as $addJsFile) {
				$pageRenderer->addJsFile($addJsFile);
			}
		}

		// Add inline language file
		if(is_string($addJsInlineFile)) {
			$pageRenderer->addInlineLanguageLabelFile($addJsInlineFile);
		}

		// Add inline language labels
		if(is_array($addJsInlineLabels) && count($addJsInlineLabels) > 0) {
			$extensionKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
			foreach($addJsInlineLabels as $key) {
				$label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, $extensionKey);
				$pageRenderer->addInlineLanguageLabel($key, $label);
			}
		}
		// Render the content and return it
		$output = $this->renderChildren();
		$output = $doc->startPage($pageTitle, $includeCsh) . $output;
		$output .= $doc->endPage();
		return $output;
	}
}
