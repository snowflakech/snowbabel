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

use Snowflake\Snowbabel\Domain\Repository\ExtensionSettingRepository;

/**
 * Class Extension
 *
 * @package Snowflake\Snowbabel\Domain\Model
 */
class Extension {


	/**
	 * @var string
	 */
	protected $key;


	/**
	 * @var string
	 */
	protected $title;


	/**
	 * @var string
	 */
	protected $description;


	/**
	 * @var string
	 */
	protected $type;


	/**
	 * @var string
	 */
	protected $icon;


	/**
	 * @var boolean
	 */
	protected $active;


	/**
	 * @var boolean
	 */
	protected $selected;


	/**
	 * @var string
	 */
	protected $path;


	/**
	 * @var string
	 */
	protected $siteRelativePath;


	/**
	 * @var string
	 */
	protected $category;


	/**
	 * Returns all protected properties as array
	 *
	 * @param array $include
	 * @return array
	 */
	public function toArray($include = array ()) {

		$properties = array ();
		$reflect = new \ReflectionClass($this);

		foreach ($reflect->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
			if (count($include) === 0 || in_array($property->getName(), $include)) {
				$property->setAccessible(TRUE);
				$properties[$property->getName()] = $property->getValue($this);
			}
		}

		return $properties;

	}


	/**
	 * Returns key of extension
	 *
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}


	/**
	 * Sets key of extension
	 *
	 * @param string $key
	 */
	public function setKey($key) {
		$this->key = $key;
	}


	/**
	 * Gets title of extension
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * Sets title of extension
	 *
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * Gets description of extension
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * Sets description of extension
	 *
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * Gets type of extension (System, Global, Local)
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * Sets type of extension
	 *
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	/**
	 * Gets icon of extension
	 *
	 * @return string
	 */
	public function getIcon() {
		return $this->icon;
	}


	/**
	 * Sets icon of extension
	 *
	 * @param string $icon
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}


	/**
	 * Is extension active
	 *
	 * @return boolean
	 */
	public function isActive() {
		return $this->active;
	}


	/**
	 * Sets active state of extension
	 *
	 * @param boolean $active
	 */
	public function setActive($active) {
		$this->active = $active;
	}


	/**
	 * Is extension selected in settings
	 *
	 * @return boolean
	 */
	public function isSelected() {
		return $this->selected;
	}


	/**
	 * Sets selected state from settings
	 *
	 * @param boolean $selected
	 */
	public function setSelected($selected) {
		$this->selected = $selected;
	}


	/**
	 * Gets full path of extension
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


	/**
	 * Sets full path of extension
	 *
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}


	/**
	 * Gets relative path of extension
	 *
	 * @return string
	 */
	public function getSiteRelativePath() {
		return $this->siteRelativePath;
	}


	/**
	 * Sets relative path of extension
	 *
	 * @param string $siteRelativePath
	 */
	public function setSiteRelativePath($siteRelativePath) {
		$this->siteRelativePath = $siteRelativePath;
	}


	/**
	 * Gets category of extension
	 *
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}


	/**
	 * Sets category of extension
	 *
	 * @param string $category
	 */
	public function setCategory($category) {
		$this->category = $category;
	}

}