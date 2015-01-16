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
 * Class Extensions
 *
 * @package Snowflake\Snowbabel\Record
 */
class Extensions {


	/**
	 * @var \Snowflake\Snowbabel\Service\Configuration
	 */
	private $confObj;


	/**
	 * @var \Snowflake\Snowbabel\Service\Database
	 */
	private $database;


	/**
	 *
	 */
	private $debug;


	/**
	 * @var
	 */
	private $currentTableId;


	/**
	 *
	 */
	private $showLocalExtensions;


	/**
	 *
	 */
	private $showSystemExtensions;


	/**
	 *
	 */
	private $showGlobalExtensions;


	/**
	 *
	 */
	private $approvedExtensions;


	/**
	 *
	 */
	private $showOnlyLoadedExtensions;


	/**
	 *
	 */
	private $isAdmin;


	/**
	 *
	 */
	private $permittedExtensions;


	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;
		$this->database = $this->confObj->getDatabase();
		$this->debug = $confObj->debug;

		// Get Current TableId
		$this->currentTableId = $this->database->getCurrentTableId();

		// get Application params
		$this->showLocalExtensions = $this->confObj->getApplicationConfiguration('ShowLocalExtensions');
		$this->showSystemExtensions = $this->confObj->getApplicationConfiguration('ShowSystemExtensions');
		$this->showGlobalExtensions = $this->confObj->getApplicationConfiguration('ShowGlobalExtensions');

		$this->approvedExtensions = $this->confObj->getApplicationConfiguration('ApprovedExtensions');

		$this->showOnlyLoadedExtensions = $this->confObj->getApplicationConfiguration('ShowOnlyLoadedExtensions');

		// get User params
		$this->isAdmin = $this->confObj->getUserConfigurationIsAdmin();
		$this->permittedExtensions = $this->confObj->getUserConfiguration('PermittedExtensions');

	}


	/**
	 * @return null
	 */
	public function getExtensions() {

		$conf = array (
			'Fields' => 'uid AS ExtensionId,ExtensionKey,ExtensionTitle,ExtensionDescription,ExtensionCategory,ExtensionIcon,ExtensionLocation,ExtensionPath,ExtensionLoaded',
			'Local' => $this->showLocalExtensions,
			'System' => $this->showSystemExtensions,
			'Global' => $this->showGlobalExtensions,
			'OnlyLoaded' => $this->showOnlyLoadedExtensions,
			'ApprovedExtensions' => $this->approvedExtensions,
			'OrderBy' => 'ExtensionTitle',
			'Debug' => '0',
		);

		if (!$this->isAdmin) {

			// Do Not Show Anything If No Permitted Extensions Available
			if ($this->permittedExtensions == '') {
				return NULL;
			} else {
				$conf['PermittedExtensions'] = $this->permittedExtensions;
			}
		}

		$extensions = $this->database->getExtensions($this->currentTableId, $conf);

		return $extensions;

	}

}