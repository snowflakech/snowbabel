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
 * Module: Snowbabel
 *
 * @author	Daniel Alder <info@snowflake.ch>
 */

unset($MCONF);
require('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$BE_USER->modAccess($MCONF, 1);

/**
 * Plugin 'Snowbabel' for the 'Snowbabel' extension.
 *
 * @author	Daniel Alder <info@snowflake.ch>
 * @package	TYPO3
 * @subpackage	tx_snowbabel
 */
class tx_mod2_snowbabel extends t3lib_SCbase {

	/**
	 * @var tx_snowbabel_Application_Translation
	 */
	private $translation;

	/**
	 * @var string
	 */
	public $content;

	/**
	 * @var bigDoc
	 */
	public $doc;

	/**
	 *
	 */
  public function __construct() {

	}

	/**
	 *
	 */
	public function init() {

	}

	/**
	 *
	 */
	public function main() {

			// init template object:
		$this->doc = t3lib_div::makeInstance('bigDoc');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];

			// create app object
		$this->settings = t3lib_div::makeInstance('tx_snowbabel_Application_Settings', $this);

			// init app
		$this->settings->init();

			// render app
		$this->settings->render();

			// start template
		$this->content .= $this->doc->startPage( $GLOBALS['LANG']->getLL('globalTitle'));

		$this->content .= '<div id="snowbabel_settings"></div>';

		$this->content .= $this->doc->endPage();

	}

	/**
	 *
	 */
	public function render() {
		echo $this->content;
	}
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_mod2_snowbabel');
$SOBE->init();

$SOBE->main();

$SOBE->render();

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/mod2/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/mod2/index.php']);
}

?>