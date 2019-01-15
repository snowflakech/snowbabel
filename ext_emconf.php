<?php

/***************************************************************
 * Extension Manager/Repository config file.
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Snowbabel',
	'description' => 'Translation Extension.Snowflake productions gmbh was the pioneers of this extension and handed over this to PIT Solutions Pvt Ltd in 2016 and sponsored by BIBUS AG.',
	'category' => 'module',
	'constraints' => array(
		'depends' => array(
			'php' => '5.6-7.2',
			'typo3' => '8.7.0-9.3.99',
			'static_info_tables' => '6.1.0-6.5.99',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'author' => 'Daniel Alder, Ricky Mathew, Anu Bhuvanendran Nair',
	'author_email' => 'ricky.mk@pitsolutions.com, anu.bn@pitsolutions.com',
	'author_company' => 'PIT Solutions Pvt Ltd',
	'version' => '5.2.0'
);