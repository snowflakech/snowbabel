<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === "BE")	{

	t3lib_extMgm::addModule('snowbabel', '', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	t3lib_extMgm::addModule('snowbabel', 'translation', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	t3lib_extMgm::addModule('snowbabel', 'settings', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod2/');


	include_once(t3lib_extMgm::extPath('snowbabel').'Classes/TCA/class.tx_snowbabel_tca.php');


		// Get Typo3 Version
	$version = class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) : t3lib_div::int_from_ver(TYPO3_version);

		// Extend Beusers For Translation Access Control
		// Typo3 4.6 & Above
	if ($version >= 4006000) {

		$tempColumns = array(
			'tx_snowbabel_extensions' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:snowbabel/locallang_db.xlf:label.tx_snowbabel_extensions',
				'config' => Array (
					'type' => 'select',
					'itemsProcFunc' => 'tx_snowbabel_TCA->getExtensions',
					'size' => 10,
					'maxitems' => 9999,
					'default' => ''
				)
			),
			'tx_snowbabel_languages' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:snowbabel/locallang_db.xlf:label.tx_snowbabel_languages',
				'config' => Array (
					'type' => 'select',
					'itemsProcFunc' => 'tx_snowbabel_TCA->getLanguages',
					'size' => 10,
					'maxitems' => 9999,
					'default' => ''
				)
			),
		);

	}
		// Lower Then Typo3 4.6
	else {

		$tempColumns = array(
			'tx_snowbabel_extensions' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:snowbabel/locallang_db.xml:label.tx_snowbabel_extensions',
				'config' => Array (
					'type' => 'select',
					'itemsProcFunc' => 'tx_snowbabel_TCA->getExtensions',
					'size' => 10,
					'maxitems' => 9999,
					'default' => ''
				)
			),
			'tx_snowbabel_languages' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:snowbabel/locallang_db.xml:label.tx_snowbabel_languages',
				'config' => Array (
					'type' => 'select',
					'itemsProcFunc' => 'tx_snowbabel_TCA->getLanguages',
					'size' => 10,
					'maxitems' => 9999,
					'default' => ''
				)
			),
		);

	}

	t3lib_div::loadTCA('be_groups');
	t3lib_extMgm::addTCAcolumns('be_groups',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('be_groups','tx_snowbabel_extensions;;;;1-1-1');
	t3lib_extMgm::addToAllTCAtypes('be_groups','tx_snowbabel_languages;;;;1-1-1');

	t3lib_div::loadTCA('be_users');
	t3lib_extMgm::addTCAcolumns('be_users',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('be_users','tx_snowbabel_extensions;;;;1-1-1');
	t3lib_extMgm::addToAllTCAtypes('be_users','tx_snowbabel_languages;;;;1-1-1');

	unset($tempColumns);

}
?>