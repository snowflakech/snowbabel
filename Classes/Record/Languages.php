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
	private $confObj;


	/**
	 *
	 */
	private $debug;


	/**
	 *
	 */
	private $userLanguages = array ();


	/**
	 *
	 */
	private $availableLanguages;


	/**
	 *
	 */
	private $isAdmin;


	/**
	 *
	 */
	private $permittedLanguages;


	/**
	 *
	 */
	private $allocatedGroups;


	/**
	 *
	 */
	private $selectedLanguages;


	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;

		$this->debug = $confObj->debug;

		// get Application params
		$this->availableLanguages = $this->confObj->getApplicationConfiguration('AvailableLanguages');

		// get Extension params

		// get User parasm
		$this->isAdmin = $this->confObj->getUserConfigurationIsAdmin();
		$this->permittedLanguages = $this->confObj->getUserConfiguration('PermittedLanguages');
		$this->allocatedGroups = $this->confObj->getUserConfiguration('AllocatedGroups');
		$this->selectedLanguages = $this->confObj->getUserConfiguration('SelectedLanguages');
	}


	/**
	 * @return array
	 */
	public function getLanguages() {

		// Get User Languages
		$this->getLanguagesUser();

		// Set Selected Languages
		$this->getLanguagesSelected();

		return $this->userLanguages;
	}


	/**
	 *
	 * @return void
	 */
	private function getLanguagesUser() {

		// Admin - application languages
		if ($this->isAdmin) {
			$this->userLanguages = $this->availableLanguages;
		} else {
			// Cm - permitted languages

			// get permitted languages
			$permittedLanguages = explode(',', $this->permittedLanguages);

			// add application language if is permitted language
			if (is_array($this->availableLanguages)) {

				foreach ($this->availableLanguages as $availableLanguage) {

					if (array_search($availableLanguage['LanguageKey'], $permittedLanguages) !== FALSE) {

						// add permitted language to language array
						array_push($this->userLanguages, $availableLanguage);

					}

				}

			}

		}

	}


	/**
	 *
	 * @return void
	 */
	private function getLanguagesSelected() {

		// selected languages
		$selectedLanguages = explode(',', $this->selectedLanguages);

		if (count($this->userLanguages) > 0) {

			foreach ($this->userLanguages as $key => $userLanguage) {

				if (array_search($userLanguage['LanguageId'], $selectedLanguages) !== FALSE) {
					$selected = TRUE;
				} else {
					$selected = FALSE;
				}

				// add marker to array
				$this->userLanguages[$key]['LanguageSelected'] = $selected;
			}

		}

	}

}