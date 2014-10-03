<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if(TYPO3_MODE === "BE") {

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Snowflake.' . $_EXTKEY,
		'snowbabel',
		'',
		'',
		array(),
		array(
			'access' => 'user,group',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_module_snowbabel.xlf'
		)
	);

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Snowflake.' . $_EXTKEY,
		'snowbabel',
		'translation',
		'',
		array(
			'Translation' => 'index'
		),
		array(
			'access' => 'user,group',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_module_translation.xlf'
		)
	);

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Snowflake.' . $_EXTKEY,
		'snowbabel',
		'settings',
		'',
		array(
			'Settings' => 'index'
		),
		array(
			'access' => 'user,group',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_module_settings.xlf'
		)
	);


	// Todo: use autoinclude tca
	// Extend Beusers For Translation Access Control
	$tempColumns = array(
		'tx_snowbabel_extensions' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:snowbabel/locallang_db.xlf:label.tx_snowbabel_extensions',
			'config' => Array(
				'type' => 'select',
				'itemsProcFunc' => 'Snowflake\Snowbabel\Hook\Tca->getExtensions',
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
				'itemsProcFunc' => 'Snowflake\Snowbabel\Hook\Tca->getLanguages',
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