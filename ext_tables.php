<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Register AJAX Requests
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
        'Snowbabel::dispatch',
        'Snowflake\\Snowbabel\\Utility\\Ajax\\DispatcherUtility->dispatch'
);

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
			'icon' => 'EXT:snowbabel/ext_icon.gif',
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
			'icon' => 'EXT:snowbabel/ext_icon.gif',
			'access' => 'user,group',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_module_settings.xlf'
		)
	);

}