<?php

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::registerExtDirectComponent (
	'TYPO3.Snowbabel.ExtDirect',
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Connection/class.tx_snowbabel_extdirectserver.php:tx_snowbabel_ExtDirectServer'
);

	// Add Scheduler Configuration For Indexing
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_snowbabel_system_indexing'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Snowbabel - Indexing',
    'description'      => 'Is indexing all translation data on current installation'
);

?>