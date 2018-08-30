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

/**
 * Class Languages
 *
 * @package Snowflake\Snowbabel\Record
 */
class Languages {


	/**
	 * @var Configuration
	 */
    protected $confObj;


	/**
	 *
	 */
    protected $debug;


	/**
	 *
	 */
    protected $UserLanguages = array();


	/**
	 *
	 */
    protected $AvailableLanguages;


	/**
	 *
	 */
    protected $IsAdmin;


	/**
	 *
	 */
    protected $PermittedLanguages;


	/**
	 *
	 */
    protected $AllocatedGroups;


	/**
	 *
	 */
    protected $SelectedLanguages;


	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;

		$this->debug = $confObj->debug;

		// get Application params
		$this->AvailableLanguages = $this->confObj->getApplicationConfiguration('AvailableLanguages');

		// get Extension params

		// get User parasm
		$this->IsAdmin = $this->confObj->getUserConfigurationIsAdmin();
		$this->PermittedLanguages = $this->confObj->getUserConfiguration('PermittedLanguages');
		$this->AllocatedGroups = $this->confObj->getUserConfiguration('AllocatedGroups');
		$this->SelectedLanguages = $this->confObj->getUserConfiguration('SelectedLanguages');
	}


	/**
	 *
	 */
	public function getLanguages() {

		// Get User Languages
		$this->getLanguagesUser();

		// Set Selected Languages
		$this->getLanguagesSelected();

		return $this->UserLanguages;
	}


	/**
	 *
	 */
	private function getLanguagesUser() {

		// Admin - application languages
		if($this->IsAdmin) {
			$this->UserLanguages = $this->AvailableLanguages;
		} // Cm - permitted languages
		else {

			// get permitted languages
			$PermittedLanguages = explode(',', $this->PermittedLanguages);

			// add application language if is permitted language
			if(is_array($this->AvailableLanguages)) {

				foreach($this->AvailableLanguages as $AvailableLanguage) {

					if(array_search($AvailableLanguage['LanguageKey'], $PermittedLanguages) !== false) {

						// add permitted language to language array
						array_push($this->UserLanguages, $AvailableLanguage);

					}

				}

			}

		}

	}


	/**
	 *
	 */
	private function getLanguagesSelected() {

		// selected languages
		$SelectedLanguages = explode(',', $this->SelectedLanguages);

		if(count($this->UserLanguages) > 0) {

			foreach($this->UserLanguages as $key => $UserLanguage) {

				if(array_search($UserLanguage['LanguageId'], $SelectedLanguages) !== false) {
					$selected = true;
				} else {
					$selected = false;
				}

				// add marker to array
				$this->UserLanguages[$key]['LanguageSelected'] = $selected;
			}

		}

	}

}