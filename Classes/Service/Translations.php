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
class Translations
{


    /**
     * @var Configuration
     */
    protected $confObj;


    /**
     * @var
     */
    protected $CopyDefaultLanguage;


    /**
     * @var
     */
    protected $AvailableLanguages;


    /**
     * @var
     */
    protected $ApprovedExtensions;


    /**
     * @var
     */
    protected $LocalExtensionPath;


    /**
     * @var
     */
    protected $SystemExtensionPath;


    /**
     * @var
     */
    protected $GlobalExtensionPath;


    /**
     * @var
     */
    protected $SitePath;


    /**
     * @var
     */
    protected $L10nPath;


    /**
     * @var
     */
    protected $LoadedExtensions;


    /**
     * @var string
     */
    protected $CacheTranslationsPath = '';


    /**
     * @var string
     */
    protected $CacheTranslationLanguage = '';


    /**
     * @var
     */
    protected $CachedTranslations;


    /**
     * @var
     */
    protected $CachedOriginalTranslations;


    /**
     * @var
     */
    protected $CacheFilePath = '';


    /**
     * @var
     */
    protected $CacheLanguageFile = array();

    /**
     * @var
     */
    protected $languageFactory;


    /**
     * @param $confObj
     * @return void
     */
    public function init($confObj)
    {

        $this->confObj = $confObj;

        // get Application params
        $this->CopyDefaultLanguage = $this->confObj->getApplicationConfiguration('CopyDefaultLanguage');
        $this->AvailableLanguages = $this->confObj->getApplicationConfiguration('AvailableLanguages');
        $this->ApprovedExtensions = $this->confObj->getApplicationConfiguration('ApprovedExtensions');
        $this->LocalExtensionPath = $this->confObj->getApplicationConfiguration('LocalExtensionPath');
        $this->SystemExtensionPath = $this->confObj->getApplicationConfiguration('SystemExtensionPath');
        $this->GlobalExtensionPath = $this->confObj->getApplicationConfiguration('GlobalExtensionPath');

        // get Extension params
        $this->SitePath = PATH_site;
        $this->L10nPath = $this->confObj->getExtensionConfiguration('L10nPath');
        $this->LoadedExtensions = $this->confObj->getExtensionConfigurationLoadedExtensions();
        $this->languageFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LocalizationFactory::class);
    }


    /**
     * @return array
     */
    public function getExtensions()
    {

        $Extensions = self::getDirectories();

        $Extensions = self::checkApprovedExtensions($Extensions);

        $Extensions = self::getExtensionData($Extensions);

        return $Extensions;

    }


    /**
     * @return array
     */
    public function getDirectories()
    {

        $Directories = array();
        $RawDirectories = array();

        // get local extension dirs
        $RawDirectories['Local'] = self::getSystemDirectories($this->SitePath . $this->LocalExtensionPath);

        // get system extension dirs
        $RawDirectories['System'] = self::getSystemDirectories($this->SitePath . $this->SystemExtensionPath);

        // get global extension dirs
        $RawDirectories['Global'] = self::getSystemDirectories($this->SitePath . $this->GlobalExtensionPath);


        if (is_array($RawDirectories['System']) && count($RawDirectories['System']) > 0) {
            $Directories = array_merge($Directories, $RawDirectories['System']);
        }

        if (is_array($RawDirectories['Global']) && count($RawDirectories['Global']) > 0) {
            $Directories = array_merge($Directories, $RawDirectories['Global']);
        }

        if (is_array($RawDirectories['Local']) && count($RawDirectories['Local']) > 0) {
            $Directories = array_merge($Directories, $RawDirectories['Local']);
        }

        // Removes Double Entries
        $Directories = array_unique($Directories);

        return $Directories;
    }


    /**
     * @param $Extensions
     * @return array
     */
    public function getFiles($Extensions)
    {

        $Files = array();

        if (is_array($Extensions) && count($Extensions) > 0) {
            foreach ($Extensions as $Extension) {

                // Get Extension Files
                $Files[$Extension['uid']] = self::getSystemFiles($Extension['ExtensionPath'], $Extension['uid']);

            }
        }


        return $Files;
    }


    /**
     * @param $Files
     * @return array
     */
    public function getLabels($Files)
    {

        $Labels = array();

        if (is_array($Files) && count($Files)) {

            foreach ($Files as $File) {

                // Get Fileinfos
                $FileInfo = self::getFileInfos($File['ExtensionPath'] . $File['FileKey']);

                // XLIFF
                if ($FileInfo['Extension'] == 'xlf') {
                    $Labels[$File['FileId']] = self::getSystemLabelsXliff($File['ExtensionPath'] . $File['FileKey'], $File['FileId']);
                } // XML
                else {
                    $Labels[$File['FileId']] = self::getSystemLabelsXml($File['ExtensionPath'] . $File['FileKey'], $File['FileId']);

                }

            }

        }

        return $Labels;

    }


    /**
     * @param $Labels
     * @return array
     */
    public function getTranslations($Labels)
    {

        $Translations = array();

        if (is_array($Labels) && count($Labels)) {
            foreach ($Labels as $Label) {
                $Translations[$Label['LabelId']] = self::getSystemTranslations($Label['LabelName'], $Label['ExtensionPath'] . $Label['FileKey'], $Label['LabelId'], $Label['ExtensionKey']);
            }
        }

        return $Translations;

    }


    /**
     * @param $Translation
     * @return void
     */
    public function updateTranslation($Translation)
    {

        $FilePath = $Translation['ExtensionPath'] . $Translation['FileKey'];
        $LanguageKey = $Translation['TranslationLanguage'];
        $ExtensionKey = $Translation['ExtensionKey'];
        $LabelName = $Translation['LabelName'];
        $TranslationValue = $Translation['TranslationValue'];

        // Get l10n Location
        $TranslationFileName = GeneralUtility::llXmlAutoFileName($FilePath, $LanguageKey);
        $TranslationFilePath = GeneralUtility::getFileAbsFileName($TranslationFileName);

        // Update XLIFF
        self::updateTranslationXlf($TranslationValue, $LabelName, $LanguageKey, $TranslationFilePath, $ExtensionKey);

        // Delete Temp Files In typo3temp-Folder
        self::deleteSystemCache($FilePath, $LanguageKey);

    }


    /**
     * @param $TranslationValue
     * @param $LabelName
     * @param $LanguageKey
     * @param $TranslationFilePath
     * @param $ExtensionKey
     * @return void
     */
    private function updateTranslationXlf($TranslationValue, $LabelName, $LanguageKey, $TranslationFilePath, $ExtensionKey)
    {

        // Get Data From L10n File
        $Translation[$LanguageKey] = self::getSystemLanguageFileXliff($TranslationFilePath, $LanguageKey);

        // Change Value If Not Empty
        if (strlen($TranslationValue)) {
            $Translation[$LanguageKey][$LabelName][0]['target'] = $TranslationValue;
        } // Otherwise Unset Value
        else {
            if ($Translation[$LanguageKey][$LabelName]) {
                unset($Translation[$LanguageKey][$LabelName]);
            }

        }

        // Write File
        self::writeTranslationXliff($Translation, $TranslationFilePath, $LanguageKey, $ExtensionKey);

    }


    /**
     * @param $Extensions
     * @return array
     *
     * todo: renaming
     */
    private function checkApprovedExtensions($Extensions)
    {


        if (count($Extensions) > 0) {

            $ExtensionsNew = array();

            foreach ($Extensions as $Extension) {

                // Check If Extension Is Available
                if (in_array($Extension, $this->ApprovedExtensions)) {
                    array_push($ExtensionsNew, $Extension);
                }

            }

            // Set New Extensionlist
            $Extensions = $ExtensionsNew;
        }


        return $Extensions;

    }


    /**
     * @param $ExtensionList
     * @return array
     */
    private function getExtensionData($ExtensionList)
    {

        $Extensions = array();

        // Get Data For Every Extension
        if (is_array($ExtensionList)) {
            foreach ($ExtensionList as $ExtensionKey) {

                $ExtensionData = self::getExtension($ExtensionKey);

                // Just Add If Data Available
                if ($ExtensionData) {
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
    private function getExtension($ExtensionKey)
    {

        if (is_string($ExtensionKey)) {

            // Locate Where Extension Is Installed
            $ExtensionLocation = self::getSystemExtensionLocation($ExtensionKey);

            // Get Extension Data From EmConf
            $EMConf = self::getSystemEMConf($ExtensionLocation['Path']);

            // Add Extension Data
            $ExtensionData = array(
                'ExtensionKey' => $ExtensionKey,
                'ExtensionTitle' => $EMConf['ExtensionTitle'] ? self::getCleanedString($EMConf['ExtensionTitle']) : $ExtensionKey,
                'ExtensionDescription' => self::getCleanedString($EMConf['ExtensionDescription']),
                'ExtensionCategory' => self::getCleanedString($EMConf['ExtensionCategory']),
                'ExtensionIcon' => self::getExtensionIcon($ExtensionLocation, $ExtensionKey),
                'ExtensionLocation' => $ExtensionLocation['Location'],
                'ExtensionPath' => $ExtensionLocation['Path'],
                'ExtensionLoaded' => self::isExtensionLoaded($ExtensionKey)
            );

            return $ExtensionData;

        }

        return false;

    }


    /**
     * @param  $ExtensionKey
     * @return bool
     */
    private function getSystemExtensionLocation($ExtensionKey)
    {

        $ExtensionPath = false;

        // ORDER'S IMPORTANT!

        // Check System Extension
        $TempExtensionPath = $this->SitePath . $this->SystemExtensionPath . $ExtensionKey . '/';
        if (is_dir($TempExtensionPath)) {
            $ExtensionPath['Path'] = $TempExtensionPath;
            $ExtensionPath['Location'] = 'System';
        }

        // Check Global Extension
        $TempExtensionPath = $this->SitePath . $this->GlobalExtensionPath . $ExtensionKey . '/';
        if (is_dir($TempExtensionPath)) {
            $ExtensionPath['Path'] = $TempExtensionPath;
            $ExtensionPath['Location'] = 'Global';
        }


        // Check Local Extension
        $TempExtensionPath = $this->SitePath . $this->LocalExtensionPath . $ExtensionKey . '/';
        if (is_dir($TempExtensionPath)) {
            $ExtensionPath['Path'] = $TempExtensionPath;
            $ExtensionPath['Location'] = 'Local';
        }


        return $ExtensionPath;
    }


    /**
     * todo: check for better solution
     *
     * @param  $ExtensionPath
     * @return bool
     */
    private function getSystemEMConf($ExtensionPath)
    {

        if ($ExtensionPath) {

            // Set EMConf Path
            $EMConfPath = $ExtensionPath . 'ext_emconf.php';

            if (file_exists($EMConfPath)) {

                // Include EMConf
                $EM_CONF = null;
                include($EMConfPath);

                // Add Needed EMConf Params To Array
                $EMConf['ExtensionCategory'] = $EM_CONF['']['category'];
                $EMConf['ExtensionTitle'] = $EM_CONF['']['title'];
                $EMConf['ExtensionDescription'] = $EM_CONF['']['description'];

                return $EMConf;
            }

        }

        return false;

    }


    /**
     * @param  $Path
     * @return array|null
     */
    private function getSystemDirectories($Path)
    {

        if (isset($Path)) {

            $Directories = GeneralUtility::get_dirs($Path);

            if (is_array($Directories)) {
                return $Directories;
            }

        }

        return null;

    }


    /**
     * @param $FilePath
     * @param $FileId
     * @return array
     */
    private function getSystemLabelsXliff($FilePath, $FileId)
    {

        $Labels = array();

        // Get LanguageFile
        $LanguageFile = $this->languageFactory->getParsedData($FilePath, 'default', '', '', false);

        // Language File Available?
        if ($LanguageFile) {

            // Set System Labels
            $LabelData = $LanguageFile['default'];

            if (is_array($LabelData)) {
                foreach ($LabelData as $LabelName => $LabelDefault) {

                    $Labels[] = array(
                        'FileId' => $FileId,
                        'LabelName' => $LabelName,
                        'LabelDefault' => $LabelDefault[0]['source']
                    );

                }
            }

        }

        return $Labels;

    }


    /**
     * @param $FilePath
     * @param $FileId
     * @return array
     */
    private function getSystemLabelsXml($FilePath, $FileId)
    {

        $Labels = array();

        // Get Language File
        $LanguageFile = self::getSystemLanguageFileXml($FilePath);

        // Language File Available?
        if ($LanguageFile) {

            // Set System Labels
            $LabelData = $LanguageFile['data']['default'];

            if (is_array($LabelData)) {
                foreach ($LabelData as $LabelName => $LabelDefault) {

                    $Labels[] = array(
                        'FileId' => $FileId,
                        'LabelName' => $LabelName,
                        'LabelDefault' => $LabelDefault
                    );

                }

            }

        }

        return $Labels;
    }


    /**
     * @param $LabelName
     * @param $FilePath
     * @param $LabelId
     * @param $ExtensionKey
     * @return array
     */
    private function getSystemTranslations($LabelName, $FilePath, $LabelId, $ExtensionKey)
    {

        $Translations = array();

        // Get Fileinfos
        $FileInfo = self::getFileInfos($FilePath);

        // Load Language File If Not Cached
        if ($FilePath != $this->CacheFilePath || !$this->CacheLanguageFile) {

            // Set FilePath In Cache
            $this->CacheFilePath = $FilePath;

            // XLIFF
            if ($FileInfo['Extension'] == 'xlf') {
                $this->CacheLanguageFile = self::getSystemLanguageFileXliff($FilePath);
            } // XML
            else {
                $this->CacheLanguageFile = self::getSystemLanguageFileXml($FilePath);
            }

        }

        if ($this->CacheLanguageFile) {

            // Checks Translations To Show
            if (is_array($this->AvailableLanguages) && count($this->AvailableLanguages) > 0) {

                // Loop Languages
                foreach ($this->AvailableLanguages as $Language) {

                    // XLIFF
                    if ($FileInfo['Extension'] == 'xlf') {
                        $Translation = self::getSystemTranslationXliff($FilePath, $Language['LanguageKey'], $LabelName, $ExtensionKey);
                    } // XML
                    else {
                        $Translation = self::getSystemTranslationXml($FilePath, $Language['LanguageKey'], $LabelName);
                    }

                    // Add Translation
                    $Translations[] = array(
                        'LabelId' => $LabelId,
                        'TranslationLanguage' => $Language['LanguageKey'],
                        'TranslationValue' => $Translation,
                        'TranslationEmpty' => $Translation ? 0 : 1
                    );

                }
            }

        }

        return $Translations;

    }


    /**
     * @param $FilePath
     * @param $LanguageKey
     * @param $LabelName
     * @param $ExtensionKey
     * @return string
     */
    private function getSystemTranslationXliff($FilePath, $LanguageKey, $LabelName, $ExtensionKey)
    {

        // While First Loop Get Translation From l10n (And Create File If Not Done Yet)
        if ($FilePath != $this->CacheTranslationsPath || $LanguageKey != $this->CacheTranslationLanguage) {

            $this->CachedTranslations = array();

            // Get Fileinfo
            $FileInfo = self::getFileInfos($FilePath);

            // Path To Translation In Extension
            $OriginalTranslationPath = $FileInfo['Dirname'] . $LanguageKey . '.' . $FileInfo['Basename'];

            // Get Path To l10n Location
            $TranslationFileName = GeneralUtility::llXmlAutoFileName($FilePath, $LanguageKey);
            $TranslationFilePath = GeneralUtility::getFileAbsFileName($TranslationFileName);

            // Check If L10n File Available Otherwise Create One
            self::isSystemTranslationAvailableXliff($LanguageKey, $TranslationFilePath, $ExtensionKey);

            // Get Data From L10n File
            $this->CachedTranslations[$LanguageKey] = self::getSystemLanguageFileXliff($TranslationFilePath, $LanguageKey);

            // Get Data From Original Translation
            $this->CachedOriginalTranslations[$LanguageKey] = self::getSystemLanguageFileXliff($OriginalTranslationPath, $LanguageKey);

            // Sync Data From L10n With Extension XML
            self::syncSystemTranslationXliff($LanguageKey, $TranslationFilePath, $ExtensionKey);

            // Set New Cached Path
            $this->CacheTranslationsPath = $FilePath;

            // Set New Cached Language
            $this->CacheTranslationLanguage = $LanguageKey;

        }

        // Return Translation If Available
        if ($this->CachedTranslations[$LanguageKey][$LabelName]) {
            return $this->CachedTranslations[$LanguageKey][$LabelName][0]['target'];
        }

        // We Always Need A Translation In DB
        return '';

    }


    /**
     * @param $FilePath
     * @param $LanguageKey
     * @param $LabelName
     * @return string
     */
    private function getSystemTranslationXml($FilePath, $LanguageKey, $LabelName)
    {

        // While First Loop Get Translation From l10n (And Create File If Not Done Yet)
        if ($FilePath != $this->CacheTranslationsPath || $LanguageKey != $this->CacheTranslationLanguage) {

            // Get l10n Location
            $TranslationFileName = GeneralUtility::llXmlAutoFileName($FilePath, $LanguageKey);
            $TranslationFilePath = GeneralUtility::getFileAbsFileName($TranslationFileName);

            // Check If L10n File Available Otherwise Create One
            self::isSystemTranslationAvailableXml($LanguageKey, $TranslationFilePath);

            // Get Data From L10n File
            $this->CachedTranslations[$LanguageKey] = self::getSystemLanguageFileXml($TranslationFilePath);

            // Set New Cached Path
            $this->CacheTranslationsPath = $FilePath;

            // Set New Cached Language
            $this->CacheTranslationLanguage = $LanguageKey;

            // Sync Data From L10n With Extension XML
            self::syncSystemTranslationXml(
                $LanguageKey,
                $TranslationFilePath
            );

        }

        // Return Translation If Available
        if ($this->CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName]) {
            return $this->CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName];
        }

        // We Always Need A Translation In DB
        return '';
    }


    /**
     * @param $LanguageKey
     * @param $TranslationFilePath
     * @param $ExtensionKey
     * @return void
     */
    private function isSystemTranslationAvailableXliff($LanguageKey, $TranslationFilePath, $ExtensionKey)
    {

        // Create L10n File & Folder
        if ($TranslationFilePath && !@is_file($TranslationFilePath)) {

            // Set Directory
            $DeepDir = dirname(substr($TranslationFilePath, strlen($this->SitePath))) . '/';

            // Create XLS & Directory
            if (GeneralUtility::isFirstPartOfStr($DeepDir, $this->L10nPath . $LanguageKey . '/')) {
                GeneralUtility::mkdir_deep($this->SitePath, $DeepDir);

                self::writeTranslationXliff(array(), $TranslationFilePath, $LanguageKey, $ExtensionKey);
            }

        }

    }


    /**
     * @param  $LanguageKey
     * @param  $TranslationFilePath
     * @return void
     */
    private function isSystemTranslationAvailableXml($LanguageKey, $TranslationFilePath)
    {

        // Create L10n File
        if ($TranslationFilePath && !@is_file($TranslationFilePath)) {

            // Copy XML Data From Extension To L10n
            if ($LanguageKey == 'en' && $this->CopyDefaultLanguage) {
                // Copy Default Labels To English
                $File['data'][$LanguageKey] = $this->CacheLanguageFile['data']['default'];
            } else {
                $File['data'][$LanguageKey] = $this->CacheLanguageFile['data'][$LanguageKey];
            }

            // Set Directory
            $DeepDir = dirname(substr($TranslationFilePath, strlen($this->SitePath))) . '/';

            // Create XML & Directory
            if (GeneralUtility::isFirstPartOfStr($DeepDir, $this->L10nPath . $LanguageKey . '/')) {

                GeneralUtility::mkdir_deep($this->SitePath, $DeepDir);
                self::writeTranslationXml($File, $TranslationFilePath);

            }

        }
    }


    /**
     * @param $LanguageKey
     * @param $TranslationFilePath
     * @param $ExtensionKey
     * @return void
     */
    private function syncSystemTranslationXliff($LanguageKey, $TranslationFilePath, $ExtensionKey)
    {

        if (is_array($this->CacheLanguageFile) && count($this->CacheLanguageFile) > 0) {
            foreach ($this->CacheLanguageFile as $LabelName => $LabelDefault) {

                // Set Source
                $this->CachedTranslations[$LanguageKey][$LabelName][0]['source'] = $LabelDefault[0]['source'];

                // Set 'l10n' Label If Available
                $L10nLabel = $this->CachedTranslations[$LanguageKey][$LabelName][0]['target'];

                // No Sync Needed If 'l10n' Already Defined
                // Otherwise Check If Labels Are Available Somewhere
                if (empty($L10nLabel)) {

                    // Copy 'default' To 'en' If Activated In Settings
                    if ($LanguageKey === 'en' && $this->CopyDefaultLanguage) {
                        $this->CachedTranslations[$LanguageKey][$LabelName][0]['target'] = $LabelDefault[0]['target'];
                    }


                    // Sync With Translation In Extension Dir
                    if ($this->CachedOriginalTranslations[$LanguageKey]) {

                        // Label From Original Translation
                        $OriginalTranslationLabel = $this->CachedOriginalTranslations[$LanguageKey][$LabelName][0]['target'];

                        // Set Original Translation If Available
                        if (!empty($OriginalTranslationLabel)) {
                            $this->CachedTranslations[$LanguageKey][$LabelName][0]['target'] = $OriginalTranslationLabel;
                        }

                    }

                }

                // Unset If No Data Available
                if (empty($this->CachedTranslations[$LanguageKey][$LabelName][0]['target'])) {
                    unset($this->CachedTranslations[$LanguageKey][$LabelName]);
                }

            }


            // Write 'l10n' File
            self::writeTranslationXliff($this->CachedTranslations, $TranslationFilePath, $LanguageKey, $ExtensionKey);

        }

    }


    /**
     * @param $LanguageKey
     * @param $TranslationFilePath
     * @return void
     */
    private function syncSystemTranslationXml($LanguageKey, $TranslationFilePath)
    {

        $Changes = 0;
        $LabelsDefault = $this->CacheLanguageFile['data']['default'];

        if (is_array($LabelsDefault)) {
            foreach ($LabelsDefault as $LabelName => $LabelDefault) {

                // Label From L10n
                $LabelL10n = $this->CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName];


                // Sync EN With Default If Activated
                if ($LanguageKey == 'en' && $this->CopyDefaultLanguage) {
                    // Do Nothing
                } else {
                    $LabelDefault = $this->CacheLanguageFile['data'][$LanguageKey][$LabelName];
                }

                // Compare Default Label With Label From L10n
                if (!empty($LabelDefault) && empty($LabelL10n)) {
                    $this->CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName] = $LabelDefault;
                    ++$Changes;
                }

            }

            // If There Are Changes Write It To XML File
            if ($Changes > 0) {
                self::writeTranslationXml($this->CachedTranslations[$LanguageKey], $TranslationFilePath);
            }

        }

    }


    /**
     * @param $File
     * @param $Path
     * @param $LanguageKey
     * @param $ExtensionKey
     * @return void
     */
    private function writeTranslationXliff($File, $Path, $LanguageKey, $ExtensionKey)
    {

        $XmlFile = array();

        $XmlFile[] = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>';
        $XmlFile[] = '<xliff version="1.0">';
        $XmlFile[] = '	<file source-language="en"' . ($LanguageKey !== 'default' ? ' target-language="' . $LanguageKey . '"' : '')
            . ' datatype="plaintext" original="messages" date="' . gmdate('Y-m-d\TH:i:s\Z') . '"'
            . ' product-name="' . $ExtensionKey . '">';
        $XmlFile[] = '		<header/>';
        $XmlFile[] = '		<body>';

        if (is_array($File[$LanguageKey]) && count($File[$LanguageKey]) > 0) {
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

        GeneralUtility::writeFile($Path, implode(LF, $XmlFile));
    }


    /**
     * @param      $File
     * @param      $Path
     * @param bool $SaveToOriginal
     * @return bool
     */
    private function writeTranslationXml($File, $Path, $SaveToOriginal = false)
    {

        $XmlOptions = array(
            'parentTagMap' => array(
                'data' => 'languageKey',
                'orig_hash' => 'languageKey',
                'orig_text' => 'languageKey',
                'labelContext' => 'label',
                'languageKey' => 'label'
            )
        );

        $XmlFile = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' . chr(10);
        $XmlFile .= GeneralUtility::array2xml($File, '', 0, $SaveToOriginal ? 'T3locallang' : 'T3locallangExt', 0, $XmlOptions);

        GeneralUtility::writeFile($Path, $XmlFile);
    }


    /**
     * @param $ExtensionPath
     * @param $ExtensionId
     * @return array
     */
    private function getSystemFiles($ExtensionPath, $ExtensionId)
    {

        $Files = array();

        // Get 'llxml' Files
        $XmlFiles = self::getSystemFilesInPath($ExtensionPath, 'xml');

        // Get 'xliff' Files
        $XliffFiles = self::getSystemFilesInPath($ExtensionPath, 'xlf');

        // Compare 'llxml' and 'xliff' Files
        $TempFiles = self::getComparedSystemFiles($XmlFiles, $XliffFiles);

        // Adds New Keys
        if (is_array($TempFiles)) {
            foreach ($TempFiles as $Key => $File) {

                $Files[] = array(
                    'ExtensionId' => $ExtensionId,
                    'FileKey' => $TempFiles[$Key]
                );

            }
        }

        return $Files;
    }


    /**
     * @param        $ExtensionPath
     * @param string $FileExtension
     * @return array
     */
    private function getSystemFilesInPath($ExtensionPath, $FileExtension = 'xml')
    {

        // Get Extension Files
        $TempFilesXml_1 = GeneralUtility::getAllFilesAndFoldersInPath(
            array(),
            $ExtensionPath,
            $FileExtension,
            0,
            99,
            '\.svn'
        );

        $TempFilesXml_2 = GeneralUtility::removePrefixPathFromList(
            $TempFilesXml_1,
            $ExtensionPath
        );

        return $TempFilesXml_2;

    }


    /**
     * @param $xmlFiles
     * @param $xliffFiles
     * @return array
     */
    private function getComparedSystemFiles($xmlFiles, $xliffFiles)
    {

        $Files = array();
        $ComparedFiles = array();

        // Add All Xml's
        foreach ($xmlFiles as $Key => $xmlFile) {

            $xmlFileInfo = self::getFileInfos($xmlFile);

            $ComparedFiles[$xmlFileInfo['Filename']] = $xmlFileInfo['Extension'];

        }

        // Add All Xliff's
        foreach ($xliffFiles as $Key => $xliffFile) {

            $xliffFileInfo = self::getFileInfos($xliffFile);

            $ComparedFiles[$xliffFileInfo['Filename']] = $xliffFileInfo['Extension'];

        }

        // Prepare Array For Return
        foreach ($ComparedFiles as $Filename => $Extension) {
            $Files[] = $Filename . '.' . $Extension;
        }

        return $Files;
    }


    /**
     * @param $File
     * @return array
     */
    private function getFileInfos($File)
    {

        $FileInfo = array();

        // Explode Array By Points -> FileExtension Should Be Last
        $FileArray = explode('.', $File);

        // Reverse Array
        // -> FileExtension Is Now First Element
        // -> FileBasename Is Now Second Part
        $FileArray = array_reverse($FileArray);

        $FileInfo['Extension'] = $FileArray[0];
        $FileInfo['Filename'] = $FileArray[1];

        // Add Basename
        $FileInfo['Basename'] = pathinfo($File, PATHINFO_BASENAME);

        // Add Dirname
        $FileInfo['Dirname'] = pathinfo($File, PATHINFO_DIRNAME) . '/';

        return $FileInfo;
    }


    /**
     * @param        $File
     * @param string $LanguageKey
     * @return array|bool
     */
    private function getSystemLanguageFileXliff($File, $LanguageKey = 'default')
    {

        if (is_file($File)) {

            // Surpress xml errors
            libxml_use_internal_errors(true);

            // Load Xls Object
            $xml = simplexml_load_file($File, 'SimpleXMLElement', \LIBXML_NOWARNING);

            // Clear xml errors and activate errors again
            libxml_use_internal_errors(false);

            // Format Xls Object
            return self::formatSimpleXmlObject_XLS($xml, $LanguageKey);

        }

        return false;

    }


    /**
     * @param $File
     * @return array|bool
     */
    private function getSystemLanguageFileXml($File)
    {

        if (is_file($File)) {

            // Surpress xml errors
            libxml_use_internal_errors(true);

            // Load Xml Object
            $xml = simplexml_load_file($File, 'SimpleXMLElement', \LIBXML_NOWARNING);

            // Clear xml errors and activate errors again
            libxml_use_internal_errors(false);

            // Format Xml Object
            return self::formatSimpleXmlObject_XML($xml);

        }

        return false;
    }


    /**
     * Function 'doParsingFromRoot' from Class 't3lib_l10n_parser_Xliff'
     *
     * @param \SimpleXMLElement $simpleXmlObject
     * @param                   $LanguageKey
     * @return array
     */
    private function formatSimpleXmlObject_XLS(\SimpleXMLElement $simpleXmlObject, $LanguageKey)
    {

        $parsedData = array();
        /** @var \SimpleXMLElement $bodyOfFileTag */
        $bodyOfFileTag = $simpleXmlObject->file->body;

        foreach ($bodyOfFileTag->children() as $translationElement) {

            $elementName = $translationElement->getName();

            if ($elementName === 'trans-unit' && !isset($translationElement['restype'])) {
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
            } elseif ($elementName === 'group' && isset($translationElement['restype']) && (string)$translationElement['restype'] === 'x-gettext-plurals') {
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
     * @param $simpleXmlObject
     * @return array|bool
     */
    private function formatSimpleXmlObject_XML($simpleXmlObject)
    {

        if ($simpleXmlObject) {

            $xmlArray = array();

            // Meta Array
            if (is_array($simpleXmlObject->meta) || is_object($simpleXmlObject->meta)) {

                $xmlArray['meta'] = array();

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

                $xmlArray['data'] = array();

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

        return false;

    }


    /**
     * @param $attributesObject
     * @return string
     */
    private function getSimpleXmlObjectAttributesIndex($attributesObject)
    {

        // Get Attributes
        if (is_array($attributesObject) || is_object($attributesObject)) {

            $attributes = array();

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
     * @param  $String
     * @return string
     */
    private function getCleanedString($String)
    {

        if ($String) {

            $String = htmlentities($String);

        }

        return $String;

    }


    /**
     * @param  $ExtensionPath
     * @param  $ExtensionKey
     * @return string
     */
    private function getExtensionIcon($ExtensionPath, $ExtensionKey)
    {

        $ExtensionIcon = '';

        if ($ExtensionPath && $ExtensionKey) {

            if (file_exists(ExtensionManagementUtility::extPath($ExtensionKey) . 'ext_icon.gif')) {
                $ExtensionIcon = ExtensionManagementUtility::extRelPath($ExtensionKey) . 'ext_icon.gif';
            } else {
                $ExtensionIcon = ExtensionManagementUtility::extRelPath('snowbabel') . 'Resources/Public/Images/Miscellaneous/ext_icon.gif';
            }

        }

        return $ExtensionIcon;

    }


    /**
     * @param  $ExtensionKey
     * @return bool
     */
    private function isExtensionLoaded($ExtensionKey)
    {

        if (isset($ExtensionKey)) {
            $InstalledExtensions = $this->LoadedExtensions;

            $Check = array_key_exists($ExtensionKey, $InstalledExtensions);

            if ($Check) {
                return true;
            } else {
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
    private function deleteSystemCache($FilePath, $Language)
    {
        // Delete Cached Language File
        $cacheFileName = self::getCacheFileName($FilePath, $Language);
        GeneralUtility::unlink_tempfile($cacheFileName);

        // Delete 'default'
        if ($Language != 'default') {
            $cacheFileNameDefault = self::getCacheFileName($FilePath);
            GeneralUtility::unlink_tempfile($cacheFileNameDefault);
        }
    }


    /**
     * @param        $FilePath
     * @param string $Language
     * @return string
     */
    private function getCacheFileName($FilePath, $Language = 'default')
    {

        $hashSource = substr($FilePath, strlen(PATH_site)) . '|' . date('d-m-Y H:i:s', filemtime($FilePath)) . '|version=2.3';
        $hash = '_' . GeneralUtility::shortMD5($hashSource);
        $tempPath = PATH_site . 'typo3temp/llxml/';
        $fileExtension = substr(basename($FilePath), 10, 15);

        return $tempPath . $fileExtension . $hash . '.' . $Language . '.' . 'utf-8' . '.cache';
    }

}