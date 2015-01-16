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

use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class Database
 *
 * @package Snowflake\Snowbabel\Service
 */
class Database {


	/**
	 * @var
	 */
	private $debug;


	/**
	 * @param  $debug
	 */
	public function __construct($debug) {
		$this->debug = $debug;
	}


	/**
	 * @param $localconfValue
	 * @param bool $showTranslatedLanguages
	 * @return array
	 */
	public function getAppConfAvailableLanguages($localconfValue, $showTranslatedLanguages = FALSE) {

		$languages = array ();
		$tempLanguages = explode(',', $localconfValue);

		if (is_array($tempLanguages)) {
			foreach ($tempLanguages as $tempLanguageId) {

				$language = $this->getStaticLanguages($tempLanguageId, $showTranslatedLanguages);

				if (!empty($language)) {
					array_push($languages, $language);
				}

			}
		}

		return $languages;
	}


	/**
	 * @param  $backendUserId
	 * @return null
	 */
	public function getUserConfSelectedLanguages($backendUserId) {

		// set configuration
		$name = 'SelectedLanguages';

		// get value
		return $this->getUserConf($name, $backendUserId);

	}


	/**
	 * @param  $backendUserId
	 * @return null
	 */
	public function getUserConfShowColumnLabel($backendUserId) {

		// set configuration
		$name = 'ShowColumnLabel';

		// get value
		return $this->getUserConf($name, $backendUserId);

	}


	/**
	 * @param  $backendUserId
	 * @return null
	 */
	public function getUserConfShowColumnDefault($backendUserId) {

		// set configuration
		$name = 'ShowColumnDefault';

		// get value
		return $this->getUserConf($name, $backendUserId);

	}


	/**
	 * @param  $name
	 * @param  $backendUserId
	 * @return null
	 */
	public function getUserConf($name, $backendUserId) {
		if (isset($name, $backendUserId)) {

			$select = $this->getDatabaseConnection()->exec_SELECTgetRows(
				$name,
				'tx_snowbabel_users',
				'deleted=0 AND be_users_uid=' . $backendUserId,
				'',
				'',
				'1'
			);

			// return value
			return $select[0][$name];

		} else {
			return NULL;
		}
	}


	/**
	 * @param  $backendUserId
	 * @return void
	 */
	public function getUserConfCheck($backendUserId) {

		if ($backendUserId > 0) {
			$select = $this->getDatabaseConnection()->exec_SELECTgetRows(
				'uid',
				'tx_snowbabel_users',
				'deleted=0 AND be_users_uid=' . $backendUserId,
				'',
				'',
				'1'
			);

			if (!$select) {

				// insert database row
				$this->insertUserConfCheck($backendUserId);
			}
		}

	}


	/**
	 * @param bool $languageId
	 * @param bool $showTranslatedLanguages
	 * @return array|null
	 */
	public function getStaticLanguages($languageId = FALSE, $showTranslatedLanguages = FALSE) {

		$whereClause = '';

		// search single language
		if (is_numeric($languageId)) {
			$whereClause = 'uid=' . $languageId;
		}

		// sort by english or local
		if (!$showTranslatedLanguages) {
			$orderBy = 'lg_name_en';
		} else {
			$orderBy = 'lg_name_local';
		}

		$select = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'*',
			'static_languages',
			$whereClause,
			'',
			$orderBy,
			''
		);

		if (!count($select)) {

			return NULL;

		} else {

			if (is_array($select)) {

				$languages = array ();

				foreach ($select as $key => $language) {

					$languages[$key]['LanguageId'] = $language['uid'];

					// check if languages should be displayed in english or local
					if (!$showTranslatedLanguages) {
						$languages[$key]['LanguageName'] = $language['lg_name_en'];
					} else {
						$languages[$key]['LanguageName'] = $language['lg_name_local'];
					}

					$languages[$key]['LanguageNameEn'] = $language['lg_name_en'];
					$languages[$key]['LanguageNameLocal'] = $language['lg_name_local'];
					$languages[$key]['LanguageKey'] = strtolower($language['lg_iso_2']);

					if ($languageId) {
						return $languages[$key];
					}
				}

				return $languages;
			} else {
				return NULL;
			}

		}

	}


	/**
	 * @param  $name
	 * @param  $value
	 * @param  $backendUserId
	 * @return void
	 */
	public function setUserConf($name, $value, $backendUserId) {
		$this->getDatabaseConnection()->exec_UPDATEquery(
			'tx_snowbabel_users',
			'deleted=0 AND be_users_uid=' . $backendUserId,
			array (
				'tstamp' => time(),
				$name => $value
			)
		);
	}


	/**
	 * @param  $backendUserId
	 * @return void
	 */
	public function insertUserConfCheck($backendUserId) {

		$this->getDatabaseConnection()->exec_INSERTquery(
			'tx_snowbabel_users',
			array (
				'tstamp' => time(),
				'crdate' => time(),
				'be_users_uid' => $backendUserId
			)
		);

	}

	/*********/
	/** API **/
	/*********/

	/**
	 * @return int
	 */
	public function getCurrentTableId() {

		$select = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'TableId',
			'tx_snowbabel_temp',
			'',
			'',
			'',
			''
		);

		if (count($select) > 0) {
			return $select[0]['TableId'];
		}

		$this->getDatabaseConnection()->exec_INSERTquery(
			'tx_snowbabel_temp',
			array ('TableId' => 0)
		);

		return 0;

	}


	/**
	 * @param  $tableId
	 * @return void
	 */
	public function setCurrentTableId($tableId) {

		// Update Field
		$this->getDatabaseConnection()->exec_UPDATEquery(
			'tx_snowbabel_temp',
			'',
			array (
				'TableId' => $tableId
			)
		);
	}


	/**
	 * @param $currentTableId
	 * @param array $conf
	 * @return null
	 */
	public function getExtensions($currentTableId, $conf = array ()) {

		$table = 'tx_snowbabel_indexing_extensions_' . $currentTableId;
		$fields = '*';
		$where = '';
		$orderBy = '';
		$groupBy = '';
		$limit = '';

		if (is_array($conf) && count($conf) > 0) {

			// FIELDS
			if ($conf['Fields']) {
				$fields = $conf['Fields'];
			}

			// WHERE
			$where = array (
				'OR' => array (),
				'AND' => array (),
			);

			if ($conf['Local']) {
				array_push($where['OR'], 'ExtensionLocation=\'Local\'');
			}
			if ($conf['System']) {
				array_push($where['OR'], 'ExtensionLocation=\'System\'');
			}
			if ($conf['Global']) {
				array_push($where['OR'], 'ExtensionLocation=\'Global\'');
			}

			if ($conf['OnlyLoaded']) {
				array_push($where['AND'], 'ExtensionLoaded=1');
			}

			if (!empty($conf['ApprovedExtensions'])) {

				$approvedExtensions = $this->prepareCommaSeparatedString($conf['ApprovedExtensions'], $table);
				if (!empty($approvedExtensions)) {
					array_push($where['AND'], 'ExtensionKey IN (' . $approvedExtensions . ')');
				}

			}

			if (!empty($conf['PermittedExtensions'])) {

				$permittedExtensions = $this->prepareCommaSeparatedString($conf['PermittedExtensions'], $table);
				if (!empty($permittedExtensions)) {
					array_push($where['AND'], 'ExtensionKey IN (' . $permittedExtensions . ')');
				}

			}

			$where = $this->where($where);

			// GROUP BY
			if ($conf['GroupBy']) {
				$groupBy = $conf['GroupBy'];
			}

			// ORDER BY
			if ($conf['OrderBy']) {
				$orderBy = $conf['OrderBy'];
			}

			// LIMIT
			if ($conf['Limit']) {
				$orderBy = $conf['Limit'];
			}
		}

		return $this->select($fields, $table, $where, $groupBy, $orderBy, $limit, $conf['Debug']);

	}


	/**
	 * @param  $extensions
	 * @param  $currentTableId
	 * @return void
	 */
	public function setExtensions($extensions, $currentTableId) {

		// Define Table
		$table = 'tx_snowbabel_indexing_extensions_' . $currentTableId;

		// Empty Table
		$this->truncate($table);

		// Add Records To Table
		$this->insert($table, $extensions);

	}


	/**
	 * @param $currentTableId
	 * @param bool $conf
	 * @return null
	 */
	public function getFiles($currentTableId, $conf = FALSE) {

		$table1 = 'tx_snowbabel_indexing_extensions_' . $currentTableId;
		$table2 = 'tx_snowbabel_indexing_files_' . $currentTableId;
		$table = $table1 . ',' . $table2;
		$fields = $table1 . '.ExtensionKey,' .
			$table1 . '.ExtensionTitle,' .
			$table1 . '.ExtensionDescription,' .
			$table1 . '.ExtensionCategory,' .
			$table1 . '.ExtensionIcon,' .
			$table1 . '.ExtensionLocation,' .
			$table1 . '.ExtensionPath,' .
			$table1 . '.ExtensionLoaded,' .
			$table2 . '.uid AS FileId,' . $table2 . '.ExtensionId,' . $table2 . '.FileKey';
		$where = array (
			'OR' => array (),
			'AND' => array (),
		);
		$orderBy = '';
		$groupBy = '';
		$limit = '';

		array_push($where['AND'], $table1 . '.uid=' . $table2 . '.ExtensionId');

		if (is_array($conf)) {

			// FIELDS
			if ($conf['Fields']) {
				$fields = $conf['Fields'];
			}

			// WHERE
			if ($conf['ExtensionId']) {
				array_push($where['AND'], $table1 . '.uid=' . intval($conf['ExtensionId']));
			}

			// GROUP BY
			if ($conf['GroupBy']) {
				$groupBy = $conf['GroupBy'];
			}

			// ORDER BY
			if ($conf['OrderBy']) {
				$orderBy = $conf['OrderBy'];
			}

			// LIMIT
			if ($conf['Limit']) {
				$limit = $conf['Limit'];
			}
		}

		// WHERE
		$where = $this->where($where);

		return $this->select($fields, $table, $where, $groupBy, $orderBy, $limit, $conf['Debug']);

	}


	/**
	 * @param  $fileArray
	 * @param  $currentTableId
	 * @return void
	 */
	public function setFiles($fileArray, $currentTableId) {

		// Define Table
		$table = 'tx_snowbabel_indexing_files_' . $currentTableId;
		$insertFiles = array ();

		// Empty Table
		$this->truncate($table);

		if (count($fileArray)) {

			foreach ($fileArray as $files) {

				if (count($files)) {

					foreach ($files as $file) {
						array_push($insertFiles, $file);
					}

				}

			}

			// Add Records To Table
			$this->insert($table, $insertFiles);
		}

	}


	/**
	 * @param       $currentTableId
	 * @param array $conf
	 * @param bool $count
	 * @return null
	 */
	public function getLabels($currentTableId, $conf = array (), $count = FALSE) {

		$table1 = 'tx_snowbabel_indexing_extensions_' . $currentTableId;
		$table2 = 'tx_snowbabel_indexing_files_' . $currentTableId;
		$table3 = 'tx_snowbabel_indexing_labels_' . $currentTableId;
		$table = $table1 . ',' . $table2 . ',' . $table3;
		$fields = $table1 . '.ExtensionKey,' .
			$table1 . '.ExtensionTitle,' .
			$table1 . '.ExtensionDescription,' .
			$table1 . '.ExtensionCategory,' .
			$table1 . '.ExtensionIcon,' .
			$table1 . '.ExtensionLocation,' .
			$table1 . '.ExtensionPath,' .
			$table1 . '.ExtensionLoaded,'
			. $table2 . '.uid AS FileId,' . $table2 . '.ExtensionId,' . $table2 . '.FileKey,'
			. $table3 . '.uid AS LabelId,' . $table3 . '.LabelName,' . $table3 . '.LabelDefault';
		$where = array (
			'OR' => array (),
			'AND' => array (),
			'SEARCH_AND' => array (),
			'SEARCH_OR' => array (),
		);
		$orderBy = '';
		$groupBy = '';
		$limit = '';

		array_push($where['AND'], $table1 . '.uid=' . $table2 . '.ExtensionId');
		array_push($where['AND'], $table2 . '.uid=' . $table3 . '.FileId');

		if (is_array($conf) && count($conf) > 0) {

			// FIELDS
			if ($conf['Fields']) {
				$fields = $conf['Fields'];
			}

			// WHERE
			if ($conf['ExtensionId']) {
				array_push($where['AND'], $table1 . '.uid=' . intval($conf['ExtensionId']));
			}

			// GROUP BY
			if ($conf['GroupBy']) {
				$groupBy = $conf['GroupBy'];
			}

			// ORDER BY
			if ($conf['OrderBy']) {
				$orderBy = $conf['OrderBy'];
			}

			// LIMIT
			if ($conf['Limit'] && !$count) {
				$limit = $conf['Limit'];
			}

			// SEARCH
			if ($conf['Search']) {
				array_push($where['SEARCH_OR'], $table3 . '.LabelName LIKE \'%' . $conf['Search'] . '%\'');
				array_push($where['SEARCH_OR'], $table3 . '.LabelDefault LIKE \'%' . $conf['Search'] . '%\'');
			}
		}

		// WHERE
		$where = $this->where($where);

		return $this->select($fields, $table, $where, $groupBy, $orderBy, $limit, $conf['Debug'], $count);

	}


	/**
	 * @param  $labelArray
	 * @param  $currentTableId
	 * @return void
	 */
	public function setLabels($labelArray, $currentTableId) {

		// Define Table
		$table = 'tx_snowbabel_indexing_labels_' . $currentTableId;
		$insertLabels = array ();

		// Empty Table
		$this->truncate($table);

		if (count($labelArray)) {

			foreach ($labelArray as $labels) {

				if (count($labels)) {

					foreach ($labels as $labelRow) {
						array_push($insertLabels, $labelRow);
					}

				}

			}

			// Add Records To Table
			$this->insert($table, $insertLabels);
		}

	}


	/**
	 * @param       $currentTableId
	 * @param array $conf
	 * @param array $languages
	 * @param bool $count
	 * @return array|int|null|string
	 */
	public function getTranslations($currentTableId, $conf = array (), $languages = array (), $count = FALSE) {

		$table1 = 'tx_snowbabel_indexing_extensions_' . $currentTableId;
		$table2 = 'tx_snowbabel_indexing_files_' . $currentTableId;
		$table3 = 'tx_snowbabel_indexing_labels_' . $currentTableId;
		$table3Alias = 'Labels';
		$table4 = 'tx_snowbabel_indexing_translations_' . $currentTableId;
		$table = $table1 . ',' . $table2 . ',' . $table3 . ' AS ' . $table3Alias; // Needed For Subqueries!!!
		$fields = $table3Alias . '.LabelName,' . $table3Alias . '.LabelDefault';

		$where = array (
			'OR' => array (),
			'AND' => array (),
			'SEARCH_AND' => array (),
			'SEARCH_OR' => array (),
		);
		$orderBy = '';
		$groupBy = '';
		$limit = '';

		array_push($where['AND'], $table1 . '.uid=' . $table2 . '.ExtensionId');
		array_push($where['AND'], $table2 . '.uid=' . $table3Alias . '.FileId');

		if (is_array($conf) && count($conf) > 0) {

			// FIELDS
			if ($conf['Fields']) {
				$fields = $conf['Fields'];
			}

			// WHERE
			if ($conf['ExtensionId']) {
				array_push($where['AND'], $table1 . '.uid=' . intval($conf['ExtensionId']));
			}

			// GROUP BY
			if ($conf['GroupBy']) {
				$groupBy = $conf['GroupBy'];
			}

			// ORDER BY
			if ($conf['OrderBy']) {
				$orderBy = $conf['OrderBy'];
			}

			if ($conf['Sort']) {

				// Translations
				if (strpos($conf['Sort'], 'TranslationValue_') !== FALSE) {
					$orderBy = $conf['Sort'] . ' ' . $conf['Dir'];
				} // Label-Table
				else {
					$orderBy = $table3Alias . '.' . $conf['Sort'] . ' ' . $conf['Dir'];
				}

			}

			// LIMIT
			if ($conf['Limit'] && !$count) {
				$limit = $conf['Limit'];
			}

			// SEARCH
			if ($conf['Search']) {
				array_push($where['SEARCH_OR'], $table3Alias . '.LabelName LIKE \'%' . $conf['Search'] . '%\'');
				array_push($where['SEARCH_OR'], $table3Alias . '.LabelDefault LIKE \'%' . $conf['Search'] . '%\'');
			}

		}

		// LANGUAGES
		if (count($languages)) {
			foreach ($languages as $language) {

				// FORM
				$table .= ',' . $table4 . ' trans_' . $language;

				// SELECT
				$fields .= ',trans_' . $language . '.uid as TranslationId_' . $language;
				$fields .= ',trans_' . $language . '.TranslationValue as TranslationValue_' . $language;

				// WHERE
				array_push($where['AND'], 'Labels.uid = trans_' . $language . '.LabelId');
				array_push($where['AND'], 'trans_' . $language . '.TranslationLanguage = \'' . $language . '\'');

				// SEARCH
				if ($conf['Search']) {
					array_push($where['SEARCH_OR'], 'trans_' . $language . '.TranslationValue LIKE \'%' . $conf['Search'] . '%\'');
				}

			}
		}

		// WHERE
		$where = $this->where($where);

		return $this->select($fields, $table, $where, $groupBy, $orderBy, $limit, $conf['Debug'], $count);

	}


	/**
	 * @param $currentTableId
	 * @param array $conf
	 * @return array|int|null|string
	 */
	public function getTranslation($currentTableId, $conf = array ()) {
		$table1 = 'tx_snowbabel_indexing_extensions_' . $currentTableId;
		$table2 = 'tx_snowbabel_indexing_files_' . $currentTableId;
		$table3 = 'tx_snowbabel_indexing_labels_' . $currentTableId;
		$table4 = 'tx_snowbabel_indexing_translations_' . $currentTableId;
		$table = $table1 . ',' . $table2 . ',' . $table3 . ',' . $table4;
		$fields = $table1 . '.ExtensionKey,' . $table1 . '.ExtensionTitle,' . $table1 . '.ExtensionDescription,' . $table1 . '.ExtensionCategory,'
			. $table1 . '.ExtensionIcon,' . $table1 . '.ExtensionLocation,' . $table1 . '.ExtensionPath,' . $table1 . '.ExtensionLoaded,'
			. $table2 . '.uid AS FileId,' . $table2 . '.ExtensionId,' . $table2 . '.FileKey,'
			. $table3 . '.uid AS LabelId,' . $table3 . '.LabelName,' . $table3 . '.LabelDefault,'
			. $table4 . '.uid AS TranslationId,' . $table4 . '.TranslationValue,' . $table4 . '.TranslationLanguage,' . $table4 . '.TranslationEmpty';

		$where = array (
			'OR' => array (),
			'AND' => array (),
			'SEARCH_AND' => array (),
			'SEARCH_OR' => array (),
		);
		$orderBy = '';
		$groupBy = '';
		$limit = '';

		array_push($where['AND'], $table1 . '.uid=' . $table2 . '.ExtensionId');
		array_push($where['AND'], $table2 . '.uid=' . $table3 . '.FileId');
		array_push($where['AND'], $table3 . '.uid=' . $table4 . '.LabelId');

		if (is_array($conf) && count($conf) > 0) {

			// FIELDS
			if ($conf['Fields']) {
				$fields = $conf['Fields'];
			}

			// WHERE
			if ($conf['TranslationId']) {
				array_push($where['AND'], $table4 . '.uid=' . intval($conf['TranslationId']));
			}

			// GROUP BY
			if ($conf['GroupBy']) {
				$groupBy = $conf['GroupBy'];
			}

			// ORDER BY
			if ($conf['OrderBy']) {
				$orderBy = $conf['OrderBy'];
			}

		}

		// WHERE
		$where = $this->where($where);

		return $this->select($fields, $table, $where, $groupBy, $orderBy, $limit, $conf['Debug']);

	}


	/**
	 * @param  $translationArray
	 * @param  $currentTableId
	 * @return void
	 */
	public function setTranslations($translationArray, $currentTableId) {

		// Define Table
		$table = 'tx_snowbabel_indexing_translations_' . $currentTableId;
		$insertTranslations = array ();

		// Empty Table
		$this->truncate($table);

		if (count($translationArray)) {

			foreach ($translationArray as $translations) {

				if (count($translations)) {

					foreach ($translations as $translation) {
						array_push($insertTranslations, $translation);
					}

				}

			}

			// Add Records To Table
			$this->insert($table, $insertTranslations);
		}

	}


	/**
	 * @param  $translationId
	 * @param  $translationValue
	 * @param  $currentTableId
	 * @return void
	 */
	public function setTranslation($translationId, $translationValue, $currentTableId) {

		$this->getDatabaseConnection()->exec_UPDATEquery(
			'tx_snowbabel_indexing_translations_' . $currentTableId,
			'uid=' . intval($translationId),
			array (
				'tstamp' => time(),
				'TranslationValue' => $translationValue
			)
		);

	}


	/**
	 * @param        $fields
	 * @param        $table
	 * @param string $where
	 * @param string $groupBy
	 * @param string $orderBy
	 * @param string $limit
	 * @param bool $debug
	 * @param bool $count
	 * @return array|int|null|string
	 */
	private function select($fields, $table, $where = '', $groupBy = '', $orderBy = '', $limit = '', $debug = FALSE, $count = FALSE) {

		if ($debug) {
			return $this->getDatabaseConnection()->SELECTquery($fields, $table, $where, $groupBy, $orderBy, $limit);
		}

		$select = $this->getDatabaseConnection()->exec_SELECTgetRows(
			$fields,
			$table,
			$where,
			$groupBy,
			$orderBy,
			$limit
		);

		if (!count($select)) {

			if ($count) {
				return 0;
			}

			return NULL;

		} else {

			if (is_array($select)) {

				if ($count) {
					return count($select);
				}

				return $select;

			} else {
				if ($count) {
					return 0;
				}

				return NULL;
			}
		}

	}


	/**
	 * @param  $table
	 * @param  $dataArray
	 * @return bool|null
	 *
	 * Simply add table name and data array. The function will take care of inserting data
	 * depending on typo3 version (single insert / all at once)
	 *
	 * data array format:
	 * $DataArray[0]['Field1'] = Value1 // First row
	 * $DataArray[0]['Field2'] = Value2
	 * $DataArray[1]['Field1'] = Value3 // Second row
	 * $DataArray[1]['Field2'] = Value4
	 *
	 */
	private function insert($table, $dataArray) {

		$success = NULL;

		if (is_array($dataArray) && count($dataArray) > 0) {

			foreach ($dataArray as $row) {

				$row['tstamp'] = strval(time());
				$row['crdate'] = strval(time());

				$this->getDatabaseConnection()->exec_INSERTquery(
					$table,
					$row
				);

			}

			// Success
			$success = TRUE;

		}

		return $success;

	}


	/**
	 * @param  $table
	 * @return void
	 */
	private function truncate($table) {
		$this->getDatabaseConnection()->exec_TRUNCATEquery($table);
	}


	/**
	 * @param  $where
	 * @return string
	 */
	private function where($where) {

		if (count($where['OR'])) {
			$where['OR'] = '(' . implode($where['OR'], ' OR ') . ')';
		} else {
			unset($where['OR']);
		}

		if (count($where['AND'])) {
			$where['AND'] = '(' . implode($where['AND'], ' AND ') . ')';
		} else {
			unset($where['AND']);
		}

		if (count($where['SEARCH_OR'])) {
			$where['SEARCH_OR'] = '(' . implode($where['SEARCH_OR'], ' OR ') . ')';
		} else {
			unset($where['SEARCH_OR']);
		}

		if (count($where['SEARCH_AND'])) {
			$where['SEARCH_AND'] = '(' . implode($where['SEARCH_AND'], ' AND ') . ')';
		} else {
			unset($where['SEARCH_AND']);
		}

		$where = implode($where, ' AND ');

		return $where;
	}


	/**
	 * @param  $commaSeparatedString
	 * @param  $table
	 * @return string
	 */
	private function prepareCommaSeparatedString($commaSeparatedString, $table) {

		if (is_string($commaSeparatedString) && strpos($commaSeparatedString, ',') !== FALSE) {
			$commaSeparatedString = explode(',', $commaSeparatedString);
		}

		$commaSeparatedString = $this->getDatabaseConnection()->fullQuoteArray($commaSeparatedString, $table);

		return implode(',', $commaSeparatedString);
	}


	/**
	 * @return DatabaseConnection
	 */
	private static function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}