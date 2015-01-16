<?php
namespace Snowflake\Snowbabel\Connection;

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

use Snowflake\Snowbabel\Record\Columns;
use Snowflake\Snowbabel\Record\Extensions;
use Snowflake\Snowbabel\Record\Labels;
use Snowflake\Snowbabel\Record\Languages;
use Snowflake\Snowbabel\Service\Configuration;
use Snowflake\Snowbabel\Service\Translations;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtDirectServer
 *
 * @package Snowflake\Snowbabel\Connection
 */
class ExtDirectServer {


	/**
	 * @var    Configuration
	 */
	private $confObj;


	/**
	 * @var    Extensions
	 */
	private $extObj;


	/**
	 * @var    Labels
	 */
	private $labelsObj;


	/**
	 * @var    Languages
	 */
	private $langObj;


	/**
	 * @var    Columns
	 */
	private $colObj;


	/**
	 * @var    Translations
	 */
	private $systemTranslationObj;


	/**
	 * @param $extjsParams
	 * @return null
	 */
	public function getExtensionMenu($extjsParams) {

		// get configuration object
		$this->getConfigurationObject($extjsParams);

		// get extension object
		$this->getExtensionsObject();

		// get all extensions for this user
		$extensions = $this->extObj->getExtensions();

		return $extensions;
	}


	/**
	 * @param $extjsParams
	 * @return array
	 */
	public function getLanguageSelection($extjsParams) {

		// get configuration object
		$this->getConfigurationObject($extjsParams);

		// get language object
		$this->getLanguageObject();

		// get available languages
		$languages = $this->langObj->getLanguages();

		return $languages;
	}


	/**
	 * @param $extjsParams
	 * @return array
	 */
	public function getColumnSelection($extjsParams) {

		// get configuration object
		$this->getConfigurationObject($extjsParams);

		// get column object
		$this->getColumnObject();

		// get available columns
		$columns = $this->colObj->getColumns();

		return $columns;
	}


	/**
	 * @param $extjsParams
	 * @return null
	 */
	public function getListView($extjsParams) {

		// get configuration object
		$this->getConfigurationObject($extjsParams);

		// Get Label Object
		$this->getLabelsObject();

		if ($extjsParams->SearchGlobal) {
			// Do Global Search

			// Set Metadata For Extjs
			$this->labelsObj->setMetaData();

			// Get Labels From Global Search
			$labels = $this->labelsObj->getSearchGlobal();

		} elseif (!empty($extjsParams->SearchString) && !empty($extjsParams->ExtensionId)) {
			// Do Extension Search

			// Set Metadata For Extjs
			$this->labelsObj->setMetaData();

			// Get Labels From Extension Search
			$labels = $this->labelsObj->getSearchExtension();

		} elseif (!empty($extjsParams->ExtensionId)) {
			// Show Extension Labels

			// Set Metadata For Extjs
			$this->labelsObj->setMetaData();

			// Get Labels From Selected Extension
			$labels = $this->labelsObj->getLabels();

		} else {
			$labels = NULL;
		}

		return $labels;
	}


	/**
	 * @param  $extjsParams
	 * @return bool
	 */
	public function ActionController($extjsParams) {
		// get configuration object
		$this->getConfigurationObject($extjsParams);

		if ($extjsParams->ActionKey == 'ListView_Update') {
			// Get Label Object
			$this->getLabelsObject();

			// Update Translation
			$this->labelsObj->updateTranslation();

		} elseif ($extjsParams->ActionKey == 'CheckScheduler') {

			// Did Scheduler Run Once?
			if ($this->confObj->getApplicationConfiguration('SchedulerCheck')) {
				return TRUE;
			} else {
				return NULL;
			}
		} elseif ($extjsParams->ActionKey == 'ConfigurationChanged') {

			// Did Configuration Changed?
			if (!$this->confObj->getApplicationConfiguration('ConfigurationChanged')) {
				return TRUE;
			} else {
				return NULL;
			}

		}

		return TRUE;
	}


	/**
	 * @return array
	 */
	public function getGeneralSettings() {

		$extjsParams = array ();

		// get configuration object
		$this->getConfigurationObject($extjsParams);

		// Set Values
		$formData['success'] = TRUE;

		// Get All Values From Configuration
		$formData['data']['LocalExtensionPath'] = $this->confObj->getApplicationConfiguration('LocalExtensionPath');
		$formData['data']['SystemExtensionPath'] = $this->confObj->getApplicationConfiguration('SystemExtensionPath');
		$formData['data']['GlobalExtensionPath'] = $this->confObj->getApplicationConfiguration('GlobalExtensionPath');

		$formData['data']['ShowLocalExtensions'] = $this->confObj->getApplicationConfiguration('ShowLocalExtensions') ? 1 : 0;
		$formData['data']['ShowSystemExtensions'] = $this->confObj->getApplicationConfiguration('ShowSystemExtensions') ? 1 : 0;
		$formData['data']['ShowGlobalExtensions'] = $this->confObj->getApplicationConfiguration('ShowGlobalExtensions') ? 1 : 0;

		$formData['data']['ShowOnlyLoadedExtensions'] = $this->confObj->getApplicationConfiguration('ShowOnlyLoadedExtensions') ? 1 : 0;
		$formData['data']['ShowTranslatedLanguages'] = $this->confObj->getApplicationConfiguration('ShowTranslatedLanguages') ? 1 : 0;

		$formData['data']['XmlFilter'] = $this->confObj->getApplicationConfiguration('XmlFilter') ? 1 : 0;

		$formData['data']['AutoBackupEditing'] = $this->confObj->getApplicationConfiguration('AutoBackupEditing') ? 1 : 0;
		$formData['data']['AutoBackupCronjob'] = $this->confObj->getApplicationConfiguration('AutoBackupCronjob') ? 1 : 0;

		$formData['data']['CopyDefaultLanguage'] = $this->confObj->getApplicationConfiguration('CopyDefaultLanguage') ? 1 : 0;

		return $formData;

	}


	/**
	 * @formHandler
	 * @param $extjsParams
	 * @return array
	 */
	public function submitGeneralSettings($extjsParams) {

		// get configuration object
		$this->getConfigurationObject($extjsParams);

		// save form
		$this->confObj->saveFormSettings();

		return array ('success' => TRUE);

	}


	/**
	 * @param $extjsParams
	 * @return array
	 */
	public function getGeneralSettingsLanguages($extjsParams) {

		// Todo: check logic
		$extjsParams = array ();

		// Get Configuration Object
		$this->getConfigurationObject($extjsParams);

		// Set Values
		$languages = $this->confObj->getLanguages(TRUE);

		return $languages;

	}


	/**
	 * @param $extjsParams
	 * @return null
	 */
	public function getGeneralSettingsLanguagesAdded($extjsParams) {

		// todo: check logic
		$extjsParams = array ();

		// Get Configuration Object
		$this->getConfigurationObject($extjsParams);

		// Set Values
		$languages = $this->confObj->getApplicationConfiguration('AvailableLanguages');

		return $languages;

	}


	/**
	 * @param $extjsParams
	 * @return array
	 *
	 * todo: renaming
	 */
	public function getGeneralSettingsApprovedExtensions($extjsParams) {

		// Todo: check logic
		$extjsParams = array ();
		$extensionArray = array ();

		// Get Configuration Object
		$this->getConfigurationObject($extjsParams);

		// Get System Translation Object
		$this->getSystemTranslationObject();

		// Init System Translation Object
		$this->systemTranslationObj->init($this->confObj);

		// Get All Available Extensions
		$extensions = $this->systemTranslationObj->getDirectories();

		// Get Approved Extensions
		$approvedExtensions = $this->confObj->getApplicationConfiguration('ApprovedExtensions');

		// Prepare For Output
		if (is_array($extensions) && count($extensions) > 0) {
			foreach ($extensions as $extension) {

				// Do Not Add Extension If Already Approved
				if (!in_array($extension, $approvedExtensions)) {
					array_push($extensionArray, array ('ExtensionKey' => $extension));
				}

			}
		}

		return $extensionArray;

	}


	/**
	 * @param $extjsParams
	 * @return null
	 *
	 * todo: renaming
	 */
	public function getGeneralSettingsApprovedExtensionsAdded($extjsParams) {

		// todo: check logic
		$extjsParams = array ();
		$approvedExtensionsArray = array ();

		// Get Configuration Object
		$this->getConfigurationObject($extjsParams);

		// Set Values
		$approvedExtensions = $this->confObj->getApplicationConfiguration('ApprovedExtensions');

		// Prepare For Output
		if (is_array($approvedExtensions) && count($approvedExtensions) > 0) {
			foreach ($approvedExtensions as $approvedExtension) {
				array_push($approvedExtensionsArray, array ('ExtensionKey' => $approvedExtension));
			}
		}

		return $approvedExtensionsArray;

	}


	/**
	 * @param  $extjsParams
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
			$this->extObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Record\\Extensions', $this->confObj);
		}
	}


	/**
	 * @return void
	 */
	private function getLabelsObject() {
		if (!is_object($this->labelsObj) && !($this->labelsObj instanceof Labels)) {
			$this->labelsObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Record\\Labels', $this->confObj);
		}
	}


	/**
	 * @return void
	 */
	private function getLanguageObject() {
		if (!is_object($this->langObj) && !($this->langObj instanceof Languages)) {
			$this->langObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Record\\Languages', $this->confObj);
		}
	}


	/**
	 * @return void
	 */
	private function getColumnObject() {
		if (!is_object($this->colObj) && !($this->colObj instanceof Columns)) {
			$this->colObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Record\\Columns', $this->confObj);
		}
	}


	/**
	 * @return void
	 */
	private function getSystemTranslationObject() {
		if (!is_object($this->systemTranslationObj) && !($this->systemTranslationObj instanceof Translations)) {
			$this->systemTranslationObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Service\\Translations');
		}
	}

}