<?php
namespace Snowflake\Snowbabel\Service;

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

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Configuration
 *
 * @package Snowflake\Snowbabel\Service
 */
class Configuration {


	/**
	 * @var ConfigurationManager
	 */
	private $configurationManager;


	/**
	 * @var
	 */
	private $configuration;


	/**
	 * @var Database
	 */
	private $database;


	/**
	 * @var string
	 */
	private $xmlPath = '';


	/**
	 * @var
	 */
	public $debug;


	/**
	 * @var mixed
	 */
	private $extjsParams;


	/**
	 * @var array
	 */
	private $standartValues = array (
		'LocalExtensionPath' => 'typo3conf/ext/',
		'SystemExtensionPath' => 'typo3/sysext/',
		'GlobalExtensionPath' => 'typo3/ext/',

		'ShowLocalExtensions' => 1,
		'ShowSystemExtensions' => 1,
		'ShowGlobalExtensions' => 1,

		'ShowOnlyLoadedExtensions' => 1,
		'ShowTranslatedLanguages' => 0,

		'ApprovedExtensions' => '',

		'XmlFilter' => 1,

		'AutoBackupEditing' => 1,
		'AutoBackupCronjob' => 0,

		'CopyDefaultLanguage' => 1,

		'AvailableLanguages' => '30',

		'SchedulerCheck' => 0,

		'ConfigurationChanged' => 0
	);


	/**
	 * @param bool $extjsParams
	 */
	public function __construct($extjsParams = FALSE) {

		$this->xmlPath = 'snowbabel/Resources/Private/Language/locallang_translation.xlf';

		$this->extjsParams = $extjsParams;

		$this->loadConfiguration();

	}


	/**
	 * @param  $value
	 * @param  $name
	 * @return bool
	 */
	public function setExtensionConfiguration($value, $name) {

		$configurationSet = FALSE;

		if (isset($value, $name)) {

			$this->configuration['Extension'][$name] = $value;

			$configurationSet = TRUE;

		}

		return $configurationSet;

	}


	/**
	 * @param  $value
	 * @return bool
	 */
	public function setExtensionConfigurationLoadedExtensions($value) {

		$setConfiguration = FALSE;

		if (isset($value)) {

			$this->configuration['Extension']['LoadedExtensions'] = $value;

			$setConfiguration = TRUE;

		}

		return $setConfiguration;

	}


	/**
	 * @param  $value
	 * @param  $name
	 * @return bool
	 */
	public function setApplicationConfiguration($value, $name) {

		$setConfiguration = FALSE;

		if (isset($value, $name)) {

			$this->configuration['Application'][$name] = $value;

			$setConfiguration = TRUE;

		}

		return $setConfiguration;

	}


	/**
	 * @param  $value
	 * @param  $name
	 * @return bool
	 */
	public function setUserConfiguration($value, $name) {

		$setConfiguration = FALSE;

		if (isset($value, $name)) {

			$this->configuration['User'][$name] = $value;

			$setConfiguration = TRUE;

		}

		return $setConfiguration;

	}


	/**
	 * @param  $value
	 * @param  $name
	 * @return bool
	 */
	public function setUserConfigurationColumn($value, $name) {

		$setConfiguration = FALSE;

		if (isset($value, $name)) {

			// set 1 and 0 to true and false
			$value = $value ? TRUE : FALSE;

			$this->configuration['User']['Columns'][$name] = $value;

			$setConfiguration = TRUE;

		}

		return $setConfiguration;

	}


	/**
	 * @param  $extensionList
	 * @return bool
	 */
	public function setUserConfigurationExtensions($extensionList) {

		$setConfiguration = FALSE;

		if (is_array($extensionList)) {

			$this->configuration['User']['Extensions'] = $extensionList;

			$setConfiguration = TRUE;

		}

		return $setConfiguration;
	}


	/**
	 * @param  $extjsParams
	 * @return bool
	 */
	public function setExtjsConfiguration($extjsParams) {

		$setConfiguration = FALSE;

		if (is_array($extjsParams)) {

			$this->configuration['Extjs'] = $extjsParams;

			$setConfiguration = TRUE;

		}

		return $setConfiguration;

	}


	/**
	 * @return void
	 */
	public function saveFormSettings() {

		$newLocalconfValues = array ();

		// Get Old Values
		$localconfValues = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['snowbabel']);

		// Get Defined Extjs Values
		$extjsParams = $this->getExtjsConfigurationFormSettings();

		// Write Defined Extjs Values To Database
		$standardKeys = array_keys($this->standartValues);
		foreach ($standardKeys as $standartKey) {

			if (isset($extjsParams[$standartKey])) {
				// New Value From Submit
				$value = $extjsParams[$standartKey];
				$newLocalconfValues[$standartKey] = $value;

			} elseif (isset($localconfValues[$standartKey])) {
				// Already Defined in Localconf
				$newLocalconfValues[$standartKey] = $localconfValues[$standartKey];
			}
		}

		// Set Languages If Added
		$languages = $this->getExtjsConfiguration('AddedLanguages');
		if ($languages) {
			$newLocalconfValues['AvailableLanguages'] = $languages;
		}

		// Set Approved Extensions If Added
		$approvedExtensions = $this->getExtjsConfiguration('ApprovedExtensions');
		if ($approvedExtensions) {
			$newLocalconfValues['ApprovedExtensions'] = $approvedExtensions;
		}

		// Mark Configuration Changes As 'CHANGED'
		$newLocalconfValues['ConfigurationChanged'] = 1;

		// Write Localconf
		$this->writeLocalconfArray($newLocalconfValues);

	}


	/**
	 * @return void
	 */
	public function setSchedulerCheckAndChangedConfiguration() {

		// Get Localconf Values
		$localconfValues = $this->loadApplicationConfiguration(FALSE);

		if ($localconfValues['SchedulerCheck'] !== 1 || $localconfValues['ConfigurationChanged'] !== 0) {

			// Set Scheduler Check
			$localconfValues['SchedulerCheck'] = 1;

			// Set Scheduler Check
			$localconfValues['ConfigurationChanged'] = 0;

			// Write To Localconf
			$this->writeLocalconfArray($localconfValues);

		}

	}


	/**
	 * @return Database
	 */
	public function getDatabase() {
		return $this->database;
	}


	/**
	 * @param $labelName
	 * @return string
	 */
	public function getLocallang($labelName) {

		// use typo3 system function
		return $this->getLanguageService()->sL('LLL:EXT:' . $this->xmlPath . ':' . $labelName);

	}


	/**
	 * @return mixed
	 */
	public function getConfiguration() {
		return $this->configuration;
	}


	/**
	 * @param $name
	 * @return null|array
	 */
	public function getApplicationConfiguration($name) {

		$configuration = NULL;

		if (isset($name)) {

			$configuration = $this->configuration['Application'][$name];

		}

		return $configuration;

	}


	/**
	 * @param $name
	 * @return null
	 */
	public function getUserConfiguration($name) {

		$configuration = NULL;

		if (isset($name)) {

			$configuration = $this->configuration['User'][$name];

		}

		return $configuration;

	}


	/**
	 * @return
	 */
	public function getUserConfigurationColumns() {
		return $this->configuration['User']['Columns'];
	}


	/**
	 * @param $name
	 * @return null
	 */
	public function getUserConfigurationColumn($name) {

		$configuration = NULL;

		if (isset($name)) {

			$configuration = $this->configuration['User']['Columns'][$name];

		}

		return $configuration;
	}


	/**
	 * @return
	 */
	public function getUserConfigurationId() {
		return $this->configuration['User']['Id'];
	}


	/**
	 * @return
	 */
	public function getUserConfigurationIsAdmin() {
		return $this->configuration['User']['IsAdmin'];
	}


	/**
	 * @param  $name
	 * @return null
	 */
	public function getExtensionConfiguration($name) {

		$configuration = NULL;

		if (isset($name)) {

			$configuration = $this->configuration['Extension'][$name];

		}

		return $configuration;

	}


	/**
	 * @return
	 */
	public function getExtensionConfigurationLoadedExtensions() {
		return $this->configuration['Extension']['LoadedExtensions'];
	}


	/**
	 * @return null
	 */
	public function getExtjsConfigurations() {

		$configuration = NULL;

		if (count($this->configuration['Extjs']) > 0) {
			$configuration = $this->configuration['Extjs'];
		}

		return $configuration;
	}


	/**
	 * @param  $name
	 * @return null
	 */
	public function getExtjsConfiguration($name) {

		$configuration = NULL;

		if (isset($name)) {

			$configuration = $this->configuration['Extjs'][$name];

		}

		return $configuration;

	}


	/**
	 * @return
	 */
	public function getExtjsConfigurationListViewStart() {

		if ($this->configuration['Extjs']['start']) {
			$configuration = $this->configuration['Extjs']['start'];
		} else {
			$configuration = $this->configuration['Extjs']['ListViewStart'];
		}

		return $configuration;

	}


	/**
	 * @return array
	 */
	public function getExtjsConfigurationFormSettings() {

		$extjsParams['LocalExtensionPath'] = $this->configuration['Extjs']['LocalExtensionPath'];
		$extjsParams['SystemExtensionPath'] = $this->configuration['Extjs']['SystemExtensionPath'];
		$extjsParams['GlobalExtensionPath'] = $this->configuration['Extjs']['GlobalExtensionPath'];

		$extjsParams['ShowLocalExtensions'] = $this->configuration['Extjs']['ShowLocalExtensions'];
		$extjsParams['ShowSystemExtensions'] = $this->configuration['Extjs']['ShowSystemExtensions'];
		$extjsParams['ShowGlobalExtensions'] = $this->configuration['Extjs']['ShowGlobalExtensions'];

		$extjsParams['ShowOnlyLoadedExtensions'] = $this->configuration['Extjs']['ShowOnlyLoadedExtensions'];
		$extjsParams['ShowTranslatedLanguages'] = $this->configuration['Extjs']['ShowTranslatedLanguages'];

		$extjsParams['XmlFilter'] = $this->configuration['Extjs']['XmlFilter'];

		$extjsParams['AutoBackupEditing'] = $this->configuration['Extjs']['AutoBackupEditing'];
		$extjsParams['AutoBackupCronjob'] = $this->configuration['Extjs']['AutoBackupCronjob'];

		$extjsParams['CopyDefaultLanguage'] = $this->configuration['Extjs']['CopyDefaultLanguage'];

		foreach ($extjsParams as $key => $parameter) {

			if ($parameter === NULL) {
				$extjsParams[$key] = 0;
			}
			if ($parameter === 'on') {
				$extjsParams[$key] = 1;
			}

		}

		return $extjsParams;
	}


	/**
	 * @return mixed
	 */
	public function getExtjsConfigurationListViewLimit() {

		if ($this->configuration['Extjs']['limit']) {
			$configuration = $this->configuration['Extjs']['limit'];
		} else {
			$configuration = $this->configuration['Extjs']['ListViewLimit'];
		}

		return $configuration;

	}


	/**
	 * @param bool $availableLanguagesDiff
	 * @return array
	 */
	public function getLanguages($availableLanguagesDiff = FALSE) {

		$languages = $this->database->getStaticLanguages();

		if ($availableLanguagesDiff) {

			$availableLanguages = $this->getApplicationConfiguration('AvailableLanguages');

			if (is_array($languages)) {

				$languagesDiff = array ();

				foreach ($languages as $language) {

					if (!in_array($language, $availableLanguages)) {
						array_push($languagesDiff, $language);
					}

				}

				$languages = $languagesDiff;

			}
		}

		return $languages;

	}


	/**
	 * @param array $localconfValues
	 * @return void
	 */
	private function writeLocalconfArray(array $localconfValues) {

		if (!$this->configurationManager instanceof ConfigurationManager) {
			$this->configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Configuration\\ConfigurationManager');
		}

		$this->configurationManager->setLocalConfigurationValueByPath('EXT/extConf/snowbabel', serialize($localconfValues));

		ExtensionManagementUtility::removeCacheFiles();

	}


	/**
	 * @return void
	 */
	private function loadConfiguration() {

		// load db object
		$this->initDatabase();

		// load extjs parameters
		$this->loadExtjsConfiguration();

		// load extension configuration
		$this->loadExtensionConfiguration();

		// load application configuration
		$this->loadApplicationConfiguration();

		// load user configuration
		$this->loadUserConfiguration();

		// load actions
		$this->loadActions();
	}


	/**
	 * @return void
	 */
	private function loadExtjsConfiguration() {

		// check if its an object or array
		if (is_object($this->extjsParams)) {
			// extjs obj var to array
			$extjsParams = get_object_vars($this->extjsParams);
			// if something's in add it to conf
			if (!empty($extjsParams)) {
				$this->setExtjsConfiguration($extjsParams);
			}
		} elseif (is_array($this->extjsParams)) {
			// if something's in add it to conf
			if (!empty($this->extjsParams)) {
				$this->setExtjsConfiguration($this->extjsParams);
			}
		}

	}


	/**
	 * @return void
	 */
	private function loadExtensionConfiguration() {

		$this->setExtensionConfiguration(ExtensionManagementUtility::extPath('snowbabel'), 'ExtPath');

		$this->setExtensionConfigurationLoadedExtensions($GLOBALS['TYPO3_LOADED_EXT']);

		$this->setExtensionConfiguration(PATH_site, 'SitePath');

		$this->setExtensionConfiguration('typo3conf/l10n/', 'L10nPath');
	}


	/**
	 * @param bool $setConfiguration
	 * @return mixed
	 */
	private function loadApplicationConfiguration($setConfiguration = TRUE) {

		$localconfValues = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['snowbabel']);

		// Check Configuration
		if (!is_array($localconfValues) || count($this->standartValues) > count($localconfValues)) {

			// Otherwise Set StandartValue
			foreach ($this->standartValues as $standartKey => $standartValue) {
				if (!isset($localconfValues[$standartKey])) {
					$localconfValues[$standartKey] = $standartValue;
				}
			}

			// Write Configuration
			$this->writeLocalconfArray($localconfValues);

		}

		if ($setConfiguration) {
			// local extension path
			$this->setApplicationConfiguration($localconfValues['LocalExtensionPath'], 'LocalExtensionPath');
			// system extension path
			$this->setApplicationConfiguration($localconfValues['SystemExtensionPath'], 'SystemExtensionPath');
			// global extension path
			$this->setApplicationConfiguration($localconfValues['GlobalExtensionPath'], 'GlobalExtensionPath');

			// show local extension
			$this->setApplicationConfiguration($localconfValues['ShowLocalExtensions'], 'ShowLocalExtensions');
			// show system extension
			$this->setApplicationConfiguration($localconfValues['ShowSystemExtensions'], 'ShowSystemExtensions');
			// show global extension
			$this->setApplicationConfiguration($localconfValues['ShowGlobalExtensions'], 'ShowGlobalExtensions');

			// show only loaded extension
			$this->setApplicationConfiguration($localconfValues['ShowOnlyLoadedExtensions'], 'ShowOnlyLoadedExtensions');
			// show translated languages
			$this->setApplicationConfiguration($localconfValues['ShowTranslatedLanguages'], 'ShowTranslatedLanguages');

			// approved extensions
			$this->setApplicationConfiguration(explode(',', $localconfValues['ApprovedExtensions']), 'ApprovedExtensions');

			// xml filter
			$this->setApplicationConfiguration($localconfValues['XmlFilter'], 'XmlFilter');

			// auto backup during editing
			$this->setApplicationConfiguration($localconfValues['AutoBackupEditing'], 'AutoBackupEditing');
			// auto backup during cronjob
			$this->setApplicationConfiguration($localconfValues['AutoBackupCronjob'], 'AutoBackupCronjob');

			// copy default language to english (en)
			$this->setApplicationConfiguration($localconfValues['CopyDefaultLanguage'], 'CopyDefaultLanguage');

			// load available languages
			$this->setApplicationConfiguration(
				$this->database->getAppConfAvailableLanguages(
					$localconfValues['AvailableLanguages'],
					$this->getApplicationConfiguration('ShowTranslatedLanguages')
				),
				'AvailableLanguages'
			);

			// Scheduler Check
			$this->setApplicationConfiguration($localconfValues['SchedulerCheck'], 'SchedulerCheck');

			// Configuration Changed
			$this->setApplicationConfiguration($localconfValues['ConfigurationChanged'], 'ConfigurationChanged');
		}

		return $localconfValues;

	}


	/**
	 * @return void
	 */
	private function loadUserConfiguration() {

		// set admin mode
		$this->setUserConfiguration($GLOBALS['BE_USER']->user['admin'], 'IsAdmin');

		// set user id
		$this->setUserConfiguration($GLOBALS['BE_USER']->user['uid'], 'Id');

		// set user permitted extensions
		$this->setUserConfiguration(
			$this->getPermittedExtensions(
				$GLOBALS['BE_USER']->user['tx_snowbabel_extensions'],
				$GLOBALS['BE_USER']->userGroups
			),
			'PermittedExtensions'
		);

		// set user permitted languages
		$this->setUserConfiguration(
			$this->getPermittedLanguages(
				$GLOBALS['BE_USER']->user['tx_snowbabel_languages'],
				$GLOBALS['BE_USER']->userGroups
			),
			'PermittedLanguages'
		);

		// checks if database record already written
		$this->database->getUserConfCheck($this->getUserConfigurationId());

		// get selected languages
		$this->setUserConfiguration(
			$this->database->getUserConfSelectedLanguages($this->getUserConfigurationId()),
			'SelectedLanguages'
		);

		// get "showColumn" values from database
		$this->setUserConfigurationColumn(
			$this->database->getUserConfShowColumnLabel($this->getUserConfigurationId()),
			'ShowColumnLabel'
		);
		$this->setUserConfigurationColumn(
			$this->database->getUserConfShowColumnDefault($this->getUserConfigurationId()),
			'ShowColumnDefault'
		);

	}


	/**
	 * @return void
	 */
	private function loadActions() {

		$actionKey = $this->getExtjsConfiguration('ActionKey');
		$languageId = $this->getExtjsConfiguration('LanguageId');
		$columnId = $this->getExtjsConfiguration('ColumnId');

		if (!empty($languageId) && $actionKey == 'LanguageSelection') {
			$this->actionUserConfSelectedLanguages($languageId);
		}

		if (!empty($columnId) && $actionKey == 'ColumnSelection') {
			$this->actionUserConfigurationColumns($columnId);
		}

	}


	/**
	 * @param  $languageId
	 * @return void
	 */
	private function actionUserConfSelectedLanguages($languageId) {

		$selectedLanguages = $this->getUserConfiguration('SelectedLanguages');

		if (!GeneralUtility::inList($selectedLanguages, $languageId)) {
			// Add
			if (!$selectedLanguages) {
				$selectedLanguages = $languageId;
			} else {
				$selectedLanguages .= ',' . $languageId;
			}
		} else {
			// Remove
			$selectedLanguages = GeneralUtility::rmFromList($languageId, $selectedLanguages);
		}

		// Write Changes To Database
		$this->database->setUserConf('SelectedLanguages', $selectedLanguages, $this->getUserConfigurationId());

		// Reset Configuration Array
		$this->setUserConfiguration($selectedLanguages, 'SelectedLanguages');
	}


	/**
	 * @param  $columnId
	 * @return void
	 */
	private function actionUserConfigurationColumns($columnId) {

		$columnsConfiguration = $this->getUserConfigurationColumns();

		// Reverse Value
		$columnsConfiguration[$columnId] = !$columnsConfiguration[$columnId];

		// Write Changes To Database
		$this->database->setUserConf($columnId, $columnsConfiguration[$columnId], $this->getUserConfigurationId());

		// Reset Configuration Array
		$this->setUserConfiguration($columnsConfiguration, 'Columns');

	}


	/**
	 * @param  $permittedExtensions
	 * @param  $allocatedGroups
	 * @return string
	 */
	private function getPermittedExtensions($permittedExtensions, $allocatedGroups) {

		$allowedExtensions1 = array ();
		$allowedExtensions2 = array ();

		if ($permittedExtensions) {
			$values = explode(',', $permittedExtensions);
			foreach ($values as $extension) {
				array_push($allowedExtensions1, $extension);
			}
		}

		// Get Allocated Groups -> Group/User Permissions
		if (is_array($allocatedGroups)) {
			foreach ($allocatedGroups as $group) {
				if ($group['tx_snowbabel_extensions']) {
					$values = explode(',', $group['tx_snowbabel_extensions']);
					foreach ($values as $extension) {
						array_push($allowedExtensions2, $extension);
					}
				}
			}
		}

		// Merge Both Together
		$allowedExtensions = array_unique(array_merge($allowedExtensions1, $allowedExtensions2));

		return implode($allowedExtensions, ',');
	}


	/**
	 * @param  $permittedLanguages
	 * @param  $allocatedGroups
	 * @return string
	 */
	private function getPermittedLanguages($permittedLanguages, $allocatedGroups) {

		$allowedLanguages1 = array ();
		$allowedLanguages2 = array ();

		if ($permittedLanguages) {
			$values = explode(',', $permittedLanguages);
			foreach ($values as $extension) {
				array_push($allowedLanguages1, $extension);
			}
		}

		// Get Allocated Groups -> Group/User Permissions
		if (is_array($allocatedGroups)) {
			foreach ($allocatedGroups as $group) {
				if ($group['tx_snowbabel_languages']) {
					$values = explode(',', $group['tx_snowbabel_languages']);
					foreach ($values as $extension) {
						array_push($allowedLanguages2, $extension);
					}
				}
			}
		}

		// Merge Both Together
		$allowedLanguages = array_unique(array_merge($allowedLanguages1, $allowedLanguages2));

		return implode($allowedLanguages, ',');
	}


	/**
	 * @return void
	 */
	private function initDatabase() {

		if (!is_object($this->database) && !($this->database instanceof Database)) {
			$this->database = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Service\\Database', $this->debug);
		}

	}


	/**
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	private static function getLanguageService() {
		return $GLOBALS['LANG'];
	}

}