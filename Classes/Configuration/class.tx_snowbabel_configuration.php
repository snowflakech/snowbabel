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
class tx_snowbabel_Configuration {

	/**
	 * @var
	 */
	private $configuration;

	/**
	 * @var tx_snowbabel_db
	 */
	private $db;

	/**
	 * @var string
	 */
	private $xmlPath = '';

	/**
	 * @var
	 */
	public $debug;

	/**
	 * @var bool
	 */
	private $extjsParams;

	/**
	 * @var array
	 */
	private $StandartValues = array(
		'LocalExtensionPath'		=> 'typo3conf/ext/',
		'SystemExtensionPath'		=> 'typo3/sysext/',
		'GlobalExtensionPath'		=> 'typo3/ext/',

		'ShowLocalExtensions'		=> 1,
		'ShowSystemExtensions'		=> 1,
		'ShowGlobalExtensions'		=> 1,

		'ShowOnlyLoadedExtensions'	=> 1,
		'ShowTranslatedLanguages'	=> 0,

		'BlacklistedExtensions'		=> 't3quixplorer,indexed_search,rtehtmlarea,t3editor,sv,sys_action,t3skin,belog,ics_awstats,ics_web_awstats,phpmyadmin,terminal,api_macmade,css_styled_content',
		'BlacklistedCategories'		=> 'module,services,misc,be',

		'WhitelistedActivated'		=> 0,
		'WhitelistedExtensions'		=> '',

		'XmlFilter'					=> 1,

		'AutoBackupEditing'			=> 1,
		'AutoBackupCronjob'			=> 0,

		'CopyDefaultLanguage'		=> 1,

		'AvailableLanguages'		=> '30',

		'SchedulerCheck'			=> 0,

		'ConfigurationChanged'		=> 0
	);

	/**
	 * @var
	 */
	private $version;

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

// PUBLIC

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

	/**
	 * @param bool $extjsParams
	 */
	public function __construct($extjsParams=false) {

		$this->setTypo3Version(
			class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) : t3lib_div::int_from_ver(TYPO3_version)
		);

		// TODO: Same Language File For All Applications!!!

			// Typo3 4.6 & Above
		if ($this->getTypo3Version() >= 4006000) {
			$this->xmlPath = 'snowbabel/Resources/Private/Language/locallang_translation.xlf';
		}
			// Lower Then Typo3 4.6
		else {
			$this->xmlPath = 'snowbabel/Resources/Private/Language/locallang_translation.xml';
		}

		$this->extjsParams = $extjsParams;

		$this->loadConfiguration();

	}



///////////////////////////////////////////////////////
// write config - set
///////////////////////////////////////////////////////

	/**
	 * @param  $value
	 * @param  $name
	 * @return bool
	 */
	public function setExtensionConfiguration($value, $name) {

		if(isset($value, $name)) {

			$this->configuration['Extension'][$name] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 * @param  $value
	 * @return bool
	 */
	public function setExtensionConfigurationLoadedExtensions($value) {

		if(isset($value)) {

			$this->configuration['Extension']['LoadedExtensions'] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 * @param  $value
	 * @param  $name
	 * @return bool
	 */
	public function setApplicationConfiguration($value, $name) {

		if(isset($value, $name)) {

			$this->configuration['Application'][$name] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 * @param  $value
	 * @param  $name
	 * @return bool
	 */
	public function setUserConfiguration($value, $name) {

		if(isset($value, $name)) {

			$this->configuration['User'][$name] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 * @param  $value
	 * @param  $name
	 * @return bool
	 */
	public function setUserConfigurationColumn($value, $name) {

		if(isset($value, $name)) {

				// set 1 and 0 to true and false
			$value = $value ? true : false;

			$this->configuration['User']['Columns'][$name] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 * @param  $ExtensionList
	 * @return bool
	 */
	public function setUserConfigurationExtensions($ExtensionList) {

		if(is_array($ExtensionList)) {

			$this->configuration['User']['Extensions'] = $ExtensionList;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 * @param  $ExtjsParams
	 * @return bool
	 */
	public function setExtjsConfiguration($ExtjsParams) {

		if(is_array($ExtjsParams)) {

			$this->configuration['Extjs'] = $ExtjsParams;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 * @return void
	 */
	public function saveFormSettings() {

		$NewLocalconfValues = array();

			// Get Old Values
		$LocalconfValues = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['snowbabel']);

			// Get Defined Extjs Values
		$ExtjsParams = $this->getExtjsConfigurationFormSettings();

		// Write Defined Extjs Values To Database
		foreach($this->StandartValues as $StandartKey => $StandartValue) {

				// New Value From Submit
			if(isset($ExtjsParams[$StandartKey])) {

				$Value = $ExtjsParams[$StandartKey];
				$NewLocalconfValues[$StandartKey] = $Value;

			}
				// Already Defined in Localconf
			elseif(isset($LocalconfValues[$StandartKey])) {
				$NewLocalconfValues[$StandartKey] = $LocalconfValues[$StandartKey];
			}
		}


			// Set Languages If Added
		$Languages = $this->getExtjsConfiguration('AddedLanguages');
		if($Languages) $NewLocalconfValues['AvailableLanguages'] = $Languages;

			// Set Whitelisted Extensions If Added
		$WhitelistedExtensions = $this->getExtjsConfiguration('WhitelistedExtensions');
		if($WhitelistedExtensions) $NewLocalconfValues['WhitelistedExtensions'] = $WhitelistedExtensions;

			// Mark Configuration Changes As 'CHANGED'
		$NewLocalconfValues['ConfigurationChanged'] = 1;

			// Write Localconf
		$this->writeLocalconfArray($NewLocalconfValues);

	}

	/**
	 * @return void
	 */
	public function setSchedulerCheckAndChangedConfiguration() {

			// Get Localconf Values
		$LocalconfValues = $this->loadApplicationConfiguration(false);

		if($LocalconfValues['SchedulerCheck'] !== 1 || $LocalconfValues['ConfigurationChanged'] !== 0) {

				// Set Scheduler Check
			$LocalconfValues['SchedulerCheck'] = 1;

				// Set Scheduler Check
			$LocalconfValues['ConfigurationChanged'] = 0;

				// Write To Localconf
			$this->writeLocalconfArray($LocalconfValues);

		}

	}

///////////////////////////////////////////////////////
// read config - get
///////////////////////////////////////////////////////

	public function getTypo3Version() {
		return $this->version;
	}

	/**
	 * @return tx_snowbabel_db
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * @param  $LabelName
	 * @return
	 */
	public function getLL($LabelName) {

			// use typo3 system function
		return $GLOBALS['LANG']->sL('LLL:EXT:' . $this->xmlPath . ':' . $LabelName);

	}

	/**
	 * @return
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 * @param  $name
	 * @return null
	 */
	public function getApplicationConfiguration($name) {

		if(isset($name)) {

			return $this->configuration['Application'][$name];

		}
		else {
			return NULL;
		}

	}

	/**
	 * @param  $name
	 * @return null
	 */
	public function getUserConfiguration($name) {

		if(isset($name)) {

			return $this->configuration['User'][$name];

		}
		else {
			return NULL;
		}

	}

	/**
	 * @return
	 */
	public function getUserConfigurationColumns() {
		return $this->configuration['User']['Columns'];
	}

	/**
	 * @param  $name
	 * @return null
	 */
	public function getUserConfigurationColumn($name) {
		if(isset($name)) {

			return $this->configuration['User']['Columns'][$name];

		}
		else {
			return NULL;
		}
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

		if(isset($name)) {

			return $this->configuration['Extension'][$name];

		}
		else {
			return NULL;
		}

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
		if(count($this->configuration['Extjs']) > 0) {
			return $this->configuration['Extjs'];
		}
		else {
			return NULL;
		}
	}

	/**
	 * @param  $name
	 * @return null
	 */
	public function getExtjsConfiguration($name) {

		if(isset($name)) {

			return $this->configuration['Extjs'][$name];

		}
		else {
			return NULL;
		}

	}

	/**
	 * @return
	 */
	public function getExtjsConfigurationListViewStart() {

		if($this->configuration['Extjs']['start']) {
			return $this->configuration['Extjs']['start'];
		}
		else {
			return $this->configuration['Extjs']['ListViewStart'];
		}

	}

	/**
	 * @return array
	 */
	public function getExtjsConfigurationFormSettings() {

		$ExtjsParams['LocalExtensionPath']			= $this->configuration['Extjs']['LocalExtensionPath'];
		$ExtjsParams['SystemExtensionPath']			= $this->configuration['Extjs']['SystemExtensionPath'];
		$ExtjsParams['GlobalExtensionPath']			= $this->configuration['Extjs']['GlobalExtensionPath'];

		$ExtjsParams['ShowLocalExtensions']			= $this->configuration['Extjs']['ShowLocalExtensions'];
		$ExtjsParams['ShowSystemExtensions']		= $this->configuration['Extjs']['ShowSystemExtensions'];
		$ExtjsParams['ShowGlobalExtensions']		= $this->configuration['Extjs']['ShowGlobalExtensions'];

		$ExtjsParams['ShowOnlyLoadedExtensions']	= $this->configuration['Extjs']['ShowOnlyLoadedExtensions'];
		$ExtjsParams['ShowTranslatedLanguages']		= $this->configuration['Extjs']['ShowTranslatedLanguages'];

		$ExtjsParams['BlacklistedExtensions']		= $this->configuration['Extjs']['BlacklistedExtensions'];
		$ExtjsParams['BlacklistedCategories']		= $this->configuration['Extjs']['BlacklistedCategories'];

		$ExtjsParams['WhitelistedActivated']		= $this->configuration['Extjs']['WhitelistedActivated'];

		$ExtjsParams['XmlFilter']					= $this->configuration['Extjs']['XmlFilter'];

		$ExtjsParams['AutoBackupEditing']			= $this->configuration['Extjs']['AutoBackupEditing'];
		$ExtjsParams['AutoBackupCronjob']			= $this->configuration['Extjs']['AutoBackupCronjob'];

		$ExtjsParams['CopyDefaultLanguage']			= $this->configuration['Extjs']['CopyDefaultLanguage'];

		foreach($ExtjsParams as $Key => $Param) {

			if($Param === NULL) $ExtjsParams[$Key] = 0;
			if($Param === 'on') $ExtjsParams[$Key] = 1;

		}

		return $ExtjsParams;
	}

	/**
	 * @return
	 */
	public function getExtjsConfigurationListViewLimit() {

		if($this->configuration['Extjs']['limit']) {
			return $this->configuration['Extjs']['limit'];
		}
		else {
			return $this->configuration['Extjs']['ListViewLimit'];
		}

	}

	/**
	 * @param bool $AvailableLanguagesDiff
	 * @return array
	 */
	public function getLanguages($AvailableLanguagesDiff = false) {

		//TODO: Get System Languages And Merge With Static
		// $this->db->getSystemLanguages();

		$Languages = $this->db->getStaticLanguages();

		if($AvailableLanguagesDiff) {

			$AvailableLanguages = $this->getApplicationConfiguration('AvailableLanguages');

			if(is_array($Languages)) {

				$LanguagesDiff = array();

				foreach($Languages as $Language) {

					if(!in_array($Language, $AvailableLanguages)) {
						array_push($LanguagesDiff, $Language);
					}

				}

				$Languages = $LanguagesDiff;

			}
		}

		return $Languages;


	}

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

// PRIVATE

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

	/**
	 * @param int $typo3Version
	 * @return void
	 */
	private function setTypo3Version($typo3Version) {
		$this->version = $typo3Version;
	}

	/**
	 * @param array $LocalconfValues
	 * @return void
	 */
	private function writeLocalconfArray(array $LocalconfValues) {

		// Instance of install tool
		$instObj = new t3lib_install;
		$instObj->allowUpdateLocalConf = 1;
		$instObj->updateIdentity = 'Snowbabel';

		// Get lines from localconf file
		$lines = $instObj->writeToLocalconf_control();
		$instObj->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'EXT\'][\'extConf\'][\'snowbabel\']', serialize($LocalconfValues));
		$instObj->writeToLocalconf_control($lines);

		t3lib_extMgm::removeCacheFiles();

		// TODO:
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
		if(is_object($this->extjsParams)) {
				// extjs obj var to array
			$extjsParams = get_object_vars($this->extjsParams);
				// if something's in add it to conf
			if(!empty($extjsParams)) $this->setExtjsConfiguration($extjsParams);
		}
		elseif(is_array($this->extjsParams)) {
				// if something's in add it to conf
			if(!empty($this->extjsParams)) $this->setExtjsConfiguration($this->extjsParams);
		}

	}

	/**
	 * @return void
	 */
	private function loadExtensionConfiguration() {

		$this->setExtensionConfiguration(t3lib_extMgm::extPath('snowbabel'), 'ExtPath');

		$this->setExtensionConfigurationLoadedExtensions($GLOBALS['TYPO3_LOADED_EXT']);

		$this->setExtensionConfiguration(PATH_site, 'SitePath');

		$this->setExtensionConfiguration('typo3conf/l10n/', 'L10nPath');
	}

	/**
	 * @param bool $SetConfiguration
	 * @return mixed
	 */
	private function loadApplicationConfiguration($SetConfiguration = true) {



		$LocalconfValues = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['snowbabel']);

			// Check Configuration
		if(!is_array($LocalconfValues) || count($this->StandartValues) > count($LocalconfValues)) {

				// Otherwise Set StandartValue
			foreach($this->StandartValues as $StandartKey => $StandartValue) {
				if(!isset($LocalconfValues[$StandartKey])) {
					$LocalconfValues[$StandartKey] = $StandartValue;
				}
			}

				// Write Configuration
			$this->writeLocalconfArray($LocalconfValues);

		}

		if($SetConfiguration) {
				// local extension path
			$this->setApplicationConfiguration($LocalconfValues['LocalExtensionPath'], 'LocalExtensionPath');
				// system extension path
			$this->setApplicationConfiguration($LocalconfValues['SystemExtensionPath'], 'SystemExtensionPath');
				// global extension path
			$this->setApplicationConfiguration($LocalconfValues['GlobalExtensionPath'], 'GlobalExtensionPath');


				// show local extension
			$this->setApplicationConfiguration($LocalconfValues['ShowLocalExtensions'], 'ShowLocalExtensions');
				// show system extension
			$this->setApplicationConfiguration($LocalconfValues['ShowSystemExtensions'], 'ShowSystemExtensions');
				// show global extension
			$this->setApplicationConfiguration($LocalconfValues['ShowGlobalExtensions'], 'ShowGlobalExtensions');


				// show only loaded extension
			$this->setApplicationConfiguration($LocalconfValues['ShowOnlyLoadedExtensions'], 'ShowOnlyLoadedExtensions');
				// show translated languages
			$this->setApplicationConfiguration($LocalconfValues['ShowTranslatedLanguages'], 'ShowTranslatedLanguages');


				// blacklist extensions
			$this->setApplicationConfiguration($LocalconfValues['BlacklistedExtensions'], 'BlacklistedExtensions');
				// blacklist categories
			$this->setApplicationConfiguration($LocalconfValues['BlacklistedCategories'], 'BlacklistedCategories');

				// whitelist activated
			$this->setApplicationConfiguration($LocalconfValues['WhitelistedActivated'], 'WhitelistedActivated');

				// whitelisted extensions
			$this->setApplicationConfiguration(explode(",", $LocalconfValues['WhitelistedExtensions']), 'WhitelistedExtensions');

				// xml filter
			$this->setApplicationConfiguration($LocalconfValues['XmlFilter'], 'XmlFilter');


				// auto backup during editing
			$this->setApplicationConfiguration($LocalconfValues['AutoBackupEditing'], 'AutoBackupEditing');
				// auto backup during cronjob
			$this->setApplicationConfiguration($LocalconfValues['AutoBackupCronjob'], 'AutoBackupCronjob');


				// copy default language to english (en)
			$this->setApplicationConfiguration($LocalconfValues['CopyDefaultLanguage'], 'CopyDefaultLanguage');


				// load available languages
			$this->setApplicationConfiguration(
				$this->db->getAppConfAvailableLanguages(
					$LocalconfValues['AvailableLanguages'],
					$this->getApplicationConfiguration('ShowTranslatedLanguages')
				),
				'AvailableLanguages'
			);

				// Scheduler Check
			$this->setApplicationConfiguration($LocalconfValues['SchedulerCheck'], 'SchedulerCheck');

				// Configuration Changed
			$this->setApplicationConfiguration($LocalconfValues['ConfigurationChanged'], 'ConfigurationChanged');
		}
		else {
			return $LocalconfValues;
		}
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
		$this->setUserConfiguration($this->getPermittedExtensions($GLOBALS['BE_USER']->user['tx_snowbabel_extensions'], $GLOBALS['BE_USER']->userGroups), 'PermittedExtensions');

			// set user permitted languages
		$this->setUserConfiguration($this->getPermittedLanguages($GLOBALS['BE_USER']->user['tx_snowbabel_languages'], $GLOBALS['BE_USER']->userGroups), 'PermittedLanguages');


			// checks if database record already written
		$this->db->getUserConfCheck($this->getUserConfigurationId());

			// get selected languages
		$this->setUserConfiguration($this->db->getUserConfSelectedLanguages($this->getUserConfigurationId()), 'SelectedLanguages');

			// get "showColumn" values from database
		$this->setUserConfigurationColumn($this->db->getUserConfShowColumnLabel($this->getUserConfigurationId()), 'ShowColumnLabel');
		$this->setUserConfigurationColumn($this->db->getUserConfShowColumnDefault($this->getUserConfigurationId()), 'ShowColumnDefault');

	}

	/**
	 * @return void
	 */
	private function loadActions() {

		$ActionKey = $this->getExtjsConfiguration('ActionKey');
		$LanguageId = $this->getExtjsConfiguration('LanguageId');
		$ColumnId = $this->getExtjsConfiguration('ColumnId');

		if(!empty($LanguageId) && $ActionKey == 'LanguageSelection') {
			$this->actionUserConfSelectedLanguages($LanguageId);
		}

		if(!empty($ColumnId) && $ActionKey == 'ColumnSelection') {
			$this->actionUserConfigurationColumns($ColumnId);
		}

	}

	/**
	 * @param  $LanguageId
	 * @return void
	 */
	private function actionUserConfSelectedLanguages($LanguageId) {

		$SelectedLanguages  = $this->getUserConfiguration('SelectedLanguages');

			// Add
		if(!t3lib_div::inList($SelectedLanguages, $LanguageId)) {
			if(!$SelectedLanguages) {
				$SelectedLanguages = $LanguageId;
			}
			else {
				$SelectedLanguages .= ',' . $LanguageId;
			}
		}
			// Remove
		else {
			$SelectedLanguages = t3lib_div::rmFromList($LanguageId, $SelectedLanguages);
		}

			// Write Changes To Database
		$this->db->setUserConf('SelectedLanguages', $SelectedLanguages, $this->getUserConfigurationId());

			// Reset Configuration Array
		$this->setUserConfiguration($SelectedLanguages, 'SelectedLanguages');
	}

	/**
	 * @param  $ColumnId
	 * @return void
	 */
	private function actionUserConfigurationColumns($ColumnId) {

		$ColumnsConfiguration = $this->getUserConfigurationColumns();

			// Reverse Value
		$ColumnsConfiguration[$ColumnId] = !$ColumnsConfiguration[$ColumnId];

			// Write Changes To Database
		$this->db->setUserConf($ColumnId, $ColumnsConfiguration[$ColumnId], $this->getUserConfigurationId());

			// Reset Configuration Array
		$this->setUserConfiguration($ColumnsConfiguration, 'Columns');

	}

	/**
	 * @param  $PermittedExtensions
	 * @param  $AllocatedGroups
	 * @return string
	 */
	private function getPermittedExtensions($PermittedExtensions, $AllocatedGroups) {

		$AllowedExtensions1 = array();
		$AllowedExtensions2 = array();

		if ($PermittedExtensions) {
			 $Values = explode(',',$PermittedExtensions);
			 foreach($Values as $Extension) {
				 array_push($AllowedExtensions1, $Extension);
			 }
		}

			// Get Allocated Groups -> Group/User Permissions
		if(is_array($AllocatedGroups)){
			foreach($AllocatedGroups as $group){
				if ($group['tx_snowbabel_extensions']) {
					$Values = explode(',',$group['tx_snowbabel_extensions']);
					 foreach($Values as $Extension) {
						 array_push($AllowedExtensions2, $Extension);
					 }
				}
			}
		}

			// Merge Both Together
		$AllowedExtensions = array_unique(array_merge($AllowedExtensions1, $AllowedExtensions2));

		return implode($AllowedExtensions, ',');
	}

	/**
	 * @param  $PermittedLanguages
	 * @param  $AllocatedGroups
	 * @return string
	 */
	private function getPermittedLanguages($PermittedLanguages, $AllocatedGroups) {

		$AllowedLanguages1 = array();
		$AllowedLanguages2 = array();

		if ($PermittedLanguages) {
			 $Values = explode(',',$PermittedLanguages);
			 foreach($Values as $Extension) {
				 array_push($AllowedLanguages1, $Extension);
			 }
		}

			// Get Allocated Groups -> Group/User Permissions
		if(is_array($AllocatedGroups)){
			foreach($AllocatedGroups as $group){
				if ($group['tx_snowbabel_languages']) {
					$Values = explode(',',$group['tx_snowbabel_languages']);
					 foreach($Values as $Extension) {
						 array_push($AllowedLanguages2, $Extension);
					 }
				}
			}
		}

			// Merge Both Together
		$AllowedLanguages = array_unique(array_merge($AllowedLanguages1, $AllowedLanguages2));

		return implode($AllowedLanguages, ',');
	}

	/**
	 * @return void
	 */
	private function initDatabase() {

		if (!is_object($this->db) && !($this->db instanceof tx_snowbabel_Db)) {
			$this->db = t3lib_div::makeInstance('tx_snowbabel_Db', $this->debug);
		}

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Configuration/class.tx_snowbabel_configuration.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Configuration/class.tx_snowbabel_configuration.php']);
}

?>