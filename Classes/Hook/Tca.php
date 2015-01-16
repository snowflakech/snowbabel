<?php
namespace Snowflake\Snowbabel\Hook;

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

use Snowflake\Snowbabel\Record\Extensions;
use Snowflake\Snowbabel\Service\Configuration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Tca
 *
 * @package Snowflake\Snowbabel\Hook
 */
class Tca {


	/**
	 * @var    Configuration
	 */
	private $confObj;


	/**
	 * @var    Extensions
	 */
	private $extObj;


	/**
	 * @param  $pa
	 * @param  $fobj
	 * @return void
	 */
	public function getExtensions($pa, $fobj) {

		$extjsParams = NULL;
		$tcaExtensions = array ();

		// get configuration object
		$this->getConfigurationObject($extjsParams);

		// get extension object
		$this->getExtensionsObject();

		// get all extensions for this user
		$extensions = $this->extObj->getExtensions();

		if (is_array($extensions)) {
			foreach ($extensions as $extension) {
				$value = array (
					// Label
					'0' => $extension['ExtensionKey'],
					// Value
					'1' => $extension['ExtensionKey']
				);

				array_push($tcaExtensions, $value);
			}
		}

		$pa['items'] = $tcaExtensions;
	}


	/**
	 * @param  $pa
	 * @param  $fobj
	 * @return void
	 */
	public function getLanguages($pa, $fobj) {

		$extjsParams = NULL;
		$tcaLanguages = array ();

		// get configuration object
		$this->getConfigurationObject($extjsParams);

		// get available languages
		$languages = $this->confObj->getApplicationConfiguration('AvailableLanguages');

		if (is_array($languages)) {
			foreach ($languages as $language) {
				$value = array (
					// Label
					'0' => $language['LanguageName'],
					// Value
					'1' => $language['LanguageKey']
				);

				array_push($tcaLanguages, $value);
			}
		}


		$pa['items'] = $tcaLanguages;
	}


	/**
	 * @param $extjsParams
	 * @return void
	 */
	private function getConfigurationObject($extjsParams) {

		if (!is_object($this->confObj) && !($this->confObj instanceof Configuration)) {
			$this->confObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Service\\Configuration', $extjsParams);
		}

	}


	/**
	 * @return void
	 */
	private function getExtensionsObject() {
		if (!is_object($this->extObj) && !($this->extObj instanceof Extensions)) {
			$this->extObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Record\Extensions', $this->confObj);
		}
	}
}