<?php

// DO NOT REMOVE OR CHANGE THESE 3 LINES:
// Switch TYPO3_MOD_PATH for global or local installed extension
if(strstr($_SERVER['SCRIPT_FILENAME'], 'typo3/ext')) {
	define('TYPO3_MOD_PATH', 'ext/snowbabel/mod1/');
}
else {
	define('TYPO3_MOD_PATH', '../typo3conf/ext/snowbabel/mod1/');
}

$BACK_PATH='../../../../typo3/';
$MCONF['name']='snowbabel_translation';


$MCONF['access']='user,group';
$MCONF['script']='index.php';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';

	// Typo3 4.6 & Above
if(version_compare(TYPO3_version, '4.6.0', '>=')) {
	$MLANG['default']['ll_ref']='LLL:EXT:snowbabel/Resources/Private/Language/locallang_mod1.xlf';
}
	// Lower Then Typo3 4.6
else {
	$MLANG['default']['ll_ref']='LLL:EXT:snowbabel/Resources/Private/Language/locallang_mod1.xml';
}


?>