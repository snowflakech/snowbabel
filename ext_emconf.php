<?php

/***************************************************************
 * Extension Manager/Repository config file.
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Snowbabel',
	'description' => 'Translation Extension',
	'category' => 'module',
	'constraints' => array(
		'depends' => array(
			'php' => '5.4-0.0.0',
			'typo3' => '6.2.0-6.2.99',
			'static_info_tables' => '6.1.0-6.1.99',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'author' => 'Daniel Alder',
	'author_email' => 'info@snowflake.ch',
	'author_company' => 'snowflake productions gmbh',
	'version' => '4.2.0'
);