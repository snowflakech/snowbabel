<?php
namespace Snowflake\Snowbabel\Task;

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

use Snowflake\Snowbabel\Service\Configuration;
use Snowflake\Snowbabel\Service\Database;
use Snowflake\Snowbabel\Service\Translations;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class Indexing
 *
 * @package Snowflake\Snowbabel\Task
 */
class Indexing extends AbstractTask {


	/**
	 * @var Configuration
	 */
    protected $confObj;


	/**
	 * @var Translations
	 */
    protected $SystemTranslation;


	/**
	 * @var Database
	 */
    protected $Db;


	/**
	 * @var number
	 */
    protected $CurrentTableId;


	/**
	 * @return void
	 */
	private function init() {

		// Init Configuration
		$this->initConfiguration();

		// Init System Translations
		$this->initSystemTranslations();

	}


	/**
	 * @return bool
	 */
	public function execute() {

		$this->init();

		// Get Current TableId & Negate
		$this->CurrentTableId = $this->Db->getCurrentTableId() ? 0 : 1;

		// Indexing Extensions
		$this->indexingExtensions();

		// Indexing Files
		$this->indexingFiles();

		// Indexing Labels
		$this->indexingLabels();

		// Indexing Translations
		$this->indexingTranslations();

		// Switch CurrentTableId
		$this->Db->setCurrentTableId($this->CurrentTableId);

		// Add Scheduler Check To Localconf & Mark Configuration Changes As 'OK'
		$this->confObj->setSchedulerCheckAndChangedConfiguration();

		return true;
	}


	/**
	 * @return void
	 */
	private function indexingExtensions() {

		// Get Extensions From Typo3
		$Extensions = $this->SystemTranslation->getExtensions();

		// Write Extensions To Database
		$this->Db->setExtensions($Extensions, $this->CurrentTableId);

	}


	/**
	 * @return void
	 */
	private function indexingFiles() {

		// Get Extensions From Database
		$Extensions = $this->Db->getExtensions($this->CurrentTableId);

		// Get Files From Typo3
		$Files = $this->SystemTranslation->getFiles($Extensions);

		// Write Extensions To Database
		$this->Db->setFiles($Files, $this->CurrentTableId);

	}


	/**
	 * @return void
	 */
	private function indexingLabels() {

		// Get Files From Database
		$Files = $this->Db->getFiles($this->CurrentTableId);

		// Get Labels From Typo
		$Labels = $this->SystemTranslation->getLabels($Files);

		// Write Labels To Database
		$this->Db->setLabels($Labels, $this->CurrentTableId);

	}


	/**
	 * @return void
	 */
	private function indexingTranslations() {

		// Important! Needed For Caching in getLabels
		$Conf['OrderBy'] = 'FileId';
       
		// Get Labels From Database
		$Labels = $this->Db->getLabels($this->CurrentTableId, $Conf);
       
		// Get Translations From Typo
		$Translations = $this->SystemTranslation->getTranslations($Labels);
       
		// Write Translations To Database
		$this->Db->setTranslations($Translations, $this->CurrentTableId);

	}


	/**
	 * @return void
	 */
	private function initConfiguration() {

		if(!is_object($this->confObj) && !($this->confObj instanceof Configuration)) {
			$this->confObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Service\\Configuration', array());

			$this->Db = $this->confObj->getDb();
		}

	}


	/**
	 * @return void
	 */
	private function initSystemTranslations() {
		if(!is_object($this->SystemTranslation) && !($this->SystemTranslation instanceof Translations)) {
			$this->SystemTranslation = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Service\\Translations');
			$this->SystemTranslation->init($this->confObj);
		}
	}

}