<?php
namespace Snowflake\Snowbabel\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Daniel Alder <support@snowflake.ch>
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
 *  GNU General Public License for more details.d
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class ExtensionSetting
 *
 * @package Snowflake\Snowbabel\Domain\Model
 */
class ExtensionSetting extends AbstractEntity {


	/**
	 * @var \DateTime
	 */
	protected $crdate;


	/**
	 * @var \DateTime
	 */
	protected $tstamp;


	/**
	 * @var string
	 */
	protected $extensionKey;


	/**
	 * @var boolean
	 */
	protected $selected;


	/**
	 * @return \DateTime
	 */
	public function getCrdate() {
		return $this->crdate;
	}


	/**
	 * @param \DateTime $crdate
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
	}


	/**
	 * @return \DateTime
	 */
	public function getTstamp() {
		return $this->tstamp;
	}


	/**
	 * @param \DateTime $tstamp
	 */
	public function setTstamp($tstamp) {
		$this->tstamp = $tstamp;
	}


	/**
	 * @return string
	 */
	public function getExtensionKey() {
		return $this->extensionKey;
	}


	/**
	 * @param string $extensionKey
	 */
	public function setExtensionKey($extensionKey) {
		$this->extensionKey = $extensionKey;
	}


	/**
	 * @return boolean
	 */
	public function getSelected() {
		return $this->selected;
	}


	/**
	 * @param boolean $selected
	 */
	public function setSelected($selected) {
		$this->selected = $selected;
	}

}
