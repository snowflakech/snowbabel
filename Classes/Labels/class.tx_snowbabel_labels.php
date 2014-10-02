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
class tx_snowbabel_Labels {

	/**
	 * @var tx_snowbabel_Configuration
	 */
	private $confObj;

	/**
	 * @var tx_snowbabel_Languages
	 */
	private $langObj;

	/**
	 * @var tx_snowbabel_Db
	 */
	private $Db;

	/**
	 * @var tx_snowbabel_system_translations
	 */
	private $SystemTranslation;

	/**
	 * @var
	 */
	private $debug;

	/**
	 * @var
	 */
	private $CurrentTableId;

	/**
	 * @var array
	 */
	private $Languages;

	/**
	 *
	 */
	private $ColumnsConfiguration;

	/**
	 *
	 */
	private $ShowColumnLabel;

	/**
	 *
	 */
	private $ShowColumnDefault;

	/**
	 *
	 */
	private $IsAdmin;

	/**
	 *
	 */
	private $PermittedExtensions;

	/**
	 *
	 */
	private $Labels;

	/**
	 *
	 */
	private $SearchModus;

	/**
	 *
	 */
	private $SearchString;

	/**
	 *
	 */
	private $ExtensionId;

	/**
	 *
	 */
	private $ListViewStart;

	/**
	 *
	 */
	private	$ListViewLimit;

	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;
		$this->Db = $this->confObj->getDb();
		$this->debug = $confObj->debug;

			// Get Current TableId
		$this->CurrentTableId = $this->Db->getCurrentTableId();

			// get User params
		$this->ColumnsConfiguration = $this->confObj->getUserConfigurationColumns();

		$this->ShowColumnLabel = $this->ColumnsConfiguration['ShowColumnLabel'];
		$this->ShowColumnDefault = $this->ColumnsConfiguration['ShowColumnDefault'];

		$this->IsAdmin = $this->confObj->getUserConfigurationIsAdmin();
		$this->PermittedExtensions = $this->confObj->getUserConfiguration('PermittedExtensions');

			// extjs params
		$this->SearchString = $this->confObj->getExtjsConfiguration('SearchString');
		$this->ExtensionId = $this->confObj->getExtjsConfiguration('ExtensionId');

		$this->Dir = $this->confObj->getExtjsConfiguration('dir');
		$this->Sort = $this->confObj->getExtjsConfiguration('sort');

		$this->ListViewStart = $this->confObj->getExtjsConfigurationListViewStart();
		$this->ListViewLimit = $this->confObj->getExtjsConfigurationListViewLimit();

		$this->TranslationId = $this->confObj->getExtjsConfiguration('TranslationId');
		$this->TranslationValue = $this->confObj->getExtjsConfiguration('TranslationValue');

			// get language object
		$this->getLanguageObject();

			// get available languages
		$this->Languages = $this->langObj->getLanguages();

	}

	/**
	 * @return void
	 */
	public function setMetaData() {

			// Set metadata to configure grid properties
		$MetaData['metaData']['idProperty'] = 'RecordId';
		$MetaData['metaData']['root'] = 'LabelRows';

			// Set field for totalcounts -> paging
		$MetaData['metaData']['totalProperty'] = 'ResultCount';

			// Set standard sorting
		$MetaData['metaData']['sortInfo']['field'] = $this->Sort ? $this->Sort : 'LabelName';
		$MetaData['metaData']['sortInfo']['direction'] = $this->Dir ? $this->Dir : 'ASC';

			// Set fields
		$MetaData['metaData']['fields'] = array();
		array_push($MetaData['metaData']['fields'], 'LabelId');
		array_push($MetaData['metaData']['fields'], 'LabelName');
		array_push($MetaData['metaData']['fields'], 'LabelDefault');

			// Add fields for selected languages
		if(is_array($this->Languages)) {
			foreach($this->Languages as $Language) {

				if($Language['LanguageSelected']) {

					array_push($MetaData['metaData']['fields'], 'TranslationId_' . $Language['LanguageKey']);
					array_push($MetaData['metaData']['fields'], 'TranslationValue_' . $Language['LanguageKey']);

				}

			}
		}

			// Set columns
		$MetaData['columns'] = array(

			array (
					'header' => 'LabelId',
					'dataIndex' => 'LabelId',
					'hidden' => true
			),

			array (
					'header' => $this->confObj->getLL('translation_listview_GridHeaderLabel'),
					'dataIndex' => 'LabelName',
					'sortable' => true,
					'hidden' => !$this->ShowColumnLabel
			),

			array (
					'header' => $this->confObj->getLL('translation_listview_GridHeaderDefault'),
					'dataIndex' => 'LabelDefault',
					'sortable' => true,
					'hidden' => !$this->ShowColumnDefault
			)

		);

			// Add Columns For Selected Languages
		if(is_array($this->Languages)) {
			foreach($this->Languages as $Language) {

				if($Language['LanguageSelected']) {

						// Translation Id
					$addColumn = array (
						'header' => 'TranslationId_' . $Language['LanguageKey'],
						'dataIndex' => 'TranslationId_' . $Language['LanguageKey'],
						'hidden' => true
					);

					array_push($MetaData['columns'], $addColumn);

						// Translation Value
					$addColumn = array (
							'header' => $Language['LanguageName'],
							'dataIndex' => 'TranslationValue_' . $Language['LanguageKey'],
							'sortable' => true,
							'editor' => array (
								'xtype' => 'textarea',
								'multiline' => true,
								'grow' => true,
								'growMin' => 30,
								'growMax' => 200
							),
							'renderer' => 'CellPreRenderer'
					);

					array_push($MetaData['columns'], $addColumn);

				}
			}
		}

			// Add MetaData
		$this->Labels = $MetaData;

			// Add Data Array
		$this->Labels['LabelRows']   = array();

	}

    /**
	 * @return
	 */
    public function getSearchGlobal() {

		$this->SearchModus = 'global';

		return $this->getLabels();

    }

    /**
	 * @return
	 */
    public function getSearchExtension() {

		$this->SearchModus = 'extension';

		return $this->getLabels();

    }

	/**
	 * @return
	 */
	public function getLabels() {

		if(!$this->IsAdmin && $this->PermittedExtensions == '') {
			$this->Labels['LabelRows'] = NULL;
		}
		else {
			$Languages = array();

			if(is_array($this->Languages)) {
				foreach($this->Languages as $Language) {
					if($Language['LanguageSelected']) {
						array_push($Languages, $Language['LanguageKey']);
					}
				}
			}

			$Conf = array(
				'ExtensionId' => $this->SearchModus == 'global' ? '' : $this->ExtensionId,
				'Sort' => $this->Sort ? $this->Sort : 'LabelName',
				'Dir' => $this->Dir ? $this->Dir : 'ASC',
				'Limit' => $this->ListViewStart . ',' . $this->ListViewLimit,
				'Search' => !$this->SearchString ? '' : $this->SearchString,
				'Languages' => $Languages,
				'Debug' => '0',
			);

			$Translations = $this->Db->getTranslations($this->CurrentTableId, $Conf, $Languages);

			if($Translations) {

					// Because Of Performance Do Not Select Translation Selects, Only Need Labels
				if(!$this->SearchString) {
					$Conf['Fields'] = 'tx_snowbabel_indexing_labels_' . $this->CurrentTableId . '.uid';
					$this->Labels['ResultCount'] = $this->Db->getLabels($this->CurrentTableId, $Conf, true);
				}
				else {
					$this->Labels['ResultCount'] = $this->Db->getTranslations($this->CurrentTableId, $Conf, $Languages, true);
				}


			}

				// Add Result To Array
			$this->Labels['LabelRows'] = $Translations;
		}

		return $this->Labels;
	}

	/**
	 * @return void
	 */
	public function updateTranslation() {

		if($this->TranslationId) {

				// DATABASE

			$this->Db->setTranslation($this->TranslationId, $this->TranslationValue, $this->CurrentTableId);

				// SYSTEM

			$Conf = array(
				'TranslationId' => $this->TranslationId
			);

				// Get Full Translation Data From DB
			$Translation = $this->Db->getTranslation($this->CurrentTableId, $Conf);

				// Init SystemTranslations
			$this->initSystemTranslations();

				// Update SystemTranslations With DB Values
			$this->SystemTranslation->updateTranslation($Translation[0]);

		}

	}

	/**
	 *
	 */
	private function getLanguageObject() {
		if (!is_object($this->langObj) && !($this->langObj instanceof tx_snowbabel_languages)) {
			$this->langObj = t3lib_div::makeInstance('tx_snowbabel_languages', $this->confObj);
		}
	}

	/**
	 * @return void
	 */
	private function initSystemTranslations() {
		if (!is_object($this->SystemTranslation) && !($this->SystemTranslation instanceof tx_snowbabel_system_translations)) {
			$this->SystemTranslation = t3lib_div::makeInstance('tx_snowbabel_system_translations');
			$this->SystemTranslation->init($this->confObj);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Configuration/class.tx_snowbabel_labels.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Configuration/class.tx_snowbabel_labels.php']);
}

?>
