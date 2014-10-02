<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if(TYPO3_MODE === "BE") {

	// Add new module 'snowbabel'
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'snowbabel',
		'',
		'',
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/'
	);
	// Add submodule 'translation'
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'snowbabel',
		'translation',
		'',
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/'
	);
	// Add submodule 'settings'
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'snowbabel',
		'settings',
		'',
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod2/'
	);

	// Todo: use autoinclude tca
	// Extend Beusers For Translation Access Control
	$tempColumns = array(
		'tx_snowbabel_extensions' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:snowbabel/locallang_db.xlf:label.tx_snowbabel_extensions',
			'config' => Array(
				'type' => 'select',
				'itemsProcFunc' => 'Snowflake\Snowbabel\Hooks\Tca->getExtensions',
				'size' => 10,
				'maxitems' => 9999,
				'default' => ''
			)
		),
		'tx_snowbabel_languages' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:snowbabel/locallang_db.xlf:label.tx_snowbabel_languages',
			'config' => Array(
				'type' => 'select',
				'itemsProcFunc' => 'Snowflake\Snowbabel\Hooks\Tca->getLanguages',
				'size' => 10,
				'maxitems' => 9999,
				'default' => ''
			)
		),
	);

	// Add be_groups fields
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
		'be_groups',
		$tempColumns,
		1
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
		'be_groups',
		'tx_snowbabel_extensions;;;;1-1-1'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
		'be_groups',
		'tx_snowbabel_languages;;;;1-1-1'
	);

	// Add be_users fields
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
		'be_users',
		$tempColumns,
		1
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
		'be_users',
		'tx_snowbabel_extensions;;;;1-1-1'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
		'be_users',
		'tx_snowbabel_languages;;;;1-1-1'
	);

	unset($tempColumns);

}
?>