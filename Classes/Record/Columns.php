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

/**
 * Class Columns
 *
 * @package Snowflake\Snowbabel\Record
 */
class Columns {


	/**
	 * @var \Snowflake\Snowbabel\Service\Configuration
	 */
	private $confObj;


	/**
	 * @var
	 */
	private $debug;


	/**
	 * @var
	 */
	private $columnsConfiguration;


	/**
	 * @var array
	 */
	private $columns = array ();


	/**
	 * @param $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;

		$this->debug = $confObj->debug;

		// get Application params

		// get Extension params

		// get User parasm
		$this->columnsConfiguration = $this->confObj->getUserConfigurationColumns();

		$this->initColumns();
	}


	/**
	 * @return array
	 */
	public function getColumns() {
		// get columns
		return $this->columns;
	}


	/**
	 * @return void
	 */
	private function initColumns() {

		if (is_array($this->columnsConfiguration)) {
			foreach ($this->columnsConfiguration as $id => $property) {

				$label = $this->getColumnLabel($id);

				array_push($this->columns, array (
					'ColumnId' => $id,
					'ColumnName' => $label,
					'ColumnSelected' => $property
				));

			}
		}

	}


	/**
	 * @param $id
	 * @return string
	 */
	private function getColumnLabel($id) {

		$labelName = 'translation_columnselection_' . $id;

		$label = $this->confObj->getLocallang($labelName);

		return $label;

	}

}