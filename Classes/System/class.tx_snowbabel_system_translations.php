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
class tx_snowbabel_system_translations {

	/**
	 * @var tx_snowbabel_configuration
	 */
	private static $confObj;

	/**
	 * @var
	 */
	private static $CopyDefaultLanguage;

	/**
	 * @var
	 */
	private static $AvailableLanguages;

	/**
	 * @var
	 */
	private static $BlacklistedExtensions;

	/**
	 * @var
	 */
	private static $BlacklistedCategories;

	/**
	 * @var
	 */
	private static $WhitelistedActivated;

	/**
	 * @var
	 */
	private static $WhitelistedExtensions;

	/**
	 * @var
	 */
	private static $LocalExtensionPath;

	/**
	 * @var
	 */
	private static $SystemExtensionPath;

	/**
	 * @var
	 */
	private static $GlobalExtensionPath;

	/**
	 * @var
	 */
	private static $SitePath;

	/**
	 * @var
	 */
	private static $L10nPath;

	/**
	 * @var
	 */
	private static $LoadedExtensions;

	/**
	 * @var string
	 */
	private static $CacheTranslationsPath = '';

	/**
	 * @var string
	 */
	private static $CacheTranslationLanguage = '';

	/**
	 * @var
	 */
	private static $CachedTranslations;

	/**
	 * @var
	 */
	private static $CachedOriginalTranslations;

	/**
	 * @var
	 */
	private static $CacheFilePath = '';

	/**
	 * @var
	 */
	private static $CacheLanguageFile = array();

	/**
	 * @param $confObj
	 * @return void
	 */
	public static function init($confObj) {

		self::$confObj = $confObj;

			// get Application params
		self::$CopyDefaultLanguage = self::$confObj->getApplicationConfiguration('CopyDefaultLanguage');
		self::$AvailableLanguages = self::$confObj->getApplicationConfiguration('AvailableLanguages');
		self::$BlacklistedExtensions = self::$confObj->getApplicationConfiguration('BlacklistedExtensions');
		self::$BlacklistedCategories = explode(',', self::$confObj->getApplicationConfiguration('BlacklistedCategories'));
		self::$WhitelistedActivated = self::$confObj->getApplicationConfiguration('WhitelistedActivated');
		self::$WhitelistedExtensions = self::$confObj->getApplicationConfiguration('WhitelistedExtensions');
		self::$LocalExtensionPath = self::$confObj->getApplicationConfiguration('LocalExtensionPath');
		self::$SystemExtensionPath = self::$confObj->getApplicationConfiguration('SystemExtensionPath');
		self::$GlobalExtensionPath = self::$confObj->getApplicationConfiguration('GlobalExtensionPath');

			// get Extension params
		self::$SitePath = self::$confObj->getExtensionConfiguration('SitePath');
		self::$L10nPath = self::$confObj->getExtensionConfiguration('L10nPath');
		self::$LoadedExtensions = self::$confObj->getExtensionConfigurationLoadedExtensions();
	}

	/**
	 * @return array
	 */
	public static function getExtensions() {

		$Extensions = self::getDirectories();

		$Extensions = self::removeBlacklistedExtensions($Extensions);

		$Extensions = self::checkWhitelistedExtensions($Extensions);

		$Extensions = self::getExtensionData($Extensions);

		return $Extensions;

	}


	/**
	 * @return array
	 */
	public static function getDirectories() {

		$Directories	= array();
		$RawDirectories = array();

		// get local extension dirs
		$RawDirectories['Local'] = self::getSystemDirectories(self::$SitePath.self::$LocalExtensionPath);

		// get system extension dirs
		$RawDirectories['System'] = self::getSystemDirectories(self::$SitePath.self::$SystemExtensionPath);

		// get global extension dirs
		$RawDirectories['Global'] = self::getSystemDirectories(self::$SitePath.self::$GlobalExtensionPath);


		if(is_array($RawDirectories['System']) && count($RawDirectories['System']) > 0) {
			$Directories = array_merge($Directories, $RawDirectories['System']);
		}

		if(is_array($RawDirectories['Global']) && count($RawDirectories['Global']) > 0) {
			$Directories = array_merge($Directories, $RawDirectories['Global']);
		}

		if(is_array($RawDirectories['Local']) && count($RawDirectories['Local']) > 0) {
			$Directories = array_merge($Directories, $RawDirectories['Local']);
		}

			// Removes Double Entries
		$Directories = array_unique($Directories);

		return $Directories;
	}

	/**
	 * @static
	 * @param $Extensions
	 * @param $Typo3Version
	 * @return array
	 */
	public static function getFiles($Extensions, $Typo3Version) {

		$Files = array();

	    if(count($Extensions) > 0) {
			foreach($Extensions as $Extension) {

					// Get Extension Files
				$Files[$Extension['uid']] = self::getSystemFiles($Extension['ExtensionPath'], $Extension['uid'], $Typo3Version);

			}
	    }


		return $Files;
	}

	/**
	 * @static
	 * @param $Files
	 * @param $Typo3Version
	 * @return array
	 */
	public static function getLabels($Files, $Typo3Version) {

		$Labels = array();

		if(count($Files)) {

			foreach($Files as $File) {

					// Get Fileinfos
				$FileInfo = self::getFileInfos($File['ExtensionPath'] . $File['FileKey']);

					// XLIFF (Typo3 4.6 & Above)
				if ($Typo3Version >= 4006000 && $FileInfo['Extension'] == 'xlf') {
					$Labels[$File['FileId']] = self::getSystemLabelsXliff($File['ExtensionPath'] . $File['FileKey'], $File['FileId']);
				}
					// XML
				else {
					$Labels[$File['FileId']] = self::getSystemLabelsXml($File['ExtensionPath'] . $File['FileKey'], $File['FileId']);

				}

			}

		}

		return $Labels;

	}

	/**
	 * @static
	 * @param $Labels
	 * @param $Typo3Version
	 * @return array
	 */
	public static function getTranslations($Labels, $Typo3Version) {

		$Translations = array();

		if(count($Labels)) {

			foreach($Labels as $Label) {

				$Translations[$Label['LabelId']] = self::getSystemTranslations($Label['LabelName'], $Label['ExtensionPath'] . $Label['FileKey'], $Label['LabelId'], $Label['ExtensionKey'], $Typo3Version);

//				// TODO: remove after dev
//				if($Label['LabelId'] == 431) {
//					t3lib_div::debug($Translations[$Label['LabelId']], 'Translations');
//					t3lib_div::debug(self::$CacheLanguageFile, 'CachedLanguageFile');
//				}

			}

		}

		return $Translations;

	}

	/**
	 * @static
	 * @param $Translation
	 * @return void
	 */
	public static function updateTranslation($Translation) {

		$FilePath			= $Translation['ExtensionPath'] . $Translation['FileKey'];
		$LanguageKey		= $Translation['TranslationLanguage'];
		$ExtensionKey		= $Translation['ExtensionKey'];
		$LabelName			= $Translation['LabelName'];
		$TranslationValue	= $Translation['TranslationValue'];

		$FileInfo = self::getFileInfos($FilePath);
		$Typo3Version = self::$confObj->getTypo3Version();

			// Get l10n Location
		$TranslationFileName = t3lib_div::llXmlAutoFileName($FilePath, $LanguageKey);
		$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

			// XLIFF (Typo3 4.6 & Above)
		if ($Typo3Version >= 4006000 && $FileInfo['Extension'] == 'xlf') {
			self::updateTranslationXlf($TranslationValue, $LabelName, $LanguageKey, $TranslationFilePath, $ExtensionKey);
		}
		else {
			self::updateTranslationXml($TranslationValue, $LabelName, $LanguageKey, $FilePath, $TranslationFilePath);
		}

			// Delete Temp Files In typo3temp-Folder
		self::deleteSystemCache($FilePath, $LanguageKey);

	}

	/**
	 * @static
	 * @param $TranslationValue
	 * @param $LabelName
	 * @param $LanguageKey
	 * @param $TranslationFilePath
	 * @param $ExtensionKey
	 * @return void
	 */
	private static function updateTranslationXlf($TranslationValue, $LabelName, $LanguageKey, $TranslationFilePath, $ExtensionKey) {

			// Get Data From L10n File
		$Translation[$LanguageKey] = self::getSystemLanguageFileXliff($TranslationFilePath, $LanguageKey);

			// Change Value If Not Empty
		if(strlen($TranslationValue)) {
			$Translation[$LanguageKey][$LabelName][0]['target'] = $TranslationValue;
		}
			// Otherwise Unset Value
		else {
			if($Translation[$LanguageKey][$LabelName]){
				unset($Translation[$LanguageKey][$LabelName]);
			}

		}

			// Write File
		self::writeTranslationXliff($Translation, $TranslationFilePath, $LanguageKey, $ExtensionKey);

	}

	/**
	 * @static
	 * @param $TranslationValue
	 * @param $LabelName
	 * @param $LanguageKey
	 * @param $FilePath
	 * @param $TranslationFilePath
	 * @return void
	 */
	private static function updateTranslationXml($TranslationValue, $LabelName, $LanguageKey, $FilePath, $TranslationFilePath) {

			// Get Data From L10n File
		$Translation = self::getSystemLanguageFileXml($TranslationFilePath);

			// Get Original Label
		$Extension = self::getSystemLanguageFileXml($FilePath);

		if($Extension) {
				// Get Hash From Original Label
			$OriginalHash = t3lib_div::md5int($Extension['data']['default'][$LabelName]);
				// Set Hash To Translation File
			$Translation['orig_hash'][$LanguageKey][$LabelName] = $OriginalHash;
		}

			// Change Value If Not Empty
		if(strlen($TranslationValue)) {
			$Translation['data'][$LanguageKey][$LabelName] = $TranslationValue;
		}
			// Otherwise Unset Value
		else {
			if($Translation['data'][$LanguageKey][$LabelName]){
				unset($Translation['data'][$LanguageKey][$LabelName]);
			}

		}

			// Write File
		self::writeTranslationXml($Translation, $TranslationFilePath);

	}

	/**
	 * @static
	 * @param $RawExtensions
	 * @return array
	 */
	private static function removeBlacklistedExtensions($RawExtensions) {

		$Extensions = array();

		if(!self::$WhitelistedActivated) {
			$BlacklistedExtensions = array();

				// Get Blacklisted Extensions
			if (self::$BlacklistedExtensions) {
				 $BlacklistedExtensions = explode(',',self::$BlacklistedExtensions);
			}

				// Just Use Allowed Extensions
			if(count($RawExtensions)) {
				foreach($RawExtensions as $Extension) {

					if(!in_array($Extension, $BlacklistedExtensions)) {
						array_push($Extensions, $Extension);
					}

				}
			}
		}
		else {
			if(count($RawExtensions)) {
				foreach($RawExtensions as $Extension) {
					array_push($Extensions, $Extension);
				}
			}
		}

		return $Extensions;

	}

	/**
	 * @static
	 * @param $Extensions
	 * @return array
	 */
	private static function checkWhitelistedExtensions($Extensions) {

		if(self::$WhitelistedActivated) {

			if(count($Extensions) > 0) {

				$ExtensionsNew = array();

				foreach($Extensions as $Extension) {

						// Check If Extension Is Available
					if(in_array($Extension, self::$WhitelistedExtensions)) {
						array_push($ExtensionsNew, $Extension);
					}

				}

					// Set New Extensionlist
				$Extensions = $ExtensionsNew;
			}

		}

		return $Extensions;

	}

	/**
	 * @static
	 * @param $ExtensionList
	 * @return array
	 */
	private static function getExtensionData($ExtensionList) {

		$Extensions = array();

			// Get Data For Every Extension
		if(is_array($ExtensionList)) {
			foreach($ExtensionList as $ExtensionKey) {

				$ExtensionData = self::getExtension($ExtensionKey);

					// Just Add If Data Available
				if($ExtensionData) {
					array_push($Extensions, $ExtensionData);
				}

			}
		}

		return $Extensions;

	}

	/**
	 * @param  $ExtensionKey
	 * @return array|bool
	 */
	private static function getExtension($ExtensionKey) {

		if(is_string($ExtensionKey)) {

				// Locate Where Extension Is Installed
			$ExtensionLocation = self::getSystemExtensionLocation($ExtensionKey);

				// Get Extension Data From EmConf
			$EMConf = self::getSystemEMConf($ExtensionLocation['Path']);

				// If Blacklisted Category Return
			if(self::isCategoryBlacklisted($EMConf['ExtensionCategory'])) return false;

				// Add Extension Data
			$ExtensionData = array(
				'ExtensionKey' 					=> $ExtensionKey,
				'ExtensionTitle'				=> $EMConf['ExtensionTitle'] ? self::getCleanedString($EMConf['ExtensionTitle']) : $ExtensionKey,
				'ExtensionDescription'			=> self::getCleanedString($EMConf['ExtensionDescription']),
				'ExtensionCategory'				=> self::getCleanedString($EMConf['ExtensionCategory']),
				'ExtensionIcon'					=> self::getExtensionIcon($ExtensionLocation, $ExtensionKey),
				'ExtensionLocation'				=> $ExtensionLocation['Location'],
				'ExtensionPath'					=> $ExtensionLocation['Path'],
				'ExtensionLoaded'				=> self::isExtensionLoaded($ExtensionKey)
			);

			return $ExtensionData;

		}

		return false;

	}

	/**
	 * @param  $ExtensionCategory
	 * @return bool
	 */
	private static function isCategoryBlacklisted($ExtensionCategory) {

			// Just Use Allowed Categories
		if($ExtensionCategory && is_array(self::$BlacklistedCategories) && !self::$WhitelistedActivated) {

			if(in_array($ExtensionCategory, self::$BlacklistedCategories)) {
				return true;
			}

		}

		return false;

	}

	/**
	 * @param  $ExtensionKey
	 * @return bool
	 */
	private static function getSystemExtensionLocation($ExtensionKey) {

		$ExtensionPath = false;

		// ORDER'S IMPORTANT!

			// Check System Extension
		$TempExtensionPath = self::$SitePath.self::$SystemExtensionPath.$ExtensionKey.'/';
		if(is_dir($TempExtensionPath)) {
			$ExtensionPath['Path'] = 		$TempExtensionPath;
			$ExtensionPath['Location'] =	'System';
		}

			// Check Global Extension
		$TempExtensionPath = self::$SitePath.self::$GlobalExtensionPath.$ExtensionKey.'/';
		if(is_dir($TempExtensionPath)) {
			$ExtensionPath['Path'] = 		$TempExtensionPath;
			$ExtensionPath['Location'] =	'Global';
		}


			// Check Local Extension
		$TempExtensionPath = self::$SitePath.self::$LocalExtensionPath.$ExtensionKey.'/';
		if(is_dir($TempExtensionPath)) {
			$ExtensionPath['Path'] = 		$TempExtensionPath;
			$ExtensionPath['Location'] =	'Local';
		}


		return $ExtensionPath;
	}

	/**
	 * @param  $ExtensionPath
	 * @return bool
	 */
	private static function getSystemEMConf($ExtensionPath) {

		if($ExtensionPath) {

				// Set EMConf Path
			$EMConfPath = $ExtensionPath . 'ext_emconf.php';

			if(file_exists($EMConfPath)) {

					// Include EMConf
				$EM_CONF = NULL;
				include ($EMConfPath);

					// Add Needed EMConf Params To Array
				$EMConf['ExtensionCategory'] =		$EM_CONF['']['category'];
				$EMConf['ExtensionTitle'] =			$EM_CONF['']['title'];
				$EMConf['ExtensionDescription'] =	$EM_CONF['']['description'];

				return $EMConf;
			}

		}

		return false;

	}

	/**
	 * @param  $Path
	 * @return array|null
	 */
	private static function getSystemDirectories($Path) {

		if(isset($Path)) {

			$Directories = t3lib_div::get_dirs($Path);

			if(is_array($Directories)) {
					return $Directories;
			}

		}

		return NULL;

	}

	/**
	 * @static
	 * @param $FilePath
	 * @param $FileId
	 * @return array
	 */
	private static function getSystemLabelsXliff($FilePath, $FileId) {

		$Labels = array();

			// Get LanguageFile
		$LanguageFile = t3lib_div::readLLfile($FilePath, 'default');

			// Language File Available?
		if($LanguageFile) {

				// Set System Labels
			$LabelData = $LanguageFile['default'];

			if(is_array($LabelData)) {
				foreach($LabelData as $LabelName => $LabelDefault) {

					$Labels[] = array(
						'FileId'		=> $FileId,
						'LabelName' 	=> $LabelName,
						'LabelDefault'	=> $LabelDefault[0]['source']
					);

				}
			}

		}

		return $Labels;

	}

	/**
	 * @static
	 * @param $FilePath
	 * @param $FileId
	 * @return array
	 */
	private static function getSystemLabelsXml($FilePath, $FileId) {

		$Labels = array();

			// Get Language File
		$LanguageFile = self::getSystemLanguageFileXml($FilePath);

			// Language File Available?
		if($LanguageFile) {

				// Set System Labels
			$LabelData = $LanguageFile['data']['default'];

			if(is_array($LabelData)) {
				foreach($LabelData as $LabelName => $LabelDefault) {

					$Labels[] = array(
						'FileId'		=> $FileId,
						'LabelName' 	=> $LabelName,
						'LabelDefault'	=> $LabelDefault
					);

				}

			}

		}

		return $Labels;
	}

	/**
	 * @static
	 * @param $LabelName
	 * @param $FilePath
	 * @param $LabelId
	 * @param $ExtensionKey
	 * @param $Typo3Version
	 * @return array
	 */
	private static function getSystemTranslations($LabelName, $FilePath, $LabelId, $ExtensionKey, $Typo3Version) {

		$Translations = array();

			// Get Fileinfos
		$FileInfo = self::getFileInfos($FilePath);

			// Load Language File If Not Cached
		if($FilePath != self::$CacheFilePath || !self::$CacheLanguageFile) {

				// Set FilePath In Cache
			self::$CacheFilePath = $FilePath;

				// XLIFF (Typo3 4.6 & Above)
			if ($Typo3Version >= 4006000 && $FileInfo['Extension'] == 'xlf') {
				self::$CacheLanguageFile = self::getSystemLanguageFileXliff($FilePath);
			}
				// XML
			else {
				self::$CacheLanguageFile = self::getSystemLanguageFileXml($FilePath);
			}

		}

		if(self::$CacheLanguageFile){

				// Checks Translations To Show
			if(is_array(self::$AvailableLanguages) && count(self::$AvailableLanguages) > 0) {

					// Loop Languages
				foreach(self::$AvailableLanguages as $Language) {

						// XLIFF (Typo3 4.6 & Above)
					if ($Typo3Version >= 4006000 && $FileInfo['Extension'] == 'xlf') {
						$Translation = self::getSystemTranslationXliff($FilePath, $Language['LanguageKey'], $LabelName, $ExtensionKey);
					}
						// XML
					else {
						$Translation = self::getSystemTranslationXml($FilePath, $Language['LanguageKey'], $LabelName);
					}

						// Add Translation
					$Translations[] = array(
						'LabelId'		=> $LabelId,
						'TranslationLanguage'	=> $Language['LanguageKey'],
						'TranslationValue'		=> $Translation,
						'TranslationEmpty'		=> $Translation ? 0 : 1
					);

				}
			}

		}

		return $Translations;

	}

	/**
	 * @static
	 * @param $FilePath
	 * @param $LanguageKey
	 * @param $LabelName
	 * @param $ExtensionKey
	 * @return string
	 */
	private static function getSystemTranslationXliff($FilePath, $LanguageKey, $LabelName, $ExtensionKey) {

	        // While First Loop Get Translation From l10n (And Create File If Not Done Yet)
	    if($FilePath != self::$CacheTranslationsPath || $LanguageKey != self::$CacheTranslationLanguage) {

			self::$CachedTranslations = array();

				// Get Fileinfo
			$FileInfo = self::getFileInfos($FilePath);

				// Path To Translation In Extension
			$OriginalTranslationPath = $FileInfo['Dirname'] . $LanguageKey . '.' . $FileInfo['Basename'];

				// Get Path To l10n Location
			$TranslationFileName = t3lib_div::llXmlAutoFileName($FilePath, $LanguageKey);
			$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

		        // Check If L10n File Available Otherwise Create One
			self::isSystemTranslationAvailableXliff($LanguageKey, $TranslationFilePath, $ExtensionKey);

		        // Get Data From L10n File
		    self::$CachedTranslations[$LanguageKey] = self::getSystemLanguageFileXliff($TranslationFilePath, $LanguageKey);

				// Get Data From Original Translation
			self::$CachedOriginalTranslations[$LanguageKey] = self::getSystemLanguageFileXliff($OriginalTranslationPath, $LanguageKey);

				// Sync Data From L10n With Extension XML
			self::syncSystemTranslationXliff($LanguageKey, $TranslationFilePath, $ExtensionKey);

				// Set New Cached Path
			self::$CacheTranslationsPath = $FilePath;

				// Set New Cached Language
			self::$CacheTranslationLanguage = $LanguageKey;

	    }

	        // Return Translation If Available
		if(self::$CachedTranslations[$LanguageKey][$LabelName]) {
			return self::$CachedTranslations[$LanguageKey][$LabelName][0]['target'];
		}

			 // We Always Need A Translation In DB
	    return '';

	}

	/**
	 * @static
	 * @param $FilePath
	 * @param $LanguageKey
	 * @param $LabelName
	 * @return string
	 */
    private static function getSystemTranslationXml($FilePath, $LanguageKey, $LabelName) {

	        // While First Loop Get Translation From l10n (And Create File If Not Done Yet)
		if($FilePath != self::$CacheTranslationsPath || $LanguageKey != self::$CacheTranslationLanguage) {

				// Get l10n Location
			$TranslationFileName = t3lib_div::llXmlAutoFileName($FilePath, $LanguageKey);
			$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

		        // Check If L10n File Available Otherwise Create One
			self::isSystemTranslationAvailableXml($LanguageKey, $TranslationFilePath);

		        // Get Data From L10n File
		    self::$CachedTranslations[$LanguageKey] = self::getSystemLanguageFileXml($TranslationFilePath);

				// Set New Cached Path
			self::$CacheTranslationsPath = $FilePath;

				// Set New Cached Language
			self::$CacheTranslationLanguage = $LanguageKey;

				// Sync Data From L10n With Extension XML
			self::syncSystemTranslationXml(
				$LanguageKey,
				$TranslationFilePath
			);

	    }

	        // Return Translation If Available
		if(self::$CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName]) {
			return self::$CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName];
		}

			 // We Always Need A Translation In DB
	    return '';
    }

	/**
	 * @static
	 * @param $LanguageKey
	 * @param $TranslationFilePath
	 * @param $ExtensionKey
	 * @return void
	 */
	private static function isSystemTranslationAvailableXliff($LanguageKey, $TranslationFilePath, $ExtensionKey) {

			// Create L10n File & Folder
		if ($TranslationFilePath && !@is_file($TranslationFilePath))	{

				// Set Directory
			$DeepDir = dirname(substr($TranslationFilePath,strlen(self::$SitePath))).'/';

				// Create XLS & Directory
			if (t3lib_div::isFirstPartOfStr($DeepDir, self::$L10nPath . $LanguageKey . '/'))	{
				t3lib_div::mkdir_deep(self::$SitePath, $DeepDir);

				self::writeTranslationXliff(array(), $TranslationFilePath, $LanguageKey, $ExtensionKey);
			}

		}

	}

	/**
	 * @param  $LanguageKey
	 * @param  $TranslationFilePath
	 * @return void
	 */
	private static function isSystemTranslationAvailableXml($LanguageKey, $TranslationFilePath) {

			// Create L10n File
		if ($TranslationFilePath && !@is_file($TranslationFilePath))	{

				// Copy XML Data From Extension To L10n
			if($LanguageKey == 'en' && self::$CopyDefaultLanguage) {
					// Copy Default Labels To English
				$File['data'][$LanguageKey] = self::$CacheLanguageFile['data']['default'];
			}
			else {
				$File['data'][$LanguageKey] = self::$CacheLanguageFile['data'][$LanguageKey];
			}

				// Set Directory
			$DeepDir = dirname(substr($TranslationFilePath,strlen(self::$SitePath))).'/';

				// Create XML & Directory
			if (t3lib_div::isFirstPartOfStr($DeepDir, self::$L10nPath . $LanguageKey . '/'))	{

				t3lib_div::mkdir_deep(self::$SitePath, $DeepDir);
				self::writeTranslationXml($File, $TranslationFilePath);

			}

		}
	}

	/**
	 * @static
	 * @param $LanguageKey
	 * @param $TranslationFilePath
	 * @param $ExtensionKey
	 * @return void
	 */
	private static function syncSystemTranslationXliff($LanguageKey, $TranslationFilePath, $ExtensionKey) {

		if(is_array(self::$CacheLanguageFile) && count(self::$CacheLanguageFile) > 0) {
			foreach(self::$CacheLanguageFile as $LabelName => $LabelDefault) {

					// Set Source
				self::$CachedTranslations[$LanguageKey][$LabelName][0]['source'] = $LabelDefault[0]['source'];

					// Set 'l10n' Label If Available
				$L10nLabel = self::$CachedTranslations[$LanguageKey][$LabelName][0]['target'];

					// No Sync Needed If 'l10n' Already Defined
					// Otherwise Check If Labels Are Available Somewhere
				if(empty($L10nLabel)) {

						// Copy 'default' To 'en' If Activated In Settings
					if($LanguageKey === 'en' && self::$CopyDefaultLanguage) {
						self::$CachedTranslations[$LanguageKey][$LabelName][0]['target'] = $LabelDefault[0]['target'];
					}


						// Sync With Translation In Extension Dir
					if(self::$CachedOriginalTranslations[$LanguageKey]) {

							// Label From Original Translation
						$OriginalTranslationLabel = self::$CachedOriginalTranslations[$LanguageKey][$LabelName][0]['target'];

							// Set Original Translation If Available
						if(!empty($OriginalTranslationLabel)) {
							self::$CachedTranslations[$LanguageKey][$LabelName][0]['target'] = $OriginalTranslationLabel;
						}

					}

				}

					// Unset If No Data Available
				if(empty(self::$CachedTranslations[$LanguageKey][$LabelName][0]['target'])) {
					unset(self::$CachedTranslations[$LanguageKey][$LabelName]);
				}

			}


				// Write 'l10n' File
			self::writeTranslationXliff(self::$CachedTranslations, $TranslationFilePath, $LanguageKey, $ExtensionKey);

		}

	}

	/**
	 * @static
	 * @param $LanguageKey
	 * @param $TranslationFilePath
	 * @return void
	 */
	private static function syncSystemTranslationXml($LanguageKey, $TranslationFilePath) {

		$Changes		= 0;
		$LabelsDefault	= self::$CacheLanguageFile['data']['default'];

		if(is_array($LabelsDefault)) {
			foreach($LabelsDefault as $LabelName => $LabelDefault) {

					// Label From L10n
				$LabelL10n = self::$CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName];


					// Sync EN With Default If Activated
				if($LanguageKey == 'en' && self::$CopyDefaultLanguage) {
					// Do Nothing
				}
				else {
					$LabelDefault = self::$CacheLanguageFile['data'][$LanguageKey][$LabelName];
				}

					// Compare Default Label With Label From L10n
				if(!empty($LabelDefault) && empty($LabelL10n)) {
					self::$CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName] = $LabelDefault;
					++$Changes;
				}

			}

				// If There Are Changes Write It To XML File
			if($Changes > 0) {
				self::writeTranslationXml(self::$CachedTranslations[$LanguageKey], $TranslationFilePath);
			}

		}

	}

	/**
	 * @static
	 * @param $File
	 * @param $Path
	 * @param $LanguageKey
	 * @param $ExtensionKey
	 * @return void
	 */
	private static function writeTranslationXliff($File, $Path, $LanguageKey, $ExtensionKey) {

		$XmlFile = array();

		$XmlFile[] = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>';
		$XmlFile[] = '<xliff version="1.0">';
		$XmlFile[] = '	<file source-language="en"' . ($LanguageKey !== 'default' ? ' target-language="' . $LanguageKey . '"' : '')
				. ' datatype="plaintext" original="messages" date="' . gmdate('Y-m-d\TH:i:s\Z') . '"'
				. ' product-name="' . $ExtensionKey . '">';
		$XmlFile[] = '		<header/>';
    	$XmlFile[] = '		<body>';

		if(is_array($File[$LanguageKey]) && count($File[$LanguageKey]) > 0) {
			foreach ($File[$LanguageKey] as $Key => $Data) {

				$Source = $Data[0]['source'];
				$Target = $Data[0]['target'];

				if ($LanguageKey === 'default') {
					$XmlFile[] = '			<trans-unit id="' . $Key . '">';
					$XmlFile[] = '				<source>' . htmlspecialchars($Source) . '</source>';
					$XmlFile[] = '			</trans-unit>';
				} else {
					$XmlFile[] = '			<trans-unit id="' . $Key . '" approved="yes">';
					$XmlFile[] = '				<source>' . htmlspecialchars($Source) . '</source>';
					$XmlFile[] = '				<target>' . htmlspecialchars($Target) . '</target>';
					$XmlFile[] = '			</trans-unit>';
				}
			}
		}

		$XmlFile[] = '		</body>';
		$XmlFile[] = '	</file>';
		$XmlFile[] = '</xliff>';

		t3lib_div::writeFile($Path, implode(LF, $XmlFile));
	}

	/**
	 * @static
	 * @param $File
	 * @param $Path
	 * @param bool $SaveToOriginal
	 * @return bool
	 */
	private static function writeTranslationXml($File, $Path, $SaveToOriginal=false) {

		$XmlOptions = array(
			'parentTagMap'=>array(
				'data'=>'languageKey',
				'orig_hash'=>'languageKey',
				'orig_text'=>'languageKey',
				'labelContext'=>'label',
				'languageKey'=>'label'
			)
    	);

		$XmlFile =	'<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'.chr(10);
		$XmlFile .=	t3lib_div::array2xml($File, '', 0, $SaveToOriginal ? 'T3locallang' : 'T3locallangExt', 0, $XmlOptions);

		t3lib_div::writeFile($Path, $XmlFile);
	}

	/**
	 * @static
	 * @param $ExtensionPath
	 * @param $ExtensionId
	 * @param $Typo3Version
	 * @return array
	 */
	private static function getSystemFiles($ExtensionPath, $ExtensionId, $Typo3Version) {

		$Files = array();

			// Typo3 4.6 & Above
		if ($Typo3Version >= 4006000) {

				// Get 'llxml' Files
			$XmlFiles = self::getSystemFilesInPath($ExtensionPath, 'xml');

				// Get 'xliff' Files
			$XliffFiles = self::getSystemFilesInPath($ExtensionPath, 'xlf');

				// Compare 'llxml' and 'xliff' Files
			$TempFiles = self::getComparedSystemFiles($XmlFiles, $XliffFiles);

		}
		else {

				// Get 'llxml' Files
			$TempFiles = self::getSystemFilesInPath($ExtensionPath, 'xml');

		}

			// Adds New Keys
		if(is_array($TempFiles)) {
			foreach($TempFiles as $Key => $File) {

					// Check Name Convention 'locallang'
				if(strstr($TempFiles[$Key], 'locallang') !== false) {
					$Files[] = array(
						'ExtensionId'	=> $ExtensionId,
						'FileKey'   	=> $TempFiles[$Key]
					);
				}

			}
		}

		return $Files;
	}

	/**
	 * @static
	 * @param $ExtensionPath
	 * @param string $FileExtension
	 * @return array
	 */
	private static function getSystemFilesInPath($ExtensionPath, $FileExtension = 'xml') {

			// Get Extension Files
		$TempFilesXml_1 = t3lib_div::getAllFilesAndFoldersInPath(
			array(),
			$ExtensionPath,
			$FileExtension,
			0,
			99,
			'\.svn'
		);

		$TempFilesXml_2 = t3lib_div::removePrefixPathFromList(
			$TempFilesXml_1,
			$ExtensionPath
		);

		return $TempFilesXml_2;

	}

	/**
	 * @static
	 * @param $xmlFiles
	 * @param $xliffFiles
	 * @return array
	 */
	private static function getComparedSystemFiles($xmlFiles, $xliffFiles) {

		$Files = array();
		$ComparedFiles = array();

			// Add All Xml's
		foreach($xmlFiles as $Key => $xmlFile) {

			$xmlFileInfo = self::getFileInfos($xmlFile);

			$ComparedFiles[$xmlFileInfo['Filename']] = $xmlFileInfo['Extension'];

		}

			// Add All Xliff's
		foreach($xliffFiles as $Key => $xliffFile) {

			$xliffFileInfo = self::getFileInfos($xliffFile);

			$ComparedFiles[$xliffFileInfo['Filename']] = $xliffFileInfo['Extension'];

		}

			// Prepare Array For Return
		foreach($ComparedFiles as $Filename => $Extension) {
			$Files[] = $Filename . '.' . $Extension;
		}

		return $Files;
	}

	/**
	 * @static
	 * @param $File
	 * @return array
	 */
	private static function getFileInfos($File) {

		$FileInfo = array();

			// Explode Array By Points -> FileExtension Should Be Last
		$FileArray = explode('.', $File);

			// Reverse Array
			// -> FileExtension Is Now First Element
			// -> FileBasename Is Now Second Part
		$FileArray = array_reverse($FileArray);

		$FileInfo['Extension']	= $FileArray[0];
		$FileInfo['Filename']	= $FileArray[1];

			// Add Basename
		$FileInfo['Basename']	= pathinfo($File, PATHINFO_BASENAME);

			// Add Dirname
		$FileInfo['Dirname']	= pathinfo($File, PATHINFO_DIRNAME) . '/';

		return $FileInfo;
	}

	/**
	 * @static
	 * @param $File
	 * @param string $LanguageKey
	 * @return array|bool
	 */
	private static function getSystemLanguageFileXliff($File, $LanguageKey = 'default') {

		if(is_file($File)) {

				// Load Xls Object
			$xml = simplexml_load_file($File, 'SimpleXMLElement', LIBXML_NOWARNING);

				// Format Xls Object
			return self::formatSimpleXmlObject_XLS($xml, $LanguageKey);

		}

		return false;

	}

	/**
	 * @static
	 * @param $File
	 * @return array|bool
	 */
	private static function getSystemLanguageFileXml($File) {

		if(is_file($File)) {

				// Load Xml Object
			$xml = simplexml_load_file($File, 'SimpleXMLElement', LIBXML_NOWARNING);

				// Format Xml Object
			return self::formatSimpleXmlObject_XML($xml);

		}

		return false;
	}

	/**
	 * Function 'doParsingFromRoot' from Class 't3lib_l10n_parser_Xliff'
	 *
	 * @static
	 * @param $simpleXmlObject
	 * @param $LanguageKey
	 * @return array
	 */
	private static function formatSimpleXmlObject_XLS($simpleXmlObject, $LanguageKey) {

		$parsedData = array();
		$bodyOfFileTag = $simpleXmlObject->file->body;

		foreach ($bodyOfFileTag->children() as $translationElement) {
			if ($translationElement->getName() === 'trans-unit' && !isset($translationElement['restype'])) {
					// If restype would be set, it could be metadata from Gettext to XLIFF conversion (and we don't need this data)

				if ($LanguageKey === 'default') {
						// Default language coming from an XLIFF template (no target element)
					$parsedData[(string)$translationElement['id']][0] = array(
						'source' => (string)$translationElement->source,
						'target' => (string)$translationElement->source,
					);
				} else {
						// @todo Support "approved" attribute
					$parsedData[(string)$translationElement['id']][0] = array(
						'source' => (string)$translationElement->source,
						'target' => (string)$translationElement->target,
					);
				}
			} elseif ($translationElement->getName() === 'group' && isset($translationElement['restype']) && (string)$translationElement['restype'] === 'x-gettext-plurals') {
					// This is a translation with plural forms
				$parsedTranslationElement = array();

				foreach ($translationElement->children() as $translationPluralForm) {
					if ($translationPluralForm->getName() === 'trans-unit') {
							// When using plural forms, ID looks like this: 1[0], 1[1] etc
						$formIndex = substr((string)$translationPluralForm['id'], strpos((string)$translationPluralForm['id'], '[') + 1, -1);

						if ($LanguageKey === 'default') {
								// Default language come from XLIFF template (no target element)
							$parsedTranslationElement[(int)$formIndex] = array(
								'source' => (string)$translationPluralForm->source,
								'target' => (string)$translationPluralForm->source,
							);
						} else {
								// @todo Support "approved" attribute
							$parsedTranslationElement[(int)$formIndex] = array(
								'source' => (string)$translationPluralForm->source,
								'target' => (string)$translationPluralForm->target,
							);
						}
					}
				}

				if (!empty($parsedTranslationElement)) {
					if (isset($translationElement['id'])) {
						$id = (string)$translationElement['id'];
					} else {
						$id = (string)($translationElement->{'trans-unit'}[0]['id']);
						$id = substr($id, 0, strpos($id, '['));
					}

					$parsedData[$id] = $parsedTranslationElement;
				}
			}
		}

		return $parsedData;

	}

	/**
	 * @static
	 * @param $simpleXmlObject
	 * @return array|bool
	 */
	private static function formatSimpleXmlObject_XML($simpleXmlObject) {

		if($simpleXmlObject) {

			$xmlArray = array();

				// Meta Array
			if(is_array($simpleXmlObject->meta) || is_object($simpleXmlObject->meta)) {

				$xmlArray['meta'] = array();

				foreach($simpleXmlObject->meta as $meta) {
					foreach($meta as $metaData) {

						$metaKey = $metaData->getName();
						$metaValue = trim($metaData[0]);

						if(!empty($metaKey) && is_string($metaKey)) $xmlArray['meta'][$metaKey] = (string) $metaValue;

					}
				}

					// Unset If Not Used
				if(empty($xmlArray['meta'])) unset($xmlArray['meta']);

			}


				// Data Array
			if(is_array($simpleXmlObject->data->languageKey) || is_object($simpleXmlObject->data->languageKey)) {

				$xmlArray['data'] = array();

				foreach($simpleXmlObject->data->languageKey as $language) {

						// LanguageKey
					$languageKey = self::getSimpleXmlObjectAttributesIndex($language->attributes());

					if(!empty($languageKey) && is_string($languageKey)) {
						if(is_array($language->label) || is_object($language->label)) {
							foreach($language->label as $label) {

									// LabelName
								$labelName = self::getSimpleXmlObjectAttributesIndex($label->attributes());

									// LabelValue
								if(!empty($labelName) && is_string($labelName)) $xmlArray['data'][$languageKey][$labelName] = (string) trim($label[0]);

							}
						}
					}
				}

					// Unset If Not Used
				if(empty($xmlArray['data'])) unset($xmlArray['data']);

			}

			return $xmlArray;

		}

		return false;

	}

	/**
	 * @static
	 * @param $attributesObject
	 * @return string
	 */
	private static function getSimpleXmlObjectAttributesIndex($attributesObject) {

			// Get Attributes
		if(is_array($attributesObject) || is_object($attributesObject)) {

			$attributes = array();

			foreach($attributesObject as $name => $value){
				$attributes[$name] = trim($value);
			}

				// Return Index
			if(!empty($attributes['index'])) return (string) $attributes['index'];
		}

		return '';
	}

	/**
	 * @param  $String
	 * @return string
	 */
    private static function getCleanedString($String) {

        if($String) {

            $String = htmlentities($String);

        }

        return $String;

    }

	/**
	 * @param  $ExtensionPath
	 * @param  $ExtensionKey
	 * @return string
	 */
	private static function getExtensionIcon($ExtensionPath, $ExtensionKey) {

		if($ExtensionPath && $ExtensionKey) {

			if(file_exists($ExtensionPath['Path'] . 'ext_icon.gif')) {

					// Check The Location And Get The CSS Path
				switch($ExtensionPath['Location']) {

					case 'Local':

						$ExtensionPath = self::$LocalExtensionPath;

						break;

					case 'Global':

						$ExtensionPath = self::$GlobalExtensionPath;

						break;

					case 'System':

						$ExtensionPath = self::$SystemExtensionPath;

						break;
				}

				$ExtensionIcon = '../../../../' . $ExtensionPath . $ExtensionKey . '/ext_icon.gif';

			}
                // Set Default Icon
            else {

                $ExtensionIcon = '../Resources/Public/Images/Miscellaneous/ext_icon.gif';

            }

            return $ExtensionIcon;

		}

		return '';

	}

	/**
	 * @param  $ExtensionKey
	 * @return bool
	 */
	private static function isExtensionLoaded($ExtensionKey) {

		if(isset($ExtensionKey)) {
			$InstalledExtensions = self::$LoadedExtensions;

			$Check = array_key_exists($ExtensionKey, $InstalledExtensions);

			if($Check) {
				return true;
			}
			else {
				return false;
			}
		}

		return false;

	}

	/**
	 * @param  $FilePath
	 * @param  $Language
	 * @return void
	 */
	private static function deleteSystemCache($FilePath, $Language) {

				// Delete Cached Language File
			$cacheFileName = self::getCacheFileName($FilePath, $Language);
			t3lib_div::unlink_tempfile($cacheFileName);

				// Delete 'default'
			if($Language != 'default') {
				$cacheFileNameDefault = self::getCacheFileName($FilePath);
				t3lib_div::unlink_tempfile($cacheFileNameDefault);
			}
	}

	/**
	 * @param  $FilePath
	 * @param string $Language
	 * @return string
	 */
	private static function getCacheFileName($FilePath, $Language='default') {

			$hashSource = substr($FilePath, strlen(PATH_site)) . '|' . date('d-m-Y H:i:s', filemtime($FilePath)) . '|version=2.3';
			$hash = '_' . t3lib_div::shortMD5($hashSource);
			$tempPath = PATH_site . 'typo3temp/llxml/';
			$fileExtension = substr(basename($FilePath), 10, 15);

			return $tempPath . $fileExtension . $hash . '.' . $Language . '.' . 'utf-8' . '.cache';
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/System/class.tx_snowbabel_system_translations.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/System/class.tx_snowbabel_system_translations.php']);
}

?>