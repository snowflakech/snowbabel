<?php
namespace Snowflake\Snowbabel\Record;

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

/**
 * Class Labels
 *
 * @package Snowflake\Snowbabel\Record
 */
class Labels {


	/**
	 * @var Configuration
	 */
	private $confObj;


	/**
	 * @var Languages
	 */
	private $langObj;


	/**
	 * @var Database
	 */
	private $database;


	/**
	 * @var Translations
	 */
	private $systemTranslation;


	/**
	 * @var
	 */
	private $debug;


	/**
	 * @var
	 */
	private $currentTableId;


	/**
	 * @var array
	 */
	private $languages;


	/**
	 *
	 */
	private $columnsConfiguration;


	/**
	 *
	 */
	private $showColumnLabel;


	/**
	 *
	 */
	private $showColumnDefault;


	/**
	 *
	 */
	private $isAdmin;


	/**
	 *
	 */
	private $permittedExtensions;


	/**
	 *
	 */
	private $labels;


	/**
	 *
	 */
	private $searchMode;


	/**
	 *
	 */
	private $searchString;


	/**
	 *
	 */
	private $extensionId;


	/**
	 *
	 */
	private $listViewStart;


	/**
	 *
	 */
	private $listViewLimit;


	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;
		$this->database = $this->confObj->getDatabase();
		$this->debug = $confObj->debug;

		// Get Current TableId
		$this->currentTableId = $this->database->getCurrentTableId();

		// get User params
		$this->columnsConfiguration = $this->confObj->getUserConfigurationColumns();

		$this->showColumnLabel = $this->columnsConfiguration['ShowColumnLabel'];
		$this->showColumnDefault = $this->columnsConfiguration['ShowColumnDefault'];

		$this->isAdmin = $this->confObj->getUserConfigurationIsAdmin();
		$this->permittedExtensions = $this->confObj->getUserConfiguration('PermittedExtensions');

		// extjs params
		$this->searchString = $this->confObj->getExtjsConfiguration('SearchString');
		$this->extensionId = $this->confObj->getExtjsConfiguration('ExtensionId');

		$this->Dir = $this->confObj->getExtjsConfiguration('dir');
		$this->Sort = $this->confObj->getExtjsConfiguration('sort');

		$this->listViewStart = $this->confObj->getExtjsConfigurationListViewStart();
		$this->listViewLimit = $this->confObj->getExtjsConfigurationListViewLimit();

		$this->translationId = $this->confObj->getExtjsConfiguration('TranslationId');
		$this->translationValue = $this->confObj->getExtjsConfiguration('TranslationValue');

		// get language object
		$this->getLanguageObject();

		// get available languages
		$this->languages = $this->langObj->getLanguages();

	}


	/**
	 * @return void
	 */
	public function setMetaData() {

		// Set metadata to configure grid properties
		$metaData['metaData']['idProperty'] = 'RecordId';
		$metaData['metaData']['root'] = 'LabelRows';

		// Set field for totalcounts -> paging
		$metaData['metaData']['totalProperty'] = 'ResultCount';

		// Set standard sorting
		$metaData['metaData']['sortInfo']['field'] = $this->Sort ? $this->Sort : 'LabelName';
		$metaData['metaData']['sortInfo']['direction'] = $this->Dir ? $this->Dir : 'ASC';

		// Set fields
		$metaData['metaData']['fields'] = array ();
		array_push($metaData['metaData']['fields'], 'LabelId');
		array_push($metaData['metaData']['fields'], 'LabelName');
		array_push($metaData['metaData']['fields'], 'LabelDefault');

		// Add fields for selected languages
		if (is_array($this->languages)) {
			foreach ($this->languages as $language) {

				if ($language['LanguageSelected']) {

					array_push($metaData['metaData']['fields'], 'TranslationId_' . $language['LanguageKey']);
					array_push($metaData['metaData']['fields'], 'TranslationValue_' . $language['LanguageKey']);

				}

			}
		}

		// Set columns
		$metaData['columns'] = array (

			array (
				'header' => 'LabelId',
				'dataIndex' => 'LabelId',
				'hidden' => TRUE
			),

			array (
				'header' => $this->confObj->getLocallang('translation_listview_GridHeaderLabel'),
				'dataIndex' => 'LabelName',
				'sortable' => TRUE,
				'hidden' => !$this->showColumnLabel
			),

			array (
				'header' => $this->confObj->getLocallang('translation_listview_GridHeaderDefault'),
				'dataIndex' => 'LabelDefault',
				'sortable' => TRUE,
				'hidden' => !$this->showColumnDefault
			)

		);

		// Add Columns For Selected Languages
		if (is_array($this->languages)) {
			foreach ($this->languages as $language) {

				if ($language['LanguageSelected']) {

					// Translation Id
					$addColumn = array (
						'header' => 'TranslationId_' . $language['LanguageKey'],
						'dataIndex' => 'TranslationId_' . $language['LanguageKey'],
						'hidden' => TRUE
					);

					array_push($metaData['columns'], $addColumn);

					// Translation Value
					$addColumn = array (
						'header' => $language['LanguageName'],
						'dataIndex' => 'TranslationValue_' . $language['LanguageKey'],
						'sortable' => TRUE,
						'editor' => array (
							'xtype' => 'textarea',
							'multiline' => TRUE,
							'grow' => TRUE,
							'growMin' => 30,
							'growMax' => 200
						),
						'renderer' => 'CellPreRenderer'
					);

					array_push($metaData['columns'], $addColumn);

				}
			}
		}

		// Add MetaData
		$this->labels = $metaData;

		// Add Data Array
		$this->labels['LabelRows'] = array ();

	}


	/**
	 * @return
	 */
	public function getSearchGlobal() {

		$this->searchMode = 'global';

		return $this->getLabels();

	}


	/**
	 * @return
	 */
	public function getSearchExtension() {

		$this->searchMode = 'extension';

		return $this->getLabels();

	}


	/**
	 * @return
	 */
	public function getLabels() {

		if (!$this->isAdmin && $this->permittedExtensions == '') {
			$this->labels['LabelRows'] = NULL;
		} else {
			$languages = array ();

			if (is_array($this->languages)) {
				foreach ($this->languages as $language) {
					if ($language['LanguageSelected']) {
						array_push($languages, $language['LanguageKey']);
					}
				}
			}

			$conf = array (
				'ExtensionId' => $this->searchMode == 'global' ? '' : $this->extensionId,
				'Sort' => $this->Sort ? $this->Sort : 'LabelName',
				'Dir' => $this->Dir ? $this->Dir : 'ASC',
				'Limit' => $this->listViewStart . ',' . $this->listViewLimit,
				'Search' => !$this->searchString ? '' : $this->searchString,
				'Languages' => $languages,
				'Debug' => '0',
			);

			$translations = $this->database->getTranslations($this->currentTableId, $conf, $languages);

			if ($translations) {

				// Because Of Performance Do Not Select Translation Selects, Only Need Labels
				if (!$this->searchString) {
					$conf['Fields'] = 'tx_snowbabel_indexing_labels_' . $this->currentTableId . '.uid';
					$this->labels['ResultCount'] = $this->database->getLabels($this->currentTableId, $conf, TRUE);
				} else {
					$this->labels['ResultCount'] = $this->database->getTranslations($this->currentTableId, $conf, $languages, TRUE);
				}

			}

			// Add Result To Array
			$this->labels['LabelRows'] = $translations;
		}

		return $this->labels;
	}


	/**
	 * @return void
	 */
	public function updateTranslation() {

		if ($this->translationId) {

			// DATABASE

			$this->database->setTranslation($this->translationId, $this->translationValue, $this->currentTableId);

			// SYSTEM

			$conf = array (
				'TranslationId' => $this->translationId
			);

			// Get Full Translation Data From DB
			$translation = $this->database->getTranslation($this->currentTableId, $conf);

			// Init SystemTranslations
			$this->initSystemTranslations();

			// Update SystemTranslations With DB Values
			$this->systemTranslation->updateTranslation($translation[0]);

		}

	}


	/**
	 *
	 */
	private function getLanguageObject() {
		if (!is_object($this->langObj) && !($this->langObj instanceof Languages)) {
			$this->langObj = GeneralUtility::makeInstance('Snowflake\\Snowbabel\\Record\\Languages', $this->confObj);
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