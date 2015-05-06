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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Translations
 *
 * @package Snowflake\Snowbabel\Service
 */
class Translations {


	/**
	 * @var Configuration
	 */
	private $confObj;


	/**
	 * @var
	 */
	private $copyDefaultLanguage;


	/**
	 * @var
	 */
	private $availableLanguages;


	/**
	 * @var
	 */
	private $approvedExtensions;


	/**
	 * @var
	 */
	private $localExtensionPath;


	/**
	 * @var
	 */
	private $systemExtensionPath;


	/**
	 * @var
	 */
	private $globalExtensionPath;


	/**
	 * @var
	 */
	private $sitePath;


	/**
	 * @var
	 */
	private $l10nPath;


	/**
	 * @var
	 */
	private $loadedExtensions;


	/**
	 * @var string
	 */
	private $cacheTranslationsPath = '';


	/**
	 * @var string
	 */
	private $cacheTranslationLanguage = '';


	/**
	 * @var
	 */
	private $cachedTranslations;


	/**
	 * @var
	 */
	private $cachedOriginalTranslations;


	/**
	 * @var
	 */
	private $cacheFilePath = '';


	/**
	 * @var
	 */
	private $cacheLanguageFile = array ();


	/**
	 * @param $confObj
	 * @return void
	 */
	public function init($confObj) {

		$this->confObj = $confObj;

		// get Application params
		$this->copyDefaultLanguage = $this->confObj->getApplicationConfiguration('CopyDefaultLanguage');
		$this->availableLanguages = $this->confObj->getApplicationConfiguration('AvailableLanguages');
		$this->approvedExtensions = $this->confObj->getApplicationConfiguration('ApprovedExtensions');
		$this->localExtensionPath = $this->confObj->getApplicationConfiguration('LocalExtensionPath');
		$this->systemExtensionPath = $this->confObj->getApplicationConfiguration('SystemExtensionPath');
		$this->globalExtensionPath = $this->confObj->getApplicationConfiguration('GlobalExtensionPath');

		// get Extension params
		$this->sitePath = $this->confObj->getExtensionConfiguration('SitePath');
		$this->l10nPath = $this->confObj->getExtensionConfiguration('L10nPath');
		$this->loadedExtensions = $this->confObj->getExtensionConfigurationLoadedExtensions();
	}


	/**
	 * @return array
	 */
	public function getExtensions() {

		$extensions = self::getDirectories();

		$extensions = self::checkApprovedExtensions($extensions);

		$extensions = self::getExtensionData($extensions);

		return $extensions;

	}


	/**
	 * @return array
	 */
	public function getDirectories() {

		$directories = array ();
		$rawDirectories = array ();

		// get local extension dirs
		$rawDirectories['Local'] = self::getSystemDirectories($this->sitePath . $this->localExtensionPath);

		// get system extension dirs
		$rawDirectories['System'] = self::getSystemDirectories($this->sitePath . $this->systemExtensionPath);

		// get global extension dirs
		$rawDirectories['Global'] = self::getSystemDirectories($this->sitePath . $this->globalExtensionPath);


		if (is_array($rawDirectories['System']) && count($rawDirectories['System']) > 0) {
			$directories = array_merge($directories, $rawDirectories['System']);
		}

		if (is_array($rawDirectories['Global']) && count($rawDirectories['Global']) > 0) {
			$directories = array_merge($directories, $rawDirectories['Global']);
		}

		if (is_array($rawDirectories['Local']) && count($rawDirectories['Local']) > 0) {
			$directories = array_merge($directories, $rawDirectories['Local']);
		}

		// Removes Double Entries
		$directories = array_unique($directories);

		return $directories;
	}


	/**
	 * @param $extensions
	 * @return array
	 */
	public function getFiles($extensions) {

		$files = array ();

		if (count($extensions) > 0) {
			foreach ($extensions as $extension) {

				// Get Extension Files
				$files[$extension['uid']] = self::getSystemFiles($extension['ExtensionPath'], $extension['uid']);

			}
		}

		return $files;
	}


	/**
	 * @param $files
	 * @return array
	 */
	public function getLabels($files) {

		$labels = array ();

		if (count($files)) {

			foreach ($files as $file) {

				// Get Fileinfos
				$fileInfo = self::getFileInfos($file['ExtensionPath'] . $file['FileKey']);

				// XLIFF
				if ($fileInfo['Extension'] == 'xlf') {
					$labels[$file['FileId']] = self::getSystemLabelsXliff($file['ExtensionPath'] . $file['FileKey'], $file['FileId']);
				} // XML
				else {
					$labels[$file['FileId']] = self::getSystemLabelsXml($file['ExtensionPath'] . $file['FileKey'], $file['FileId']);

				}

			}

		}

		return $labels;

	}


	/**
	 * @param $labels
	 * @return array
	 */
	public function getTranslations($labels) {

		$translations = array ();

		if (count($labels)) {
			foreach ($labels as $label) {
				$translations[$label['LabelId']] = self::getSystemTranslations(
					$label['LabelName'],
					$label['ExtensionPath'] . $label['FileKey'],
					$label['LabelId'],
					$label['ExtensionKey']
				);
			}
		}

		return $translations;

	}


	/**
	 * @param $translation
	 * @return void
	 */
	public function updateTranslation($translation) {

		$filePath = $translation['ExtensionPath'] . $translation['FileKey'];
		$languageKey = $translation['TranslationLanguage'];
		$extensionKey = $translation['ExtensionKey'];
		$labelName = $translation['LabelName'];
		$translationValue = $translation['TranslationValue'];

		// Get l10n Location
		$translationFileName = GeneralUtility::llXmlAutoFileName($filePath, $languageKey);
		$translationFilePath = GeneralUtility::getFileAbsFileName($translationFileName);

		// Update XLIFF
		self::updateTranslationXlf($translationValue, $labelName, $languageKey, $translationFilePath, $extensionKey);

		// Delete Temp Files In typo3temp-Folder
		self::deleteSystemCache($filePath, $languageKey);

	}


	/**
	 * @param $translationValue
	 * @param $labelName
	 * @param $languageKey
	 * @param $translationFilePath
	 * @param $extensionKey
	 * @return void
	 */
	private function updateTranslationXlf($translationValue, $labelName, $languageKey, $translationFilePath, $extensionKey) {

		// Get Data From L10n File
		$translation[$languageKey] = self::getSystemLanguageFileXliff($translationFilePath, $languageKey);

		// Change Value If Not Empty
		if (strlen($translationValue)) {
			$translation[$languageKey][$labelName][0]['target'] = $translationValue;
		} // Otherwise Unset Value
		else {
			if ($translation[$languageKey][$labelName]) {
				unset($translation[$languageKey][$labelName]);
			}

		}

		// Write File
		self::writeTranslationXliff($translation, $translationFilePath, $languageKey, $extensionKey);

	}


	/**
	 * @param $extensions
	 * @return array
	 *
	 * todo: renaming
	 */
	private function checkApprovedExtensions($extensions) {


		if (count($extensions) > 0) {

			$extensionsNew = array ();

			foreach ($extensions as $extension) {

				// Check If Extension Is Available
				if (in_array($extension, $this->approvedExtensions)) {
					array_push($extensionsNew, $extension);
				}

			}

			// Set New Extensionlist
			$extensions = $extensionsNew;
		}


		return $extensions;

	}


	/**
	 * @param $extensionList
	 * @return array
	 */
	private function getExtensionData($extensionList) {

		$extensions = array ();

		// Get Data For Every Extension
		if (is_array($extensionList)) {
			foreach ($extensionList as $extensionKey) {

				$extensionData = self::getExtension($extensionKey);

				// Just Add If Data Available
				if ($extensionData) {
					array_push($extensions, $extensionData);
				}

			}
		}

		return $extensions;

	}


	/**
	 * @param  $extensionKey
	 * @return array|bool
	 */
	private function getExtension($extensionKey) {

		if (is_string($extensionKey)) {

			// Locate Where Extension Is Installed
			$extensionLocation = self::getSystemExtensionLocation($extensionKey);

			// Get Extension Data From EmConf
			$emConf = self::getSystemEMConf($extensionLocation['Path']);

			// Add Extension Data
			$extensionData = array (
				'ExtensionKey' => $extensionKey,
				'ExtensionTitle' => $emConf['ExtensionTitle'] ? self::getCleanedString($emConf['ExtensionTitle']) : $extensionKey,
				'ExtensionDescription' => self::getCleanedString($emConf['ExtensionDescription']),
				'ExtensionCategory' => self::getCleanedString($emConf['ExtensionCategory']),
				'ExtensionIcon' => self::getExtensionIcon($extensionLocation, $extensionKey),
				'ExtensionLocation' => $extensionLocation['Location'],
				'ExtensionPath' => $extensionLocation['Path'],
				'ExtensionLoaded' => self::isExtensionLoaded($extensionKey)
			);

			return $extensionData;

		}

		return FALSE;

	}


	/**
	 * @param  $extensionKey
	 * @return bool
	 */
	private function getSystemExtensionLocation($extensionKey) {

		$extensionPath = FALSE;

		// ORDER'S IMPORTANT!

		// Check System Extension
		$tempExtensionPath = $this->sitePath . $this->systemExtensionPath . $extensionKey . '/';
		if (is_dir($tempExtensionPath)) {
			$extensionPath['Path'] = $tempExtensionPath;
			$extensionPath['Location'] = 'System';
		}

		// Check Global Extension
		$tempExtensionPath = $this->sitePath . $this->globalExtensionPath . $extensionKey . '/';
		if (is_dir($tempExtensionPath)) {
			$extensionPath['Path'] = $tempExtensionPath;
			$extensionPath['Location'] = 'Global';
		}


		// Check Local Extension
		$tempExtensionPath = $this->sitePath . $this->localExtensionPath . $extensionKey . '/';
		if (is_dir($tempExtensionPath)) {
			$extensionPath['Path'] = $tempExtensionPath;
			$extensionPath['Location'] = 'Local';
		}


		return $extensionPath;
	}


	/**
	 * todo: check for better solution
	 *
	 * @param  $extensionPath
	 * @return bool
	 */
	private function getSystemEMConf($extensionPath) {

		if ($extensionPath) {

			// Set EMConf Path
			$emConfPath = $extensionPath . 'ext_emconf.php';

			if (file_exists($emConfPath)) {

				// Include EMConf
				$EM_CONF = NULL;
				require_once($emConfPath);

				// Add Needed EMConf Params To Array
				$EMConf['ExtensionCategory'] = $EM_CONF['']['category'];
				$EMConf['ExtensionTitle'] = $EM_CONF['']['title'];
				$EMConf['ExtensionDescription'] = $EM_CONF['']['description'];

				return $EMConf;
			}

		}

		return FALSE;

	}


	/**
	 * @param  $path
	 * @return array|null
	 */
	private function getSystemDirectories($path) {

		if (isset($path)) {

			$directories = GeneralUtility::get_dirs($path);

			if (is_array($directories)) {
				return $directories;
			}

		}

		return NULL;

	}


	/**
	 * @param $filePath
	 * @param $fileId
	 * @return array
	 */
	private function getSystemLabelsXliff($filePath, $fileId) {

		$labels = array ();

		// Get LanguageFile
		$languageFile = GeneralUtility::readLLfile($filePath, 'default');

		// Language File Available?
		if ($languageFile) {

			// Set System Labels
			$labelData = $languageFile['default'];

			if (is_array($labelData)) {
				foreach ($labelData as $labelName => $labelDefault) {

					$labels[] = array (
						'FileId' => $fileId,
						'LabelName' => $labelName,
						'LabelDefault' => $labelDefault[0]['source']
					);

				}
			}

		}

		return $labels;

	}


	/**
	 * @param $filePath
	 * @param $fileId
	 * @return array
	 */
	private function getSystemLabelsXml($filePath, $fileId) {

		$labels = array ();

		// Get Language File
		$languageFile = self::getSystemLanguageFileXml($filePath);

		// Language File Available?
		if ($languageFile) {

			// Set System Labels
			$labelData = $languageFile['data']['default'];

			if (is_array($labelData)) {
				foreach ($labelData as $labelName => $labelDefault) {

					$labels[] = array (
						'FileId' => $fileId,
						'LabelName' => $labelName,
						'LabelDefault' => $labelDefault
					);

				}

			}

		}

		return $labels;
	}


	/**
	 * @param $labelName
	 * @param $filePath
	 * @param $labelId
	 * @param $extensionKey
	 * @return array
	 */
	private function getSystemTranslations($labelName, $filePath, $labelId, $extensionKey) {

		$translations = array ();

		// Get Fileinfos
		$fileInfo = self::getFileInfos($filePath);

		// Load Language File If Not Cached
		if ($filePath != $this->cacheFilePath || !$this->cacheLanguageFile) {

			// Set FilePath In Cache
			$this->cacheFilePath = $filePath;

			// XLIFF
			if ($fileInfo['Extension'] == 'xlf') {
				$this->cacheLanguageFile = self::getSystemLanguageFileXliff($filePath);
			} // XML
			else {
				$this->cacheLanguageFile = self::getSystemLanguageFileXml($filePath);
			}

		}

		if ($this->cacheLanguageFile) {

			// Checks Translations To Show
			if (is_array($this->availableLanguages) && count($this->availableLanguages) > 0) {

				// Loop Languages
				foreach ($this->availableLanguages as $language) {

					// XLIFF
					if ($fileInfo['Extension'] == 'xlf') {
						$translation = self::getSystemTranslationXliff($filePath, $language['LanguageKey'], $labelName, $extensionKey);
					} // XML
					else {
						$translation = self::getSystemTranslationXml($filePath, $language['LanguageKey'], $labelName);
					}

					// Add Translation
					$translations[] = array (
						'LabelId' => $labelId,
						'TranslationLanguage' => $language['LanguageKey'],
						'TranslationValue' => $translation,
						'TranslationEmpty' => $translation ? 0 : 1
					);

				}
			}

		}

		return $translations;

	}


	/**
	 * @param $filePath
	 * @param $languageKey
	 * @param $labelName
	 * @param $extensionKey
	 * @return string
	 */
	private function getSystemTranslationXliff($filePath, $languageKey, $labelName, $extensionKey) {

		// While First Loop Get Translation From l10n (And Create File If Not Done Yet)
		if ($filePath != $this->cacheTranslationsPath || $languageKey != $this->cacheTranslationLanguage) {

			$this->cachedTranslations = array ();

			// Get Fileinfo
			$fileInfo = self::getFileInfos($filePath);

			// Path To Translation In Extension
			$originalTranslationPath = $fileInfo['Dirname'] . $languageKey . '.' . $fileInfo['Basename'];

			// Get Path To l10n Location
			$translationFileName = GeneralUtility::llXmlAutoFileName($filePath, $languageKey);
			$translationFilePath = GeneralUtility::getFileAbsFileName($translationFileName);

			// Check If L10n File Available Otherwise Create One
			self::isSystemTranslationAvailableXliff($languageKey, $translationFilePath, $extensionKey);

			// Get Data From L10n File
			$this->cachedTranslations[$languageKey] = self::getSystemLanguageFileXliff($translationFilePath, $languageKey);

			// Get Data From Original Translation
			$this->cachedOriginalTranslations[$languageKey] = self::getSystemLanguageFileXliff($originalTranslationPath, $languageKey);

			// Sync Data From L10n With Extension XML
			self::syncSystemTranslationXliff($languageKey, $translationFilePath, $extensionKey);

			// Set New Cached Path
			$this->cacheTranslationsPath = $filePath;

			// Set New Cached Language
			$this->cacheTranslationLanguage = $languageKey;

		}

		// Return Translation If Available
		if ($this->cachedTranslations[$languageKey][$labelName]) {
			return $this->cachedTranslations[$languageKey][$labelName][0]['target'];
		}

		// We Always Need A Translation In DB
		return '';

	}


	/**
	 * @param $filePath
	 * @param $languageKey
	 * @param $labelName
	 * @return string
	 */
	private function getSystemTranslationXml($filePath, $languageKey, $labelName) {

		// While First Loop Get Translation From l10n (And Create File If Not Done Yet)
		if ($filePath != $this->cacheTranslationsPath || $languageKey != $this->cacheTranslationLanguage) {

			// Get l10n Location
			$translationFileName = GeneralUtility::llXmlAutoFileName($filePath, $languageKey);
			$translationFilePath = GeneralUtility::getFileAbsFileName($translationFileName);

			// Check If L10n File Available Otherwise Create One
			self::isSystemTranslationAvailableXml($languageKey, $translationFilePath);

			// Get Data From L10n File
			$this->cachedTranslations[$languageKey] = self::getSystemLanguageFileXml($translationFilePath);

			// Set New Cached Path
			$this->cacheTranslationsPath = $filePath;

			// Set New Cached Language
			$this->cacheTranslationLanguage = $languageKey;

			// Sync Data From L10n With Extension XML
			self::syncSystemTranslationXml(
				$languageKey,
				$translationFilePath
			);

		}

		// Return Translation If Available
		if ($this->cachedTranslations[$languageKey]['data'][$languageKey][$labelName]) {
			return $this->cachedTranslations[$languageKey]['data'][$languageKey][$labelName];
		}

		// We Always Need A Translation In DB
		return '';
	}


	/**
	 * @param $languageKey
	 * @param $translationFilePath
	 * @param $extensionKey
	 * @return void
	 */
	private function isSystemTranslationAvailableXliff($languageKey, $translationFilePath, $extensionKey) {

		// Create L10n File & Folder
		if ($translationFilePath && !@is_file($translationFilePath)) {

			// Set Directory
			$deepDir = dirname(substr($translationFilePath, strlen($this->sitePath))) . '/';

			// Create XLS & Directory
			if (GeneralUtility::isFirstPartOfStr($deepDir, $this->l10nPath . $languageKey . '/')) {
				GeneralUtility::mkdir_deep($this->sitePath, $deepDir);

				self::writeTranslationXliff(array (), $translationFilePath, $languageKey, $extensionKey);
			}

		}

	}


	/**
	 * @param  $languageKey
	 * @param  $translationFilePath
	 * @return void
	 */
	private function isSystemTranslationAvailableXml($languageKey, $translationFilePath) {

		// Create L10n File
		if ($translationFilePath && !@is_file($translationFilePath)) {

			// Copy XML Data From Extension To L10n
			if ($languageKey == 'en' && $this->copyDefaultLanguage) {
				// Copy Default Labels To English
				$file['data'][$languageKey] = $this->cacheLanguageFile['data']['default'];
			} else {
				$file['data'][$languageKey] = $this->cacheLanguageFile['data'][$languageKey];
			}

			// Set Directory
			$deepDir = dirname(substr($translationFilePath, strlen($this->sitePath))) . '/';

			// Create XML & Directory
			if (GeneralUtility::isFirstPartOfStr($deepDir, $this->l10nPath . $languageKey . '/')) {

				GeneralUtility::mkdir_deep($this->sitePath, $deepDir);
				self::writeTranslationXml($file, $translationFilePath);

			}

		}
	}


	/**
	 * @param $languageKey
	 * @param $translationFilePath
	 * @param $extensionKey
	 * @return void
	 */
	private function syncSystemTranslationXliff($languageKey, $translationFilePath, $extensionKey) {

		if (is_array($this->cacheLanguageFile) && count($this->cacheLanguageFile) > 0) {
			foreach ($this->cacheLanguageFile as $labelName => $labelDefault) {

				// Set Source
				$this->cachedTranslations[$languageKey][$labelName][0]['source'] = $labelDefault[0]['source'];

				// Set 'l10n' Label If Available
				$l10nLabel = $this->cachedTranslations[$languageKey][$labelName][0]['target'];

				// No Sync Needed If 'l10n' Already Defined
				// Otherwise Check If Labels Are Available Somewhere
				if (empty($l10nLabel)) {

					// Copy 'default' To 'en' If Activated In Settings
					if ($languageKey === 'en' && $this->copyDefaultLanguage) {
						$this->cachedTranslations[$languageKey][$labelName][0]['target'] = $labelDefault[0]['target'];
					}


					// Sync With Translation In Extension Dir
					if ($this->cachedOriginalTranslations[$languageKey]) {

						// Label From Original Translation
						$originalTranslationLabel = $this->cachedOriginalTranslations[$languageKey][$labelName][0]['target'];

						// Set Original Translation If Available
						if (!empty($originalTranslationLabel)) {
							$this->cachedTranslations[$languageKey][$labelName][0]['target'] = $originalTranslationLabel;
						}

					}

				}

				// Unset If No Data Available
				if (empty($this->cachedTranslations[$languageKey][$labelName][0]['target'])) {
					unset($this->cachedTranslations[$languageKey][$labelName]);
				}

			}


			// Write 'l10n' File
			self::writeTranslationXliff($this->cachedTranslations, $translationFilePath, $languageKey, $extensionKey);

		}

	}


	/**
	 * @param $languageKey
	 * @param $translationFilePath
	 * @return void
	 */
	private function syncSystemTranslationXml($languageKey, $translationFilePath) {

		$changes = 0;
		$labelsDefault = $this->cacheLanguageFile['data']['default'];

		if (is_array($labelsDefault)) {
			foreach ($labelsDefault as $labelName => $labelDefault) {

				// Label From L10n
				$labelL10n = $this->cachedTranslations[$languageKey]['data'][$languageKey][$labelName];


				// Sync EN With Default If Activated
				if ($languageKey == 'en' && $this->copyDefaultLanguage) {
					// Do Nothing
				} else {
					$labelDefault = $this->cacheLanguageFile['data'][$languageKey][$labelName];
				}

				// Compare Default Label With Label From L10n
				if (!empty($labelDefault) && empty($labelL10n)) {
					$this->cachedTranslations[$languageKey]['data'][$languageKey][$labelName] = $labelDefault;
					++$changes;
				}

			}

			// If There Are Changes Write It To XML File
			if ($changes > 0) {
				self::writeTranslationXml($this->cachedTranslations[$languageKey], $translationFilePath);
			}

		}

	}


	/**
	 * @param $file
	 * @param $path
	 * @param $languageKey
	 * @param $extensionKey
	 * @return void
	 */
	private function writeTranslationXliff($file, $path, $languageKey, $extensionKey) {

		$xmlFile = array ();

		$xmlFile[] = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>';
		$xmlFile[] = '<xliff version="1.0">';
		$xmlFile[] = '	<file source-language="en"' . ($languageKey !== 'default' ? ' target-language="' . $languageKey . '"' : '')
			. ' datatype="plaintext" original="messages" date="' . gmdate('Y-m-d\TH:i:s\Z') . '"'
			. ' product-name="' . $extensionKey . '">';
		$xmlFile[] = '		<header/>';
		$xmlFile[] = '		<body>';

		if (is_array($file[$languageKey]) && count($file[$languageKey]) > 0) {
			foreach ($file[$languageKey] as $Key => $Data) {

				$source = $Data[0]['source'];
				$target = $Data[0]['target'];

				if ($languageKey === 'default') {
					$xmlFile[] = '			<trans-unit id="' . $Key . '">';
					$xmlFile[] = '				<source>' . htmlspecialchars($source) . '</source>';
					$xmlFile[] = '			</trans-unit>';
				} else {
					$xmlFile[] = '			<trans-unit id="' . $Key . '" approved="yes">';
					$xmlFile[] = '				<source>' . htmlspecialchars($source) . '</source>';
					$xmlFile[] = '				<target>' . htmlspecialchars($target) . '</target>';
					$xmlFile[] = '			</trans-unit>';
				}
			}
		}

		$xmlFile[] = '		</body>';
		$xmlFile[] = '	</file>';
		$xmlFile[] = '</xliff>';

		GeneralUtility::writeFile($path, implode(LF, $xmlFile));
	}


	/**
	 * @param      $file
	 * @param      $path
	 * @param bool $saveToOriginal
	 * @return bool
	 */
	private function writeTranslationXml($file, $path, $saveToOriginal = FALSE) {

		$xmlOptions = array (
			'parentTagMap' => array (
				'data' => 'languageKey',
				'orig_hash' => 'languageKey',
				'orig_text' => 'languageKey',
				'labelContext' => 'label',
				'languageKey' => 'label'
			)
		);

		$xmlFile = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' . chr(10);
		$xmlFile .= GeneralUtility::array2xml($file, '', 0, $saveToOriginal ? 'T3locallang' : 'T3locallangExt', 0, $xmlOptions);

		GeneralUtility::writeFile($path, $xmlFile);
	}


	/**
	 * @param $extensionPath
	 * @param $extensionId
	 * @return array
	 */
	private function getSystemFiles($extensionPath, $extensionId) {

		$files = array ();

		// Get 'llxml' Files
		$xmlFiles = self::getSystemFilesInPath($extensionPath, 'xml');

		// Get 'xliff' Files
		$xliffFiles = self::getSystemFilesInPath($extensionPath, 'xlf');

		// Compare 'llxml' and 'xliff' Files
		$tempFiles = self::getComparedSystemFiles($xmlFiles, $xliffFiles);

		// Adds New Keys
		if (is_array($tempFiles)) {
			foreach ($tempFiles as $key => $file) {

				$files[] = array (
					'ExtensionId' => $extensionId,
					'FileKey' => $tempFiles[$key]
				);

			}
		}

		return $files;
	}


	/**
	 * @param $extensionPath
	 * @param string $fileExtension
	 * @return array
	 */
	private function getSystemFilesInPath($extensionPath, $fileExtension = 'xml') {

		// Get Extension Files
		$tempFilesXml1 = GeneralUtility::getAllFilesAndFoldersInPath(
			array (),
			$extensionPath,
			$fileExtension,
			0,
			99,
			'\.svn'
		);

		$tempFilesXml2 = GeneralUtility::removePrefixPathFromList(
			$tempFilesXml1,
			$extensionPath
		);

		return $tempFilesXml2;

	}


	/**
	 * @param $xmlFiles
	 * @param $xliffFiles
	 * @return array
	 */
	private function getComparedSystemFiles($xmlFiles, $xliffFiles) {

		$files = array ();
		$comparedFiles = array ();

		// Add All Xml's
		foreach ($xmlFiles as $key => $xmlFile) {

			$xmlFileInfo = self::getFileInfos($xmlFile);

			$comparedFiles[$xmlFileInfo['Filename']] = $xmlFileInfo['Extension'];

		}

		// Add All Xliff's
		foreach ($xliffFiles as $key => $xliffFile) {

			$xliffFileInfo = self::getFileInfos($xliffFile);

			$comparedFiles[$xliffFileInfo['Filename']] = $xliffFileInfo['Extension'];

		}

		// Prepare Array For Return
		foreach ($comparedFiles as $filename => $extension) {
			$files[] = $filename . '.' . $extension;
		}

		return $files;
	}


	/**
	 * @param $file
	 * @return array
	 */
	private function getFileInfos($file) {

		$fileInfo = array ();

		// Explode Array By Points -> FileExtension Should Be Last
		$fileArray = explode('.', $file);

		// Reverse Array
		// -> FileExtension Is Now First Element
		// -> FileBasename Is Now Second Part
		$fileArray = array_reverse($fileArray);

		$fileInfo['Extension'] = $fileArray[0];
		$fileInfo['Filename'] = $fileArray[1];

		// Add Basename
		$fileInfo['Basename'] = pathinfo($file, PATHINFO_BASENAME);

		// Add Dirname
		$fileInfo['Dirname'] = pathinfo($file, PATHINFO_DIRNAME) . '/';

		return $fileInfo;
	}


	/**
	 * @param        $file
	 * @param string $languageKey
	 * @return array|bool
	 */
	private function getSystemLanguageFileXliff($file, $languageKey = 'default') {

		if (is_file($file)) {

			// Surpress xml errors
			libxml_use_internal_errors(TRUE);

			// Load Xls Object
			$xml = simplexml_load_file($file, 'SimpleXMLElement', \LIBXML_NOWARNING);

			// Clear xml errors and activate errors again
			libxml_use_internal_errors(FALSE);

			// Format Xls Object
			return self::formatSimpleXmlObject_XLS($xml, $languageKey);

		}

		return FALSE;

	}


	/**
	 * @param $file
	 * @return array|bool
	 */
	private function getSystemLanguageFileXml($file) {

		if (is_file($file)) {

			// Surpress xml errors
			libxml_use_internal_errors(TRUE);

			// Load Xml Object
			$xml = simplexml_load_file($file, 'SimpleXMLElement', \LIBXML_NOWARNING);

			// Clear xml errors and activate errors again
			libxml_use_internal_errors(FALSE);

			// Format Xml Object
			return self::formatSimpleXmlObject_XML($xml);

		}

		return FALSE;
	}


	/**
	 * Function 'doParsingFromRoot' from Class 't3lib_l10n_parser_Xliff'
	 *
	 * @param \SimpleXMLElement $simpleXmlObject
	 * @param                   $languageKey
	 * @return array
	 */
	private function formatSimpleXmlObject_XLS(\SimpleXMLElement $simpleXmlObject, $languageKey) {

		$parsedData = array ();
		/** @var \SimpleXMLElement $bodyOfFileTag */
		$bodyOfFileTag = $simpleXmlObject->file->body;

		if (!is_object($bodyOfFileTag)) {
			return $parsedData;
		}

		foreach ($bodyOfFileTag->children() as $translationElement) {

			$elementName = $translationElement->getName();

			if ($elementName === 'trans-unit' && !isset($translationElement['restype'])) {
				// If restype would be set, it could be metadata from Gettext to XLIFF conversion (and we don't need this data)

				if ($languageKey === 'default') {
					// Default language coming from an XLIFF template (no target element)
					$parsedData[(string)$translationElement['id']][0] = array (
						'source' => (string)$translationElement->source,
						'target' => (string)$translationElement->source,
					);
				} else {
					// @todo Support "approved" attribute
					$parsedData[(string)$translationElement['id']][0] = array (
						'source' => (string)$translationElement->source,
						'target' => (string)$translationElement->target,
					);
				}
			} elseif ($elementName === 'group' && isset($translationElement['restype']) && (string)$translationElement['restype'] === 'x-gettext-plurals') {
				// This is a translation with plural forms
				$parsedTranslationElement = array ();

				foreach ($translationElement->children() as $translationPluralForm) {
					if ($translationPluralForm->getName() === 'trans-unit') {
						// When using plural forms, ID looks like this: 1[0], 1[1] etc
						$formIndex = substr((string)$translationPluralForm['id'], strpos((string)$translationPluralForm['id'], '[') + 1, -1);

						if ($languageKey === 'default') {
							// Default language come from XLIFF template (no target element)
							$parsedTranslationElement[(int)$formIndex] = array (
								'source' => (string)$translationPluralForm->source,
								'target' => (string)$translationPluralForm->source,
							);
						} else {
							// @todo Support "approved" attribute
							$parsedTranslationElement[(int)$formIndex] = array (
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
	 * @param $simpleXmlObject
	 * @return array|bool
	 */
	private function formatSimpleXmlObject_XML($simpleXmlObject) {

		if ($simpleXmlObject) {

			$xmlArray = array ();

			// Meta Array
			if (is_array($simpleXmlObject->meta) || is_object($simpleXmlObject->meta)) {

				$xmlArray['meta'] = array ();

				foreach ($simpleXmlObject->meta as $meta) {
					foreach ($meta as $metaData) {

						$metaKey = $metaData->getName();
						$metaValue = trim($metaData[0]);

						if (!empty($metaKey) && is_string($metaKey)) {
							$xmlArray['meta'][$metaKey] = (string)$metaValue;
						}

					}
				}

				// Unset If Not Used
				if (empty($xmlArray['meta'])) {
					unset($xmlArray['meta']);
				}

			}

			// Data Array
			if (is_array($simpleXmlObject->data->languageKey) || is_object($simpleXmlObject->data->languageKey)) {

				$xmlArray['data'] = array ();

				foreach ($simpleXmlObject->data->languageKey as $language) {

					// LanguageKey
					$languageKey = self::getSimpleXmlObjectAttributesIndex($language->attributes());

					if (!empty($languageKey) && is_string($languageKey)) {
						if (is_array($language->label) || is_object($language->label)) {
							foreach ($language->label as $label) {

								// LabelName
								$labelName = self::getSimpleXmlObjectAttributesIndex($label->attributes());

								// LabelValue
								if (!empty($labelName) && is_string($labelName)) {
									$xmlArray['data'][$languageKey][$labelName] = (string)trim($label[0]);
								}

							}
						}
					}
				}

				// Unset If Not Used
				if (empty($xmlArray['data'])) {
					unset($xmlArray['data']);
				}

			}

			return $xmlArray;

		}

		return FALSE;

	}


	/**
	 * @param $attributesObject
	 * @return string
	 */
	private function getSimpleXmlObjectAttributesIndex($attributesObject) {

		// Get Attributes
		if (is_array($attributesObject) || is_object($attributesObject)) {

			$attributes = array ();

			foreach ($attributesObject as $name => $value) {
				$attributes[$name] = trim($value);
			}

			// Return Index
			if (!empty($attributes['index'])) {
				return (string)$attributes['index'];
			}
		}

		return '';
	}


	/**
	 * @param  $string
	 * @return string
	 */
	private function getCleanedString($string) {

		if ($string) {

			$string = htmlentities($string);

		}

		return $string;

	}


	/**
	 * @param  $extensionPath
	 * @param  $extensionKey
	 * @return string
	 */
	private function getExtensionIcon($extensionPath, $extensionKey) {

		$extensionIcon = '';

		if ($extensionPath && $extensionKey) {

			if (file_exists(ExtensionManagementUtility::extPath($extensionKey) . 'ext_icon.gif')) {
				$extensionIcon = ExtensionManagementUtility::extRelPath($extensionKey) . 'ext_icon.gif';
			} else {
				$extensionIcon = ExtensionManagementUtility::extRelPath('snowbabel') . 'Resources/Public/Images/Miscellaneous/ext_icon.gif';
			}

		}

		return $extensionIcon;

	}


	/**
	 * @param  $extensionKey
	 * @return bool
	 */
	private function isExtensionLoaded($extensionKey) {

		$isLoaded = FALSE;

		if (isset($extensionKey)) {
			$installedExtensions = $this->loadedExtensions;

			$check = array_key_exists($extensionKey, $installedExtensions);

			if ($check) {
				return TRUE;
			}
		}

		return $isLoaded;

	}


	/**
	 * @param  $filePath
	 * @param  $language
	 * @return void
	 */
	private function deleteSystemCache($filePath, $language) {

		// Delete Cached Language File
		$cacheFileName = self::getCacheFileName($filePath, $language);
		GeneralUtility::unlink_tempfile($cacheFileName);

		// Delete 'default'
		if ($language != 'default') {
			$cacheFileNameDefault = self::getCacheFileName($filePath);
			GeneralUtility::unlink_tempfile($cacheFileNameDefault);
		}
	}


	/**
	 * @param        $filePath
	 * @param string $language
	 * @return string
	 */
	private function getCacheFileName($filePath, $language = 'default') {

		$hashSource = substr($filePath, strlen(PATH_site)) . '|' . date('d-m-Y H:i:s', filemtime($filePath)) . '|version=2.3';
		$hash = '_' . GeneralUtility::shortMD5($hashSource);
		$tempPath = PATH_site . 'typo3temp/llxml/';
		$fileExtension = substr(basename($filePath), 10, 15);

		return $tempPath . $fileExtension . $hash . '.' . $language . '.' . 'utf-8' . '.cache';
	}

}