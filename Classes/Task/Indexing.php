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
	private $confObj;


	/**
	 * @var Translations
	 */
	private $systemTranslation;


	/**
	 * @var Database
	 */
	private $database;


	/**
	 * @var number
	 */
	private $currentTableId;


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
		$this->currentTableId = $this->database->getCurrentTableId() ? 0 : 1;

		// Indexing Extensions
		$this->indexingExtensions();

		// Indexing Files
		$this->indexingFiles();

		// Indexing Labels
		$this->indexingLabels();

		// Indexing Translations
		$this->indexingTranslations();

		// Switch CurrentTableId
		$this->database->setCurrentTableId($this->currentTableId);

		// Add Scheduler Check To Localconf & Mark Configuration Changes As 'OK'
		$this->confObj->setSchedulerCheckAndChangedConfiguration();

		return TRUE;
	}


	/**
	 * @return void
	 */
	private function indexingExtensions() {

		// Get Extensions From Typo3
		$extensions = $this->systemTranslation->getExtensions();

		// Write Extensions To Database
		$this->database->setExtensions($extensions, $this->currentTableId);

	}


	/**
	 * @return void
	 */
	private function indexingFiles() {

		// Get Extensions From Database
		$extensions = $this->database->getExtensions($this->currentTableId);

		// Get Files From Typo3
		$files = $this->systemTranslation->getFiles($extensions);

		// Write Extensions To Database
		$this->database->setFiles($files, $this->currentTableId);

	}


	/**
	 * @return void
	 */
	private function indexingLabels() {

		// Get Files From Database
		$files = $this->database->getFiles($this->currentTableId);

		// Get Labels From Typo
		$labels = $this->systemTranslation->getLabels($files);

		// Write Labels To Database
		$this->database->setLabels($labels, $this->currentTableId);

	}


	/**
	 * @return void
	 */
	private function indexingTranslations() {

		// Important! Needed For Caching in getLabels
		$conf['OrderBy'] = 'FileId';

		// Get Labels From Database
		$labels = $this->database->getLabels($this->currentTableId, $conf);

		// Get Translations From Typo
		$translations = $this->systemTranslation->getTranslations($labels);

		// Write Translations To Database
		$this->database->setTranslations($translations, $this->currentTableId);

	}


	/**
	 * @return void
	 */
	private function initConfiguration() {

		if (!is_object($this->confObj) && !($this->confObj instanceof Configuration)) {
			$this->confObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Service\\Configuration', array ());

			$this->database = $this->confObj->getDatabase();
		}

	}


	/**
	 * @return void
	 */
	private function initSystemTranslations() {
		if (!is_object($this->systemTranslation) && !($this->systemTranslation instanceof Translations)) {
			$this->systemTranslation = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Service\\Translations');
			$this->systemTranslation->init($this->confObj);
		}
	}

}