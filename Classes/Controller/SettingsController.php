<?php
namespace Snowflake\Snowbabel\Controller;

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

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;


/**
 * Class SettingsController
 *
 * @package Snowflake\Snowbabel\Controller
 */
class SettingsController extends ActionController {


	/**
	 * Sets default view to json
	 *
	 * @var string
	 */
	protected $defaultViewObjectName = 'TYPO3\CMS\Extbase\Mvc\View\JsonView';


	/**
	 * Extension repository
	 *
	 * @var \Snowflake\Snowbabel\Domain\Repository\ExtensionRepository
	 * @inject
	 */
	protected $extensionRepository;


	/**
	 * Returns all active & non active extensions
	 */
	public function getExtensionsAction() {

		$this->view->assign(
			'value',
			$this->extensionRepository->findAllAsArray(
				array ('key', 'title', 'description', 'category', 'selected', 'active', 'siteRelativePath', 'icon')
			)
		);
	}


	/**
	 * Returns all selected extensions
	 */
	public function getSelectedExtensionsAction() {

		$this->view->assign(
			'value',
			$this->extensionRepository->findSelectedAsArray(array ('key', 'title'))
		);

	}


	/**
	 * Updates extension
	 *
	 * @throws \Exception
	 */
	public function updateExtensionAction() {

		try {

			if (!$this->request->hasArgument('key')) {
				throw new \Exception('Required \'key\' is missing');
			}

			// Gets required key
			$key = $this->request->getArgument('key');

			// Finds extension by key
			$extension = $this->extensionRepository->findByKey($key);

			// Check for different arguments and set it accordingly
			if ($this->request->hasArgument('selected')) {
				$extension->setSelected($this->request->getArgument('selected'));
			}

			// Saves changes
			$this->extensionRepository->update($extension);

		} catch (\Exception $e) {
			// Todo: make it json conform
			// Makes sure there is always thrown a 400er (since system errors are often timestamps)
			throw new \Exception($e->getMessage(), 400);
		}


	}

}