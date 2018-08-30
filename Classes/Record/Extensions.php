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
    protected $confObj;


	/**
	 * @var \Snowflake\Snowbabel\Service\Database
	 */
    protected $Db;


	/**
	 *
	 */
    protected $debug;


	/**
	 * @var
	 */
    protected $CurrentTableId;


	/**
	 *
	 */
    protected $ShowLocalExtensions;


	/**
	 *
	 */
    protected $ShowSystemExtensions;


	/**
	 *
	 */
    protected $ShowGlobalExtensions;


	/**
	 *
	 */
    protected $ApprovedExtensions;


	/**
	 *
	 */
    protected $ShowOnlyLoadedExtensions;


	/**
	 *
	 */
    protected $IsAdmin;


	/**
	 *
	 */
    protected $PermittedExtensions;


	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;
		$this->Db = $this->confObj->getDb();
		$this->debug = $confObj->debug;

		// Get Current TableId
		$this->CurrentTableId = $this->Db->getCurrentTableId();

		// get Application params
		$this->ShowLocalExtensions = $this->confObj->getApplicationConfiguration('ShowLocalExtensions');
		$this->ShowSystemExtensions = $this->confObj->getApplicationConfiguration('ShowSystemExtensions');
		$this->ShowGlobalExtensions = $this->confObj->getApplicationConfiguration('ShowGlobalExtensions');

		$this->ApprovedExtensions = $this->confObj->getApplicationConfiguration('ApprovedExtensions');

		$this->ShowOnlyLoadedExtensions = $this->confObj->getApplicationConfiguration('ShowOnlyLoadedExtensions');

		// get User params
		$this->IsAdmin = $this->confObj->getUserConfigurationIsAdmin();
		$this->PermittedExtensions = $this->confObj->getUserConfiguration('PermittedExtensions');

	}


	/**
	 * @return null
	 */
	public function getExtensions() {

		$Conf = array(
			'Fields' => 'uid AS ExtensionId,ExtensionKey,ExtensionTitle,ExtensionDescription,ExtensionCategory,ExtensionIcon,ExtensionLocation,ExtensionPath,ExtensionLoaded',
			'Local' => $this->ShowLocalExtensions,
			'System' => $this->ShowSystemExtensions,
			'Global' => $this->ShowGlobalExtensions,
			'OnlyLoaded' => $this->ShowOnlyLoadedExtensions,
			'ApprovedExtensions' => $this->ApprovedExtensions,
			'OrderBy' => 'ExtensionTitle',
			'Debug' => '0',
		);

		if(!$this->IsAdmin) {

			// Do Not Show Anything If No Permitted Extensions Available
			if($this->PermittedExtensions == '') {
				return null;
			} else {
				$Conf['PermittedExtensions'] = $this->PermittedExtensions;
			}
		}

		$Extensions = $this->Db->getExtensions($this->CurrentTableId, $Conf);

		return $Extensions;

	}

}