<?php
namespace Snowflake\Snowbabel\Utility\Ajax;

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

use TYPO3\CMS\Core\Http\AjaxRequestHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Dispatcher
 *
 * @package Snowflake\Snowbabel\Utility\Ajax
 */
class DispatcherUtility {


	/**
	 * @var string
	 */
	protected $vendorName = 'Snowflake';


	/**
	 * @var string
	 */
	protected $extensionName = 'Snowbabel';


	/**
	 * @var string
	 */
	protected $pluginName = '';


	/**
	 * @var string
	 */
	protected $controllerName = 'Main';


	/**
	 * @var string
	 */
	protected $actionName = 'Index';


	/**
	 * @var array
	 */
	protected $arguments = array ();


	/**
	 * @var array
	 */
	protected $output = array ();


	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;


	/**
	 */
	public function dispatch() {

		try {

			// Init parameters from ajax call
			$this->initCallParameters();

			// Validate parameters
			$this->validateParameters();

			// Runs extbase
			$this->runExtbase();


		} catch (\Exception $e) {

			// Set error
			$this->error($e);

			// Output result or error
			$this->output();
		}

	}


	/**
	 *
	 */
	protected function output() {
		header('Content-Type: application/json');
		echo json_encode($this->output);

	}


	/**
	 * @param \Exception $exception
	 */
	protected function error($exception) {

		$this->output = array (

			'error' => array (
				'code' => $exception->getCode(),
				'message' => $exception->getMessage()
			),


		);

	}


	/**
	 *
	 */
	protected function initCallParameters() {

		if (is_string(GeneralUtility::_GP('pluginName'))) {
			$this->pluginName = GeneralUtility::_GP('pluginName');
		}

		if (is_string(GeneralUtility::_GP('controllerName'))) {
			$this->controllerName = GeneralUtility::_GP('controllerName');
		}

		if (is_string(GeneralUtility::_GP('actionName'))) {
			$this->actionName = GeneralUtility::_GP('actionName');
		}

		$arguments = json_decode(GeneralUtility::_GP('arguments'), true);

		if (is_array($arguments)) {
			$this->arguments = $arguments;
		}

	}


	/**
	 * @throws \Exception
	 */
	protected function validateParameters() {

		// Plugin
		$this->validateForNonEmptyString('pluginName');

		// Controller
		$this->validateForNonEmptyString('controllerName');

		// Action
		$this->validateForNonEmptyString('actionName');

	}


	/**
	 * @param $name
	 * @throws \Exception
	 */
	protected function validateForNonEmptyString($name) {

		if (!$this->$name || !is_string($this->$name) || strlen($this->$name) < 1) {
			throw new \Exception('No \'' . $name . '\' set', 400);
		}

	}


	/**
	 * Runs the called extbase controller/action
	 */
	protected function runExtbase() {

		// Init object manager from extbase
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

		// Run the bootstrap procedure
		$this->runExtbaseBootstrap();

		// Get the request
		$request = $this->getExtbaseRequest();

		// Get the response
		$response = $this->getExtbaseResponse();

		// Run the dispatcher
		$this->runExtbaseDispatcher($request, $response);

		// Send responded output to client
		$response->send();

	}


	/**
	 *
	 */
	protected function runExtbaseBootstrap() {

		/** @var \TYPO3\CMS\Extbase\Core\Bootstrap $bootstrap */
		$bootstrap = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Core\\Bootstrap');
		$bootstrap->initialize(array (
			'extensionName' => $this->extensionName,
			'pluginName' => $this->pluginName,
		));

	}


	/**
	 * @return \TYPO3\CMS\Extbase\Mvc\Web\Request
	 */
	protected function getExtbaseRequest() {

		/** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
		$request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Request');

		// Set vendor
		$request->setControllerVendorName($this->vendorName);

		// Set extension
		$request->setControllerExtensionName($this->extensionName);

		// Set plugin
		$request->setPluginName($this->pluginName);

		// Set controller
		$request->setControllerName($this->controllerName);

		// Set action
		$request->setControllerActionName($this->actionName);

		// Set arguments
		$request->setArguments($this->arguments);

		return $request;

	}


	/**
	 * @return \TYPO3\CMS\Extbase\Mvc\Web\Response
	 */
	protected function getExtbaseResponse() {

		// Create response object
		return $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response');

	}


	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request
	 * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response
	 */
	protected function runExtbaseDispatcher(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response) {

		/** @var \TYPO3\CMS\Extbase\Mvc\Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Dispatcher');

		// Dispatch response & request
		$dispatcher->dispatch($request, $response);

	}


}