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

use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Module: Snowbabel
 *
 * @author    Daniel Alder <info@snowflake.ch>
 */

unset($MCONF);
require('conf.php');
require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');
$BE_USER->modAccess($MCONF, 1);

/**
 * Plugin 'Snowbabel' for the 'Snowbabel' extension.
 *
 * @author        Daniel Alder <info@snowflake.ch>
 * @package       TYPO3
 * @subpackage    tx_snowbabel
 */
class tx_mod1_snowbabel extends BaseScriptClass {


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
		$this->doc = GeneralUtility::makeInstance('bigDoc');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];

		// create app object
		$this->translation = GeneralUtility::makeInstance('tx_snowbabel_Application_Translation', $this);

		// init app
		$this->translation->init();

		// render app
		$this->translation->render();

		// start template
		$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('globalTitle'));

		$this->content .= '<div id="snowbabel_translation"></div>';

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
$SOBE = GeneralUtility::makeInstance('tx_mod1_snowbabel');
$SOBE->init();

$SOBE->main();

$SOBE->render();