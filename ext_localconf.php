<?php

if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerExtDirectComponent(
	'TYPO3.Snowbabel.ExtDirect',
	'Snowflake\\Snowbabel\\Connection\ExtDirectServer'
);

// Add Scheduler Configuration For Indexing
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_snowbabel_system_indexing'] = array(
	'extension' => $_EXTKEY,
	'title' => 'Snowbabel - Indexing',
	'description' => 'Is indexing all translation data on current installation'
);

?>