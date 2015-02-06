<?php

/***************************************************************
 * Extension Manager/Repository config file.
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Snowbabel',
	'description' => 'Translation Extension',
	'category' => 'module',
	'constraints' => array (
		'depends' => array (
			'typo3' => '6.2.0-6.2.99',
		),
		'conflicts' => array (),
		'suggests' => array (),
	),
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'author' => 'Daniel Alder',
	'author_email' => 'support@snowflake.ch',
	'author_company' => 'snowflake productions gmbh',
	'version' => '5.0.0'
);