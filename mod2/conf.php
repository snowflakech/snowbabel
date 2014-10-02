<?php

// DO NOT REMOVE OR CHANGE THESE 3 LINES:
// Switch TYPO3_MOD_PATH for global or local installed extension
if(strstr($_SERVER['SCRIPT_FILENAME'], 'typo3/ext')) {
	define('TYPO3_MOD_PATH', 'ext/snowbabel/mod2/');
} else {
	define('TYPO3_MOD_PATH', '../typo3conf/ext/snowbabel/mod2/');
}

$BACK_PATH = '../../../../typo3/';
$MCONF['name'] = 'snowbabel_settings';


$MCONF['access'] = 'user,group';
$MCONF['script'] = 'index.php';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:snowbabel/Resources/Private/Language/locallang_mod2.xlf';