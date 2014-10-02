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
class tx_snowbabel_Extensions {

	/**
	 * @var tx_snowbabel_configuration
	 */
	private $confObj;

	/**
	 * @var tx_snowbabel_db
	 */
	private $Db;

	/**
	 *
	 */
	private $debug;

	/**
	 * @var
	 */
	private $CurrentTableId;

	/**
	 *
	 */
	private $ShowLocalExtensions;

	/**
	 *
	 */
	private $ShowSystemExtensions;

	/**
	 *
	 */
	private $ShowGlobalExtensions;

	/**
	 *
	 */
	private $BlacklistedExtensions;

	/**
	 *
	 */
	private $BlacklistedCategories;

	/**
	 *
	 */
	private $WhitelistedActivated;

	/**
	 *
	 */
	private $WhitelistedExtensions;

	/**
	 *
	 */
	private $ShowOnlyLoadedExtensions;

	/**
	 *
	 */
	private $IsAdmin;

	/**
	 *
	 */
	private $PermittedExtensions;

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

		$this->BlacklistedExtensions = $this->confObj->getApplicationConfiguration('BlacklistedExtensions');
		$this->BlacklistedCategories = $this->confObj->getApplicationConfiguration('BlacklistedCategories');

		$this->WhitelistedActivated = $this->confObj->getApplicationConfiguration('WhitelistedActivated');
		$this->WhitelistedExtensions = $this->confObj->getApplicationConfiguration('WhitelistedExtensions');

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
			'BlacklistedExtensions' => !$this->WhitelistedActivated ? $this->BlacklistedExtensions : '',
			'BlacklistedCategories' => !$this->WhitelistedActivated ? $this->BlacklistedCategories : '',
			'WhitelistedExtensions' => $this->WhitelistedActivated ? $this->WhitelistedExtensions : '',
			'OrderBy' => 'ExtensionTitle',
			'Debug' => '0',
		);

		if(!$this->IsAdmin) {

				// Do Not Show Anything If No Permitted Extensions Available
			if($this->PermittedExtensions == '') {
				return NULL;
			}
			else {
				$Conf['PermittedExtensions'] = $this->PermittedExtensions;
			}
		}

		$Extensions = $this->Db->getExtensions($this->CurrentTableId, $Conf);

		return $Extensions;

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Extensions/class.tx_snowbabel_extensions_list.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Extensions/class.tx_snowbabel_extensions_list.php']);
}

?>