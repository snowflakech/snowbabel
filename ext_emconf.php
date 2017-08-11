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
			'php' => '5.4-7.0.99',
			'typo3' => '6.2.0-8.5.99',
			'static_info_tables' => '6.1.0-6.4.99',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'author' => 'Daniel Alder, Ricky Mathew',
	'author_email' => 'ricky.mk@pitsolutions.com',
	'author_company' => 'PIT Solutions Pvt Ltd',
	'version' => '5.0.1'
);