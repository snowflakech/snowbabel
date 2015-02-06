<?php
namespace Snowflake\Snowbabel\Domain\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Daniel Alder <support@snowflake.ch>
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
 *  GNU General Public License for more details.d
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Snowflake\Snowbabel\Domain\Model\Extension;
use Snowflake\Snowbabel\Domain\Model\ExtensionSetting;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\Flow\Package\PackageInterface;


/**
 * Class ExtensionFactory
 *
 * @package Snowflake\Snowbabel\Domain\Service
 */
class ExtensionFactory implements SingletonInterface {


	/**
	 *
	 */
	const EXT_EM_CONF_FILE = '/ext_emconf.php';


	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;


	/**
	 * @var \TYPO3\CMS\Core\Package\PackageManager
	 * @inject
	 */
	protected $packageManger;


	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;


	/**
	 * @var \Snowflake\Snowbabel\Domain\Repository\ExtensionSettingRepository
	 * @inject
	 */
	protected $extensionSettingRepository;


	/**
	 * Returns an extension object by extension key
	 *
	 * @param string $key
	 * @return Extension
	 */
	public function getExtensionByKey($key) {

		// Get package from package manager
		$package = $this->packageManger->getPackage($key);

		return $this->getExtensionByPackage($package);
	}


	/**
	 * Returns an extension object by extension package
	 *
	 * @param PackageInterface $package
	 * @return Extension
	 */
	public function getExtensionByPackage(PackageInterface $package) {

		/** @var Extension $extension */
		$extension = $this->objectManager->get('Snowflake\\Snowbabel\\Domain\\Model\\Extension');

		// Package values
		$extension = $this->initValuesFromPackage($extension, $package);

		// Package manager values
		$extension = $this->initValuesFromPackageManager($extension);

		// EmConf values
		$extension = $this->initValuesFromEmConf($extension);

		// ExtensionSettings values
		$extension = $this->initValuesFromExtensionSettings($extension);

		// Returns extension
		return $extension;
	}


	/**
	 * Adds values from package to model
	 *
	 * @param Extension $extension
	 * @param PackageInterface $package
	 * @return Extension
	 */
	protected function initValuesFromPackage(Extension $extension, PackageInterface $package) {

		// Sets available package properties
		$extension->setKey($package->getPackageKey());

		// Sets icon
		$extension->setIcon(ExtensionManagementUtility::getExtensionIcon($package->getPackagePath()));

		// Full path to package
		$extension->setPath($package->getPackagePath());

		// Relative site path
		$extension->setSiteRelativePath(str_replace(PATH_site, '', $package->getPackagePath()));

		// Sets type
		$extension->setType($this->getInstallTypeForPackage($package));

		// Returns extension
		return $extension;
	}


	/**
	 * Adds values from package manager to model
	 *
	 * @param Extension $extension
	 * @return Extension
	 */
	protected function initValuesFromPackageManager(Extension $extension) {

		// Is it installed or not
		$extension->setActive($this->packageManger->isPackageActive($extension->getKey()));

		// Returns extension
		return $extension;
	}


	/**
	 * Adds values from emConf to model
	 *
	 * @param Extension $extension
	 * @return Extension
	 */
	protected function initValuesFromEmConf(Extension $extension) {

		// Gets ext_emconf.php manually since $package->getPackageMetaData() doesn't provide all data
		// Beside some of the information (title) are faulty
		$extEmConf = $this->getExtensionEmconf($extension);

		// Sets category
		$extension->setCategory($extEmConf['category']);

		// Sets description from emconf
		$extension->setDescription($extEmConf['description']);

		// Sets title
		$extension->setTitle($extEmConf['title']);

		// Returns extension
		return $extension;
	}


	/**
	 * Adds values from extension settings to model
	 * This are additional values saved by snowbabel itself
	 *
	 * @param Extension $extension
	 * @return Extension
	 */
	protected function initValuesFromExtensionSettings(Extension $extension) {

		// Try to find assigned settings from database
		/** @var ExtensionSetting $extensionSettings */
		$extensionSettings = $this->extensionSettingRepository->findByKey($extension->getKey());

		// No settings available yet
		if ($extensionSettings === NULL) {
			$extensionSettings = $this->addExtensionSettings($extension);
		}

		// Sets if package was selected to show up for editing
		$extension->setSelected($extensionSettings->getSelected());

		return $extension;
	}


	/**
	 * Returns extension position in filesystem (System,Global,Local)
	 *
	 * @param PackageInterface $package
	 * @return string
	 */
	protected function getInstallTypeForPackage(PackageInterface $package) {

		$installTypeForPackage = '';

		foreach (self::getInstallPaths() as $installType => $installPath) {
			if (GeneralUtility::isFirstPartOfStr($package->getPackagePath(), $installPath)) {
				$installTypeForPackage = $installType;
			}
		}

		return $installTypeForPackage;
	}


	/**
	 * Returns array of emConf
	 *
	 * @param Extension $extension
	 * @return null|array
	 */
	protected function getExtensionEmconf(Extension $extension) {

		$EM_CONF = NULL;
		$_EXTKEY = $extension->getKey();

		$extEmConf = NULL;
		$path = $extension->getPath() . self::EXT_EM_CONF_FILE;

		if (@file_exists($path)) {
			include $path;
			if (is_array($EM_CONF[$_EXTKEY])) {
				$extEmConf = $EM_CONF[$_EXTKEY];
			}
		}

		return $extEmConf;
	}


	/**
	 * Adds a new instance of additional extension settings
	 *
	 * @param Extension $extension
	 * @return ExtensionSetting
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
	 */
	protected function addExtensionSettings(Extension $extension) {

		// Create new instance if not available already
		$extensionSettings = $this->objectManager->get('Snowflake\\Snowbabel\\Domain\\Model\\ExtensionSetting');

		// Set default values
		$extensionSettings->setExtensionKey($extension->getKey());
		$extensionSettings->setSelected(FALSE);

		// Add new model via repository
		$this->extensionSettingRepository->add($extensionSettings);

		// We have to persist the model manually
		/** @var PersistenceManager $persistenceManager */
		$persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$persistenceManager->persistAll();

		return $extensionSettings;
	}


	/**
	 * Returns paths of system, global & local installation locations
	 *
	 * @return array
	 */
	protected static function getInstallPaths() {

		return array (
			'System' => PATH_typo3 . 'sysext/',
			'Global' => PATH_typo3 . 'ext/',
			'Local' => PATH_typo3conf . 'ext/'
		);
	}

}