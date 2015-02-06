<?php
namespace Snowflake\Snowbabel\Domain\Repository;

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
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;


/**
 * Class ExtensionRepository
 *
 * @package Snowflake\Snowbabel\Domain\Repository
 */
class ExtensionRepository {


	/**
	 * Object manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;


	/**
	 * Package manager
	 *
	 * @var \TYPO3\CMS\Core\Package\PackageManager
	 * @inject
	 */
	protected $packageManager;


	/**
	 * Extension factory
	 *
	 * @var \Snowflake\Snowbabel\Domain\Service\ExtensionFactory
	 * @inject
	 */
	protected $extensionFactory;


	/**
	 * Returns all active and non active extensions
	 *
	 * @return Extension[]
	 */
	public function findAll() {

		$extensions = array ();

		foreach ($this->packageManager->getAvailablePackages() as $package) {

			// Only TYPO3 related packages could be handled by the extension manager
			// Composer packages from "Packages" folder will be instantiate as \TYPO3\Flow\Package\Package
			if (!($package instanceof PackageInterface)) {
				continue;
			}

			/** @var Extension $extensions */
			$extensions[] = $this->extensionFactory->getExtensionByPackage($package);

		}

		return $extensions;
	}


	/**
	 * Returns all active & non active extensions as array
	 *
	 * @param array $include
	 * @return array
	 */
	public function findAllAsArray($include = array ()) {

		$extensions = array ();

		foreach ($this->findAll() as $extension) {
			// Collect extension meta
			$extensions[] = $extension->toArray($include);
		}

		return $extensions;
	}


	/**
	 *
	 */
	public function findSelected() {

	}


	/**
	 *
	 */
	public function findSelectedAsArray() {

	}


	/**
	 * Returns extension by key
	 *
	 * @param $key string
	 * @return Extension
	 */
	public function findByKey($key) {

		return $this->extensionFactory->getExtensionByKey($key);

	}


	/**
	 * Updates extension (extension settings only, since everything else is 'read only')
	 *
	 * @param Extension $extension
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
	 */
	public function update(Extension $extension) {

		/** @var ExtensionSetting $extensionSettings */
		$extensionSettingRepository = $this->objectManager->get('Snowflake\\Snowbabel\\Domain\\Repository\\ExtensionSettingRepository');

		// Try to find assigned settings from database
		$extensionSettings = $extensionSettingRepository->findByKey($extension->getKey());

		// Set values of extension settings
		$extensionSettings->setSelected($extension->isSelected());

		// Update model
		$extensionSettingRepository->update($extensionSettings);

		/** @var PersistenceManager $persistenceManager */
		$persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');

		// We have to persist manually
		$persistenceManager->persistAll();

	}

}