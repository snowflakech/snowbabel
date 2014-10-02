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
class tx_snowbabel_TCA {

	/**
	 * @var
	 */
	private $confObj;

	/**
	 * @var
	 */
	private $extObj;

	/**
	 * @param  $PA
	 * @param  $fobj
	 * @return void
	 */
	public function getExtensions($PA, $fobj) {

		$extjsParams = NULL;
		$tcaExtensions = array();

			// get configuration object
		$this->getConfigurationObject($extjsParams);

			// get extension object
		$this->getExtensionsObject();

			// get all extensions for this user
		$Extensions = $this->extObj->getExtensions();

		if(is_array($Extensions)) {
			foreach($Extensions as $Extension) {
				$Value = array (
					'0' => $Extension['ExtensionKey'],	// label
					'1' => $Extension['ExtensionKey']		// value
				);

				array_push($tcaExtensions, $Value);
			}
		}

		$PA['items'] = $tcaExtensions;
	}

	/**
	 * @param  $PA
	 * @param  $fobj
	 * @return void
	 */
	public function getLanguages($PA, $fobj) {

		$extjsParams = NULL;
		$tcaLanguages = array();

			// get configuration object
		$this->getConfigurationObject($extjsParams);

			// get available languages
		$Languages = $this->confObj->getApplicationConfiguration('AvailableLanguages');

		if(is_array($Languages)) {
			foreach($Languages as $Language) {
				$Value = array (
					'0' => $Language['LanguageName'],		// label
					'1' => $Language['LanguageKey']			// value
				);

				array_push($tcaLanguages, $Value);
			}
		}


		$PA['items'] = $tcaLanguages;
	}

	/**
	 *
	 */
	private function getConfigurationObject($extjsParams) {

		if (!is_object($this->confObj) && !($this->confObj instanceof tx_snowbabel_Configuration)) {
			$this->confObj = t3lib_div::makeInstance('tx_snowbabel_Configuration', $extjsParams);
		}

	}

	/**
	 *
	 */
	private function getExtensionsObject() {
		if (!is_object($this->extObj) && !($this->extObj instanceof tx_snowbabel_extensions)) {
			$this->extObj = t3lib_div::makeInstance('tx_snowbabel_extensions', $this->confObj);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/TCA/class.tx_snowbabel_tca.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/TCA/class.tx_snowbabel_tca.php']);
}

?>